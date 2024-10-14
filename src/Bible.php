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
class Bible {

	/**
	 * Undocumented variable
	 *
	 * @var BCP47
	 */
	private BCP47 $bcp47;
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
	 * @param BCP47           $bcp47      BCP47.
	 * @param LoggerInterface $log        Log.
	 * @param Parameters      $parameters Parameters.
	 */
	public function __construct( BCP47 $bcp47, LoggerInterface $log, Parameters $parameters ) {
		$this->bcp47 = $bcp47;
		$this->log = $log;
		$this->parameters = $parameters;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function render_options(): void {
		/**
		 * Global WP DB.
		 *
		 * @var \wpdb
		 */
		global $wpdb;
		/**
		 * Hello
		 *
		 * @var array<string, array{language: string,title: string,identifier:string}>
		 */
		$res = $wpdb->get_results( 'SELECT title,identifier,language FROM ' . $wpdb->prefix . 'bible', ARRAY_A );
		$ret_arr = array();
		foreach ( $res as $translation ) {
			if ( isset( $ret_arr[ $translation['language'] ] ) !== true ) {
				$ret_arr[ $translation['language'] ] = array();
			}
			$ret_arr[ $translation['language'] ][] = array(
				'title' => $translation['title'],
				'identifier' => $translation['identifier'],
			);
		}

		$current_bible = $this->parameters->get_parameter( Parameters::PARAMETER_BIBLE );
		foreach ( $ret_arr as $lang => $translations ) {
			echo '<optgroup label="' . esc_attr( $this->bcp47->get_lang_name( $lang ) ) . '">';
			foreach ( $translations as $translation ) {
				printf(
					'<option value="%1$s"%2$s>%3$s</option>',
					esc_attr( $translation['identifier'] ),
					selected( $translation['identifier'], $current_bible, false ),
					esc_html( $translation['title'] )
				);
			}
			echo '</optgroup>';
		}
	}

	// Add shortcode to translate reference to a specific part of selected Bible translation
	// If the translation/reference does not exist, return empty <div>
	// oombible_ref('Ex 22:20-26', 'NABRE') => 'Thus says the LORD...'
	// [oombible-ref trans="NABRE"]Ex 22:20-26[/oombible-ref] => '<div>Thus says the LORD...</div>'
	// [oombible-ref trans="NABRE"]Ex 123:45-67[/oombible-ref] => '<div></div>'.

	// Add shortcode to return whole Bible book chapter
	// If translation/book/chapter does not exist, return empty string
	// oombible_chap('NABRE', 'Exodus', '1') => '<h1>Exodus</h1><h2>Chapter 1</h2><h3>1 Jacob’s Descendants in Egypt.</h3><p>1 These are the names...</p>'
	// [oombible-chap trans="NABRE" book="Exodus"]1[/oombible-chap] => '<h1>Exodus</h1><h2>Chapter 1</h2><h3>1 Jacob’s Descendants in Egypt.</h3><p>1 These are the names...</p>'
	// [oombible-chap trans="NABRE" book="Exodus"]42[/oombible-chap] => ''.
}
