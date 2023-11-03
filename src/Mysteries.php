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
class Mysteries {

	/**
	 * Undocumented variable
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $log;

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
	 * @param string $date Date.
	 * @throws \Exception Wrong date param.
	 * @return string
	 */
	public function get_mystery( string $date ): string {
		$datecls = \DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
		if ( false === $datecls ) {
			throw new \Exception( 'Wrong date param.' );
		}
		$weekday = $datecls->format( 'w' );
		switch ( $weekday ) {
			case '0':
				return 'g';
			case '1':
				return 'j';
			case '2':
				return 's';
			case '3':
				return 'g';
			case '4':
				return 'l';
			case '5':
				return 's';
			case '6':
				return 'j';
		}

			return '';
	}

	/**
	 * Undocumented function
	 *
	 * @param string $mystery Mystery.
	 *
	 * @return string
	 */
	public function get_mystery_long( string $mystery ): string {
		$mysteries = array(
			'g' => __( 'Glorious mystery', 'order-of-mass' ),
			'j' => __( 'Joyful mystery', 'order-of-mass' ),
			's' => __( 'Sorrowful mystery', 'order-of-mass' ),
			'l' => __( 'Luminous mystery', 'order-of-mass' ),
		);

		if ( isset( $mysteries[ $mystery ] ) === true ) {
			return $mysteries[ $mystery ];
		}

		return '';
	}
}
