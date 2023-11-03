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
		echo '<table id="oomlabels-table-languages">';
		echo '<tr><th>Code</th><th>Language</th></tr>';
		$key = 1;
		$this->loop_languages(
			function ( string $code ) use ( &$key ) {
				$lang = $this->bcp47->get_lang_name( $code );
				echo '<tr>';
				echo '<td><input name="oomlabels-languages[' . esc_attr( $key ) . ']" id="oomlabels-languages_' . esc_attr( $key ) . '_code" type="text" value="' . esc_attr( $code ) . '" /></td>';
				echo '<td>' . esc_html( $lang ) . '</td>';
				echo '</tr>';
				$key++;
			}
		);

		echo '</table>';
		echo '<div><button id="oomlabels-languages-add_row" type="button">Add row</button></div>';
		echo '<script>
		  const btnLangAddRow = document.getElementById("oomlabels-languages-add_row");
		  if (btnLangAddRow) {
			btnLangAddRow.addEventListener("click", () => {
			  const tblTestArr = document.getElementById("oomlabels-table-languages");
			  if (!tblTestArr) {
				return;
			  }
			  const trNewRow = tblTestArr.insertRow();
			  if (!trNewRow) {
				return;
			  }
			  const tdLabel = trNewRow.insertCell();
			  const inpLabel = document.createElement("input");
			  inpLabel.name = "oomlabels-languages["+trNewRow.rowIndex+"]";
			  inpLabel.id = "oomlabels-languages"+trNewRow.rowIndex+"_code";
			  inpLabel.type = "text";
			  inpLabel.value = "";
			  tdLabel.appendChild(inpLabel);

			  const tdLang = trNewRow.insertCell();
			});
		  }
		</script>';
	}

	/**
	 * Undocumented function
	 *
	 * @param mixed $args Args.
	 *
	 * @return void
	 */
	public function render_list( $args ) {
		echo '<table id="oomlabels-table-list">';
		echo '<tr>';
		echo '<th>Label</th>';
		$js_languages = '[';
		$this->loop_languages(
			function ( string $code ) use ( &$js_languages ) {
				$lang = $this->bcp47->get_lang_name( $code );
				echo '<th>' . esc_html( $lang ) . '</th>';
				$js_languages .= '{lang: "' . $lang . '", code: "' . $code . '"},';
			}
		);
		$js_languages .= ']';
		echo '</tr>';
		$key = 1;
		$this->loop_list(
			function ( array $list_item ) use ( $key ) {
				$label = $list_item['label'] ?? '';
				echo '<tr>';
				echo '<td><input name="oomlabels-list[' . esc_attr( strval( $key ) ) . '][label]" id="oomlabels-list_' . esc_attr( strval( $key ) ) . '_label" type="text" value="' . esc_attr( $label ) . '" /></td>';
				$this->loop_languages(
					function ( string $code ) use ( $list_item, $key, $label ) {
						$trans = $list_item[ $code ] ?? $label;
						echo '<td><input name="oomlabels-list[' . esc_attr( strval( $key ) ) . '][' . esc_attr( $code ) . ']" id="oomlabels-list_' . esc_attr( strval( $key ) ) . '_' . esc_attr( $code ) . '" type="text" value="' . esc_attr( $trans ) . '" /></td>';
					}
				);
				echo '</tr>';
				$key++;
			}
		);
		echo '</table>';
		echo '<div><button id="oomlabels-list-add_row" type="button">Add row</button></div>';
		echo '<script>
		  const arrLanguages = ' . esc_js( $js_languages ) . ';
		  const btnListAddRow = document.getElementById("oomlabels-list-add_row");
		  if (btnListAddRow) {
			btnListAddRow.addEventListener("click", () => {
			  const tblTestArr = document.getElementById("oomlabels-table-list");
			  if (!tblTestArr) {
				return;
			  }
			  const trNewRow = tblTestArr.insertRow();
			  if (!trNewRow) {
				return;
			  }
			  const tdLabel = trNewRow.insertCell();
			  const inpLabel = document.createElement("input");
			  inpLabel.name = "oomlabels-list["+trNewRow.rowIndex+"][label]";
			  inpLabel.id = "oomlabels-list_"+trNewRow.rowIndex+"_label";
			  inpLabel.type = "text";
			  inpLabel.value = "";
			  tdLabel.appendChild(inpLabel);

			  for (const langObj of arrLanguages) {
				const tdLang = trNewRow.insertCell();
				const inpLang = document.createElement("input");
				inpLang.name = "oomlabels-list["+trNewRow.rowIndex+"]["+langObj.code+"]";
				inpLang.id = "oomlabels-list_"+trNewRow.rowIndex+"_"+langObj.code;
				inpLang.type = "text";
				inpLang.value = "";
				tdLang.appendChild(inpLang);
			  }
			});
		  }
		</script>';
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
	public function shortcode_oomlabel( $atts, $content, $shortcode_tag ) {
		$opt = get_option( 'oomlabels-list' );
		if ( is_array( $opt ) !== true ) {
			$opt = array();
		}

		if ( empty( $content ) ) {
			return '<span class="oomlabel oomlabelerror">Label text not defined</span>';
		}

		$label = null;
		foreach ( $opt as $opt_val ) {
			if ( is_array( $opt_val ) !== true ) {
				continue;
			}
			if ( isset( $opt_val['label'] ) !== true ) {
				continue;
			}

			if ( strcasecmp( $opt_val['label'], $content ) !== 0 ) {
				continue;
			}

			$label = $opt_val;
			break;
		}
		if ( null === $label ) {
			return '<span class="oomlabel oomlabelerror">Label not found</span>';
		}
		$param_labels = $this->parameters->get_parameter( Parameters::PARAMETER_LABELS );
		$label_text = $label['label'];
		if ( isset( $label[ $param_labels ] ) === true ) {
			$label_text = $label[ $param_labels ];
		}

		return '<span class="oomlabel">' . esc_html( $label_text ) . '</span>';
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function render_options(): void {
		$current_option = $this->parameters->get_parameter( Parameters::PARAMETER_LABELS );
		echo '<option value="en-US"' . selected( $current_option, 'en-US', false ) . '>English</option>';
		$this->loop_languages(
			function ( string $code ) use ( &$options, $current_option ) {
				$lang = $this->bcp47->get_lang_name( $code );
				echo '<option value="' . esc_attr( $code ) . '"' . selected( $current_option, $code, false ) . '>' . esc_html( $lang ) . '</option>';
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
		$opt = get_option( 'oomlabels-languages' );
		if ( is_array( $opt ) !== true ) {
			$opt = array();
		}
		foreach ( $opt as $code ) {
			if ( is_string( $code ) !== true ) {
				$code = 'zxx';
			}
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
		$opt = get_option( 'oomlabels-list' );
		if ( is_array( $opt ) !== true ) {
			$opt = array();
		}
		foreach ( $opt as $list_item ) {
			if ( is_array( $list_item ) !== true ) {
				$list_item = array();
			}
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
			'OoM Labels Settings',
			'OoM Labels Settings',
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
			'OoM Labels Settings',
			array( $this, 'render_section_main' ),
			'oomlabels-settings',
			array()
		);
		add_settings_field(
			'oomlabels-languages',
			'Languages',
			array( $this, 'render_languages' ),
			'oomlabels-settings',
			'oomlabels-settings-main'
		);
		add_settings_field(
			'oomlabels-list',
			'List of labels',
			array( $this, 'render_list' ),
			'oomlabels-settings',
			'oomlabels-settings-main'
		);
		register_setting(
			'options',
			'oomlabels-languages',
			array(
				'type' => 'array',
				'description' => 'Languages available for translation of labels',
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
				'description' => 'List of translations of labels',
				'default' => array(),
				'show_in_rest' => false,
			)
		);
	}
}
