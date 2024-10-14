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
class Parameters {

	/**
	 * Undocumented variable
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $log;

	public const PARAMETER_TYPE = 'type';
	public const PARAMETER_TYPE_MASS = 'mass';
	public const PARAMETER_TYPE_ROSARY = 'rosary';
	public const PARAMETER_TYPE_BIBLE = 'bible';
	public const PARAMETER_TEXTS = 'texts';
	public const PARAMETER_LABELS = 'labels';
	public const PARAMETER_DATE = 'date';
	public const PARAMETER_BIBLE = 'bible';

	/**
	 * Undocumented function
	 *
	 * @param LoggerInterface $log Log.
	 */
	public function __construct( LoggerInterface $log ) {
		$this->log = $log;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $param Parameter.
	 * @throws \Exception Unknown parameter.
	 * @return string
	 */
	public function get_parameter( string $param ): string {
		$get_unslashed = wp_unslash( $_GET );

		if ( self::PARAMETER_TYPE === $param ) {
			$curr_post = get_post();
			if ( is_a( $curr_post, \WP_Post::class ) !== true ) {
				return self::PARAMETER_TYPE_MASS;
			}
			if ( $curr_post->post_parent > 0 ) {
				$curr_post = get_post( $curr_post->post_parent );
				if ( is_a( $curr_post, \WP_Post::class ) !== true ) {
					return self::PARAMETER_TYPE_MASS;
				}
			}
			if ( in_array( $curr_post->post_name, array( self::PARAMETER_TYPE_MASS, self::PARAMETER_TYPE_ROSARY, self::PARAMETER_TYPE_BIBLE ), true ) !== true ) {
				return self::PARAMETER_TYPE_MASS;
			}
			return $curr_post->post_name;
		}
		if ( self::PARAMETER_TEXTS === $param ) {
			$curr_post = get_post();
			if ( is_a( $curr_post, \WP_Post::class ) !== true ) {
				return 'en';
			}
			if ( $curr_post->post_parent <= 0 ) {
				return 'en';
			}
			$curr_post_parent = get_post( $curr_post->post_parent );
			if ( is_a( $curr_post_parent, \WP_Post::class ) !== true ) {
				return 'en';
			}
			if ( in_array( $curr_post_parent->post_name, array( self::PARAMETER_TYPE_MASS, self::PARAMETER_TYPE_ROSARY, self::PARAMETER_TYPE_BIBLE ), true ) !== true ) {
				return 'en';
			}
			return $curr_post->post_name;
		}
		if ( self::PARAMETER_LABELS === $param ) {
			if ( true !== isset( $get_unslashed[ self::PARAMETER_LABELS ] ) || true !== is_string( $get_unslashed[ self::PARAMETER_LABELS ] ) ) {
				return 'cs';
			}
			$preg = preg_replace( '/[^A-z0-9_\-]/', '', $get_unslashed[ self::PARAMETER_LABELS ] );
			return $preg;
		}
		if ( self::PARAMETER_DATE === $param ) {
			if ( true !== isset( $get_unslashed[ self::PARAMETER_DATE ] ) ) {
				return gmdate( 'Y-m-d' );
			}
			$param_date = $get_unslashed[ self::PARAMETER_DATE ];
			if ( true !== is_string( $param_date ) || preg_match( '/\d{4}-\d{2}-\d{2}/', $param_date ) !== 1 ) {
				return gmdate( 'Y-m-d' );
			}
			return $param_date;
		}
		if ( self::PARAMETER_BIBLE === $param ) {
			if ( true !== isset( $get_unslashed[ self::PARAMETER_BIBLE ] ) || true !== is_string( $get_unslashed[ self::PARAMETER_BIBLE ] ) ) {
				return '';
			}
			return $get_unslashed[ self::PARAMETER_BIBLE ];
		}
		throw new \Exception( 'Unknown OoM Param "' . esc_html( $param ) . '"' );
	}
}
