<?php
/**
 * Undocumented file
 *
 * @package OrderOfMass
 */

namespace TMD\OrderOfMass\Plugin;

use Psr\Log\LoggerInterface;
use InvalidArgumentException;

/**
 * Undocumented class
 */
class Labels {

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
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'allowed_options', array( $this, 'allowed_options' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$js_file = plugin_dir_url( __FILE__ ) . '../resources/oom-labels.js';
		wp_enqueue_script( 'oomplugin-labels-js', $js_file, array(), microtime(), array() );

		/**
		 * Undocumented var.
		 *
		 * @var array<array-key, array{lang: string, code: string}>
		 */
		$js_languages = array();
		$this->loop_languages(
			function ( string $code ) use ( &$js_languages ) {
				$js_languages[] = array(
					'lang' => $this->bcp47->get_lang_name( $code ),
					'code' => $code,
				);
			}
		);

		wp_add_inline_script(
			'oomplugin-globals-js',
			'window.oom.languages = ' . json_encode( $js_languages ) . ';',
			'after'
		);
	}

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
	public function render_settings() {
		echo '<form method="POST" action="options.php">';
		settings_fields( 'oomlabels-settings' );
		do_settings_sections( 'oomlabels-settings' );
		submit_button();
		echo '</form>';
	}

	/**
	 * Undocumented function
	 *
	 * @param mixed $args Args.
	 *
	 * @return void
	 */
	public function render_section_main( $args ) {
		echo '<p>Main section</p>';
	}

