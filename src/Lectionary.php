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
	 * Undocumented variable
	 *
	 * @var Labels
	 */
	private Labels $labels;

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
	 * @param Labels          $labels     Labels.
	 * @param LoggerInterface $log        Log.
	 * @param Parameters      $parameters Parameters.
	 */
	public function __construct( Calendar $calendar, Labels $labels, LoggerInterface $log, Parameters $parameters ) {
		$this->calendar = $calendar;
		$this->labels = $labels;
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
	 * @return array<string, non-empty-list<string>>
	 */
	public function get_reference( string $type, string $date, string $lectionary ): array {
		/**
		 * Undocumented
		 *
		 * @var \wpdb
		 */
		global $wpdb;
		$lit_year = $this->calendar->liturgical_year( $date );
		$year_cycle = $this->calendar->year_cycle( $lit_year );
		$week_cycle = $this->calendar->week_cycle( $lit_year );
		$day_abbr = $this->calendar->date_abbr( $date );
		$lect_id = $this->lect_id( $lectionary );
		$meta_key = ( 'pp' === $type || 'pr' === $type ) ? 'p' : $type;
		/**
		 * ArrayA.
		 *
		 * @var string
		 */
		$prep = $wpdb->prepare(
			'SELECT meta_value, occasion FROM ' . $wpdb->prefix . 'lectionarymeta WHERE lectionary_id=%s AND day_abbr=%s AND (year_cycle=%s OR year_cycle=%s) AND (week_cycle=%s OR week_cycle=%s) AND meta_key=%s',
			$lect_id,
			$day_abbr,
			$year_cycle,
			'X',
			$week_cycle,
			'X',
			$meta_key
		);
		/**
		 * Undocumented.
		 *
		 * @var array<array-key, array<string, string>>|null
		 */
		$res = $wpdb->get_results(
			$prep, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			ARRAY_A
		);
		$ret = array();
		if ( null !== $res ) {
			foreach ( $res as $one_res ) {
				if ( 'pp' === $type || 'pr' === $type ) {
					$arr = explode( '||', $one_res['meta_value'], 2 );
					if ( count( $arr ) < 2 || 'pp' === $type ) {
						$ret[ $one_res['occasion'] ] = array( $arr[0] );
						continue;
					}
					if ( count( $arr ) === 2 ) {
						$ret[ $one_res['occasion'] ] = array( $arr[1] );
						continue;
					}
					continue;
				}
				$ret[ $one_res['occasion'] ] = explode( '||', $one_res['meta_value'] );
				// A$ret[ $one_res['occasion'] ] = $one_res['meta_value'];.
			}
		}

		return $ret;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $lectref Lectionary reference.
	 *
	 * @return string
	 */
	public function lect_id( string $lectref ): string {
		/**
		 * Undocumented.
		 *
		 * @var \wpdb
		 */
		global $wpdb;
		$prep = (string) $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'lectionaries WHERE reference=%s', $lectref );

		/**
		 * Undocumented.
		 *
		 * @var array<string, string>|null
		 */
		$res = $wpdb->get_row( $prep, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( null !== $res ) {
			return $res['id'];
		}
		return '';
	}

	/**
	 * Undocumented function
	 *
	 * @param array<string, non-empty-list<string>> $ref             Reference.
	 *
	 * @return string
	 */
	public function ref_to_str( array $ref ): string {
		$ret_arr = array();
		foreach ( $ref as $occasion => $one_ref ) {
			if ( '' !== $occasion ) {
				$ret_arr[] = $this->labels->get_label( 'Occasion' ) . ' "' . $this->labels->get_label( $occasion ) . '": ';
			}
			foreach ( $one_ref as $one_one_ref ) {
				$ret_arr[] = $one_one_ref;
			}
		}
		if ( count( $ret_arr ) === 0 ) {
			return '';
		}
		return implode( '<br>', $ret_arr );
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

		return '<span>' . $this->ref_to_str( $this->get_reference( $content, (string) $safe_atts['date'], (string) $safe_atts['lect'] ) ) . '</span>';
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
