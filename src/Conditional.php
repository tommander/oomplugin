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
class Conditional {

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
	 * @param LoggerInterface $log        Log.
	 * @param Parameters      $parameters Parameters.
	 */
	public function __construct( LoggerInterface $log, Parameters $parameters ) {
		$this->log = $log;
		$this->parameters = $parameters;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function init() {
		register_block_type(
			__DIR__ . '/../assets/json/oom-conditional-block.json',
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param array{bafiky: array<string, string>} $block_attributes Block attributes.
	 * @param string                               $content          Content.
	 * @throws \Exception Wrong global date param.
	 * @return string
	 */
	public function render_block( $block_attributes, $content ) {
		$weekdays = array(
			0 => 'su',
			1 => 'mo',
			2 => 'tu',
			3 => 'we',
			4 => 'th',
			5 => 'fr',
			6 => 'sa',
		);
		$date = \DateTimeImmutable::createFromFormat( 'Y-m-d', $this->parameters->get_parameter( Parameters::PARAMETER_DATE ) );
		if ( false === $date ) {
			throw new \Exception( 'Wrong date global param.' );
		}
		$weekday = intval( $date->format( 'w' ) );
		if ( isset( $weekdays[ $weekday ] ) !== true ) {
			return '';
		}
		$weekday_str = $weekdays[ $weekday ];
		if ( isset( $block_attributes['bafiky'][ $weekday_str ] ) !== true ) {
			return '';
		}
		if ( '1' !== $block_attributes['bafiky'][ $weekday_str ] ) {
			return '';
		}
		return wp_kses( $content, 'post' );
	}
}