	/**
	 * Undocumented function
	 *
	 * @param mixed $args Args.
	 *
	 * @return void
	 */
	public function render_languages( $args ) {
		?>
		<table id="oomlabels-table-languages">
			<tr>
				<th>Code</th>
				<th>Language</th>
			</tr>
			<?php
			$key = 1;
			$this->loop_languages(
				function ( string $code ) use ( &$key ) {
					$lang = $this->bcp47->get_lang_name( $code );
					?>
			<tr>
				<td><input name="oomlabels-languages[<?php echo esc_attr( (string) $key ); ?>]" id="oomlabels-languages_<?php echo esc_attr( (string) $key ); ?>_code" type="text" value="<?php echo esc_attr( $code ); ?>" /></td>
				<td><?php echo esc_html( $lang ); ?></td>
			</tr>
					<?php
					$key++;
				}
			);
		?>
		</table>
		<div><button id="oomlabels-languages-add_row" type="button">Add row</button></div>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param mixed $args Args.
	 *
	 * @return void
	 */
	public function render_list( $args ) {
		?>
		<table id="oomlabels-table-list">
			<tr>
				<th>Label</th>
				<?php
				$this->loop_languages(
					function ( string $code ) {
						?>
				<th><?php echo esc_html( $this->bcp47->get_lang_name( $code ) ); ?></th>
						<?php
					}
				);
		?>
			</tr>
			<?php
			$key = 1;
			$this->loop_list(
				function ( array $list_item ) use ( &$key ) {
					$label = (string) $list_item['label'];
					?>
			<tr>
				<td><input name="oomlabels-list[<?php echo esc_attr( (string) $key ); ?>][label]" id="oomlabels-list_<?php echo esc_attr( (string) $key ); ?>_label" type="text" value="<?php echo esc_attr( $label ); ?>" /></td>
					<?php
					$this->loop_languages(
						function ( string $code ) use ( $list_item, $key, $label ) {
							$trans = ( isset( $list_item[ $code ] ) && is_string( $list_item[ $code ] ) ) ? $list_item[ $code ] : $label;
							?>
				<td><input name="oomlabels-list[<?php echo esc_attr( (string) $key ); ?>][<?php echo esc_attr( $code ); ?>]" id="oomlabels-list_<?php echo esc_attr( (string) $key ); ?>_<?php echo esc_attr( $code ); ?>" type="text" value="<?php echo esc_attr( $trans ); ?>" /></td>
							<?php
						}
					);
					?>
			</tr>
					<?php
					$key++;
				}
			);
		?>
		</table>
		<div><button id="oomlabels-list-add_row" type="button">Add row</button></div>
		<?php
	}

	/**
	 * Undocumented function
	 *
	 * @param array  $atts          Atts.
	 * @param string $content       Content.
	 * @param string $shortcode_tag Shortcode tag.
	 * @throws InvalidArgumentException Empty content param.
	 * @return string
	 */
	public function shortcode_oomlabel( $atts, $content, $shortcode_tag ) {
		$opt = (array) get_option( 'oomlabels-list' );

		if ( empty( $content ) ) {
			throw new InvalidArgumentException( self::class . '::' . __FUNCTION__ . ' - param "content" is empty.' );
		}

		$safe_content = strtolower( htmlspecialchars( $content ) );

		$label = null;
		foreach ( $opt as $opt_val ) {
			if ( is_array( $opt_val ) !== true ) {
				continue;
			}
			if ( isset( $opt_val['label'] ) !== true || is_string( $opt_val['label'] ) !== true ) {
				continue;
			}

			if ( strcasecmp( $opt_val['label'], $content ) !== 0 ) {
				continue;
			}

			$label = $opt_val;
			break;
		}
		if ( null === $label ) {
			$this->log->notice( 'Missing label "' . $content . '"', array() );
			return '';
		}
		$param_labels = $this->parameters->get_parameter( Parameters::PARAMETER_LABELS );
		$label_text = $label['label'];
		if ( isset( $label[ $param_labels ] ) === true ) {
			$label_text = (string) $label[ $param_labels ];
		}

		return '<span class="oomlabel">' . esc_html( $label_text ) . '</span>';
	}

	/**
	 * Undocumented function
	 *
	 * @param string $label Label.
	 *
	 * @return string
	 */
	public function get_label( string $label ): string {
		return $this->shortcode_oomlabel( array(), $label, '' );
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function render_options(): void {
		$current_option = $this->parameters->get_parameter( Parameters::PARAMETER_LABELS );
		?>
		<option value="en"<?php selected( $current_option, 'en' ); ?>>English</option>
		<?php
		$this->loop_languages(
			function ( string $code ) use ( $current_option ) {
				$lang = $this->bcp47->get_lang_name( $code );
				?>
		<option value="<?php echo esc_attr( $code ); ?>"<?php selected( $current_option, $code ); ?>><?php echo esc_html( $lang ); ?></option>';
				<?php
			}
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function init() {
		add_shortcode( 'oomlabel', array( $this, 'shortcode_oomlabel' ) );
	}

	/**
	 * Undocumented function
	 *
	 * @param callable $loop_fcn Loop function.
	 *
	 * @return void
	 */
	private function loop_languages( callable $loop_fcn ) {
		/**
		 * Undocumented.
		 *
		 * @var list{string}
		 */
		$opt = (array) get_option( 'oomlabels-languages' );
		foreach ( $opt as $code ) {
			call_user_func( $loop_fcn, $code );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param callable $loop_fcn Loop function.
	 *
	 * @return void
	 */
	private function loop_list( callable $loop_fcn ) {
		/**
		 * Undocumented.
		 *
		 * @var list{array}
		 */
		$opt = (array) get_option( 'oomlabels-list' );
		foreach ( $opt as $list_item ) {
			call_user_func( $loop_fcn, $list_item );
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param mixed $options Options.
	 *
	 * @return mixed
	 */
	public function allowed_options( $options ) {
		if ( true !== is_array( $options ) ) {
			return $options;
		}
		$options['oomlabels-settings'] = array( 'oomlabels-languages', 'oomlabels-list' );
		return $options;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_options_page(
			$this->get_label( 'OoM Labels Settings' ),
			$this->get_label( 'OoM Labels Settings' ),
			'manage_options',
			'oomlabels-settings',
			array( $this, 'render_settings' ),
			null
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function admin_init() {
		add_settings_section(
			'oomlabels-settings-main',
			$this->get_label( 'OoM Labels Settings' ),
			array( $this, 'render_section_main' ),
			'oomlabels-settings',
			array()
		);
		add_settings_field(
			'oomlabels-languages',
			$this->get_label( 'Languages' ),
			array( $this, 'render_languages' ),
			'oomlabels-settings',
			'oomlabels-settings-main'
		);
		add_settings_field(
			'oomlabels-list',
			$this->get_label( 'List of labels' ),
			array( $this, 'render_list' ),
			'oomlabels-settings',
			'oomlabels-settings-main'
		);
		register_setting(
			'options',
			'oomlabels-languages',
			array(
				'type' => 'array',
				'description' => $this->get_label( 'Languages available for translation of labels' ),
				'default' => array(),
				'show_in_rest' => array(
					'schema' => array(
						'type' => 'array',
						'items' => array(
							'type' => 'string',
						),
					),
				),
			)
		);
		register_setting(
			'options',
			'oomlabels-list',
			array(
				'type' => 'array',
				'description' => $this->get_label( 'List of translations of labels' ),
				'default' => array(),
				'show_in_rest' => false,
			)
		);
	}
}
