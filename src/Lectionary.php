<?php
/**
 * Undocumented file
 *
 * @package OrderOfMass
 */

namespace TMD\OrderOfMass\Plugin;

use Psr\Log\LoggerInterface;

/**
 * Undocumented class
 */
class Lectionary {
	/**
	 * Undocumented variable
	 *
	 * @var Calendar
	 */
	private Calendar $calendar;
	/**
	 * Undocumented variable
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $log;
	/**
	 * Undocumented variable
	 *
	 * @var Parameters
	 */
	private Parameters $parameters;

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Undocumented function
	 *
	 * @param Calendar        $calendar   Calendar.
	 * @param LoggerInterface $log        Log.
	 * @param Parameters      $parameters Parameters.
	 */
	public function __construct( Calendar $calendar, LoggerInterface $log, Parameters $parameters ) {
		$this->calendar = $calendar;
		$this->log = $log;
		$this->parameters = $parameters;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $type       Type.
	 * @param string $date       Date.
	 * @param string $lectionary Lectionary.
	 *
	 * @return array
	 */
	public function get_reference( string $type, string $date, string $lectionary ): array {
		global $wpdb;
		$lit_year = $this->calendar->liturgical_year( $date );
		$year_cycle = $this->calendar->year_cycle( $lit_year );
		$week_cycle = $this->calendar->week_cycle( $lit_year );
		$day_abbr = $this->calendar->date_abbr( $date );
		$lect_id = $this->lect_id( $lectionary );
		/**
		 * ArrayA.
		 *
		 * @psalm-suppress UndefinedConstant
		 */
		$res = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT meta_value, occasion FROM ' . $wpdb->prefix . 'lectionarymeta WHERE lectionary_id=%s AND day_abbr=%s AND (year_cycle=%s OR year_cycle=%s) AND (week_cycle=%s OR week_cycle=%s) AND meta_key=%s',
				$lect_id,
				$day_abbr,
				$year_cycle,
				'X',
				$week_cycle,
				'X',
				$type
			),
			ARRAY_A
		);
		if ( null !== $res ) {
			$ret = array();
			foreach ( $res as $one_res ) {
				$ret[ $one_res['occasion'] ] = explode( '||', $one_res['meta_value'] );
			}
			return $ret;
		}

		return array();
	}

	/**
	 * Undocumented function
	 *
	 * @param string $lectref Lectionary reference.
	 *
	 * @return string
	 */
	public function lect_id( string $lectref ): string {
		global $wpdb;
		/**
		 * ArrayA.
		 *
		 * @psalm-suppress UndefinedConstant
		 */
		$res = $wpdb->get_row( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'lectionaries WHERE reference=%s', $lectref ), ARRAY_A );
		if ( null !== $res ) {
			return $res['id'];
		}
		return '';
	}

	// Add shortcode to translate reading type (e.g. first reading) to a specific Bible reference using the specified Lectionary (e.g. OLM81)
	// If there is no such type, return empty string
	// oomreading_ref('r1', 'OLM81') => 'Ex 22:20-26'
	// [oomreading-ref lect="OLM81" date="2012-12-21"]r1[/oomreading-ref] => '<span>Ex 22:20-26</span>'
	// [oomreading-ref lect="OLM81" date="2012-12-21"]r42[/oomreading-ref] => ''.

	/**
	 * Undocumented function
	 *
	 * @param string $ref_type Reference type.
	 *
	 * @return string
	 */
	public function ref_type_label( string $ref_type ): string {

		if ( 'a' === $ref_type ) {
			return 'Alleluia';
		}
		if ( 'ae' === $ref_type ) {
			return 'Before Gospel';
		}
		if ( 'e' === $ref_type ) {
			return 'Gospel';
		}
		if ( 'ep' === $ref_type ) {
			return 'Epistola';
		}
		if ( in_array( $ref_type, array( 'p', 'p2', 'p3', 'p4', 'p5', 'p6', 'p7' ), true ) === true ) {
			return 'Responsorial psalm';
		}
		if ( 'r1' === $ref_type ) {
			return 'First reading';
		}
		if ( 'r2' === $ref_type ) {
			return 'Second reading';
		}
		if ( 'r3' === $ref_type ) {
			return 'Third reading';
		}
		if ( 'r4' === $ref_type ) {
			return 'Fourth reading';
		}
		if ( 'r5' === $ref_type ) {
			return 'Fifth reading';
		}
		if ( 'r6' === $ref_type ) {
			return 'Sixth reading';
		}
		if ( 'r7' === $ref_type ) {
			return 'Seventh reading';
		}
		return '';
	}

	/**
	 * Undocumented function
	 *
	 * @param array  $ref             Reference.
	 * @param string $ref_type        Reference type.
	 * @param string $heading_element Heading element.
	 * @param bool   $div             Div.
	 *
	 * @return string
	 */
	public function ref_to_str( array $ref, string $ref_type, string $heading_element = 'h2', bool $div = true ): string {
		$ret_arr = array();
		foreach ( $ref as $occasion => $one_ref ) {
			if ( '' !== $occasion ) {
				$ret_arr[] = 'Occasion: ' . $occasion;
			}
			foreach ( $one_ref as $one_one_ref ) {
				$ret_arr[] = $one_one_ref;
			}
		}
		if ( count( $ret_arr ) === 0 ) {
			return '';
		}
		$ret = sprintf( '<%1$s>%2$s</%1$s>', $heading_element, $this->ref_type_label( $ref_type ) );
		$ret .= $div ? '<div>' : '<span>';
		$ret .= implode( $div ? '<br>' : ' ', $ret_arr );
		$ret .= $div ? '</div>' : '</span>';
		return $ret;
	}

	/**
	 * Undocumented function
	 *
	 * @param array  $atts          Atts.
	 * @param string $content       Content.
	 * @param string $shortcode_tag Shortcode tag.
	 *
	 * @return string
	 */
	public function shortcode_oomreading( $atts, $content, $shortcode_tag ) {
		$safe_atts = shortcode_atts(
			array(
				'lect' => 'OLM81',
				'date' => $this->parameters->get_parameter( Parameters::PARAMETER_DATE ),
			),
			$atts,
			$shortcode_tag
		);

		return $this->ref_to_str( $this->get_reference( $content, $safe_atts['date'], $safe_atts['lect'] ), $content );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function init() {
		add_shortcode( 'oomreading', array( $this, 'shortcode_oomreading' ) );
	}
}
