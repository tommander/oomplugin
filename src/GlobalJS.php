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
class GlobalJS {
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$js_file = plugin_dir_url( __FILE__ ) . '../resources/oom-globals.js';
		wp_enqueue_script( 'oomplugin-globals-js', $js_file, array(), microtime(), array() );
		wp_add_inline_script(
			'oomplugin-globals-js',
			'window.oom.siteurl = "' . site_url( '/' ) . '";',
			'after'
		);
	}
}
