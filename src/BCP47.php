<?php
/**
 * Undocumented file (language-extlang-script-region-variant-extension-privateuse)
 *
 * @package OrderOfMass
 *
 * @link https://www.iana.org/assignments/language-subtag-registry/language-subtag-registry
 * @link https://www.w3.org/International/articles/language-tags/index.en
 */

namespace TMD\OrderOfMass\Plugin;

use Psr\Log\LoggerInterface;

/**
 * Undocumented class
 */
class BCP47 {
	/**
	 * Undocumented variable
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $log;
	/**
	 * Undocumented variable
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * Undocumented function
	 *
	 * @param array $arr  Array.
	 * @param int   $case Case.
	 *
	 * @return array
	 */
	private function array_change_key_case_recursive( $arr, $case = CASE_LOWER ): array {
		return array_map(
			function ( $item ) use ( $case ) {
				if ( is_array( $item ) ) {
					$item = $this->array_change_key_case_recursive( $item, $case );
				}
				return $item;
			},
			array_change_key_case( $arr, $case )
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param LoggerInterface $log Log.
	 */
	public function __construct( LoggerInterface $log ) {
		$this->log = $log;

		$json_raw = file_get_contents( __DIR__ . '/../assets/json/bcp47.json' );
		/**
		 * Undocumented var.
		 *
		 * @var array */
		$json_dec = json_decode( $json_raw, true );
		$this->data = $this->array_change_key_case_recursive( $json_dec );
	}

	/**
	 * Undocumented function
	 *
	 * @return bool
	 */
	private function check_data(): bool {
		if ( array_key_exists( 'language', $this->data ) !== true ) {
			return false;
		}

		// Let's say the other parts of BCP47 (extlang, script, ...) are not
		// *that* important, so we don't have to rely on their existence.
		return true;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $identifier Identifier.
	 *
	 * @return string
	 */
	public function get_lang_name( string $identifier ): string {
		if ( $this->check_data() !== true ) {
			return 'Check failed';
		}
		$identifier_parts = explode( '-', $identifier );
		$ret = '';
		foreach ( $identifier_parts as $key => $identifier_part ) {
			$identifier_part_low = strtolower( $identifier_part );
			if ( 0 === $key ) {
				if ( isset( $this->data['language'][ $identifier_part_low ] ) !== true || is_array( $this->data['language'][ $identifier_part_low ] ) !== true || isset( $this->data['language'][ $identifier_part_low ]['description'] ) !== true ) {
					return 'First part is unknown';
				}
				$ret = (string) $this->data['language'][ $identifier_part_low ]['description'];
				continue;
			}
			foreach ( array_keys( $this->data ) as $subtag_key ) {
				if ( 'language' === $subtag_key ) {
					continue;
				}
				if ( isset( $this->data[ $subtag_key ][ $identifier_part_low ] ) !== true || is_array( $this->data[ $subtag_key ][ $identifier_part_low ] ) !== true || isset( $this->data[ $subtag_key ][ $identifier_part_low ]['description'] ) !== true ) {
					continue;
				}
				$ret .= ' (' . (string) $this->data[ $subtag_key ][ $identifier_part_low ]['description'] . ')';
			}
		}
		return $ret;
	}
}
