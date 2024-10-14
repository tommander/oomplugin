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
class Calendar {

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
	private array $days;

	/**
	 * Undocumented function
	 *
	 * @param LoggerInterface $log Log.
	 */
	public function __construct( LoggerInterface $log ) {
		$this->log = $log;

		$days_json = file_get_contents( __DIR__ . '/../assets/json/day-abbrs.json' );
		$this->days = (array) json_decode( $days_json, true );
	}

	/**
	 * Undocumented function
	 *
	 * @param string $date Date.
	 * @throws \Exception Incorrect date format.
	 * @return string
	 */
	public function date_abbr( string $date ): string {
		$lityear = $this->liturgical_year( $date );
		$this->generate_calendar( $lityear );

		/**
		 * WpDb.
		 *
		 * @var \wpdb
		 */
		global $wpdb;

		/**
		 * Aaa
		 *
		 * @var \DateTimeImmutable|false
		 */
		$dti = \DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
		if ( false === $dti ) {
			throw new \Exception( 'Date for function Calendar::date_abbr() must be in "Y-m-d" format.' );
		}
		/**
		 * ArrayA.
		 *
		 * @var string
		 */
		$prep = $wpdb->prepare( 'SELECT reference FROM ' . $wpdb->prefix . 'calendar WHERE date=%s', $dti->format( 'd.m.Y' ) );
		/**
		 * ArrayA.
		 *
		 * @var array{reference: string}
		 */
		$res = $wpdb->get_row( $prep, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $res['reference'];
	}

	/**
	 * Undocumented function
	 *
	 * @param string $abbr Abbr.
	 *
	 * @return string
	 */
	public function abbr_description( string $abbr ): string {
		return isset( $this->days[ $abbr ] ) ? (string) $this->days[ $abbr ] : '';
	}

	/**
	 * Undocumented function
	 *
	 * @param int $liturgical_year Liturgical year.
	 *
	 * @return string
	 */
	public function year_cycle( int $liturgical_year ): string {
		$mod = ( $liturgical_year % 3 );
		switch ( $mod ) {
			case 0:
				return 'C';
			case 1:
				return 'A';
			case 2:
				return 'B';
		}

		return '';
	}

	/**
	 * Undocumented function
	 *
	 * @param int $liturgical_year Liturgical year.
	 *
	 * @return string
	 */
	public function week_cycle( int $liturgical_year ): string {
		if ( ( $liturgical_year % 2 ) === 0 ) {
			return 'II';
		}

		return 'I';
	}

	/**
	 * Undocumented function
	 *
	 * @param string $date Date.
	 * @throws \Exception Wrong date param.
	 * @return int
	 */
	public function liturgical_year( string $date ): int {
		$date = \DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
		if ( false === $date ) {
			throw new \Exception( 'Wrong date parameter in Calendar::liturgical_year.' );
		}
		$year = intval( $date->format( 'Y' ) );
		$odi  = new \DateInterval( 'P1D' );
		$fas  = new \DateTime( $year . '-11-27' );
		while ( $fas->format( 'w' ) !== '0' ) {
			$fas->add( $odi );
		}

		if ( $fas->diff( $date )->format( '%R' ) === '+' ) {
			return ( $year + 1 );
		}

		return $year;
	}

	/**
	 * Undocumented function
	 *
	 * @param int  $liturgical_year       Liturgical year.
	 * @param bool $traditional_epiphany  Traditional Epiphany.
	 * @param bool $traditional_ascension Traditional Ascension.
	 *
	 * @return void
	 */
	public function generate_calendar( int $liturgical_year, bool $traditional_epiphany = true, bool $traditional_ascension = false ): void {
		// We do not allow to generate a calendar that is +-3 years away from current year.
		if ( $liturgical_year < ( (int) ( gmdate( 'Y' ) - 3 ) ) || $liturgical_year > ( (int) ( ( gmdate( 'Y' ) ) + 3 ) ) ) {
			return;
		}

		// Check whether the calendar already exists.
		/**
		 * Undocumented var.
		 *
		 * @var \wpdb
		 */
		global $wpdb;
		/**
		 * ArrayA.
		 *
		 * @var string
		 */
		$prep = $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'calendar WHERE liturgical_year=%d', $liturgical_year );
		$check_cal = $wpdb->get_row( $prep, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( null !== $check_cal ) {
			return;
		}

		$calendar = array();

		$odi = new \DateInterval( 'P1D' );

		$short_days = array(
			'0' => 'Su',
			'1' => 'Mo',
			'2' => 'Tu',
			'3' => 'We',
			'4' => 'Th',
			'5' => 'Fr',
			'6' => 'Sa',
		);

		$days_until_baptism = array(
			'27' => array(
				1 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'ThoA3', 'FroA3', 'Dec17', 'SuoA4', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'FotHF', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'EotL', 'Jan7', 'BotL' ),
				0 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'ThoA3', 'FroA3', 'Dec17', 'SuoA4', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'FotHF', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'Jan6', 'Jan7', 'EoTL', 'BotL' ),
			),
			'28' => array(
				1 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'ThoA3', 'Dec17', 'Dec18', 'SuoA4', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'FotHF', 'Dec27', 'Dec28', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'SuaC2', 'Jan3', 'Jan4', 'Jan5', 'EotL', 'Jan7', 'Jan8', 'BotL' ),
				0 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'ThoA3', 'Dec17', 'Dec18', 'SuoA4', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'FotHF', 'Dec27', 'Dec28', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'EotL', 'Jan3', 'Jan4', 'Jan5', 'Jan6', 'Jan7', 'Jan8', 'BotL' ),
			),
			'29' => array(
				1 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'Dec17', 'Dec18', 'Dec19', 'SuoA4', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'FotHF', 'Dec28', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'SuaC2', 'Jan4', 'Jan5', 'EotL', 'Jan7', 'Jan8', 'Jan9', 'BotL' ),
				0 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'WeoA3', 'Dec17', 'Dec18', 'Dec19', 'SuoA4', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'FotHF', 'Dec28', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'EotL', 'Jan4', 'Jan5', 'Jan6', 'Jan7', 'Jan8', 'Jan9', 'BotL' ),
			),
			'30' => array(
				1 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'SuoA4', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'FotHF', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'SuaC2', 'Jan5', 'EotL', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'BotL' ),
				0 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'TuoA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'SuoA4', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'FotHF', 'Dec29', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'EotL', 'Jan5', 'Jan6', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'BotL' ),
			),
			'01' => array(
				1 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'SuoA4', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'FotHF', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'SuaC2', 'EotL', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'Jan11', 'BotL' ),
				0 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'MooA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'SuoA4', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'FotHF', 'Dec30', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'EotL', 'Jan6', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'Jan11', 'BotL' ),
			),
			'02' => array(
				1 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'SuoA4', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'FotHF', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'EotL', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'Jan11', 'Jan12', 'BotL' ),
				0 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'Dec17', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'SuoA4', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'FotHF', 'Dec31', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'EotL', 'Jan7', 'Jan8', 'Jan9', 'Jan10', 'Jan11', 'Jan12', 'BotL' ),
			),
			'03' => array(
				1 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'Dec30', 'FotHF', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'EotL', 'BotL' ),
				0 => array( 'SuoA1', 'MooA1', 'TuoA1', 'WeoA1', 'ThoA1', 'FroA1', 'SaoA1', 'SuoA2', 'MooA2', 'TuoA2', 'WeoA2', 'ThoA2', 'FroA2', 'SaoA2', 'SuoA3', 'Dec18', 'Dec19', 'Dec20', 'Dec21', 'Dec22', 'Dec23', 'Dec24', 'Christmas', 'Dec26', 'Dec27', 'Dec28', 'Dec29', 'Dec30', 'FotHF', 'BVMMoG', 'Jan2', 'Jan3', 'Jan4', 'Jan5', 'Jan6', 'EotL', 'BotL' ),
			),
		);

		// First we find the Sunday within 27-11 to 03-12, that is
		// the First Sunday of Advent, i.e. beginning of the
		// liturgical year.
		$start = new \DateTime( ( $liturgical_year - 1 ) . '-11-27' );
		while ( intval( $start->format( 'w' ) ) !== 0 ) {
			$start->add( $odi );
		}

		// Now we add the days until the Baptism of the Lord.
		foreach ( $days_until_baptism[ $start->format( 'd' ) ][ (int) $traditional_epiphany ] as $day_abbr ) {
			$calendar[ $start->format( 'd.m.Y' ) ] = $day_abbr;
			$start->add( $odi );
		}

		// Now we need to know the date of Ash Wednesday.
		$equinox = new \DateTime( $liturgical_year . '-03-21' );
		// Current year's spring equinox.
		$fms = new \DateTime( '2000-01-06' );
		// Known full moon date close to 12 pm.
		$dftmp = intval( $fms->diff( $equinox )->format( '%a' ) );
		// Days between.
		while ( $dftmp >= 29.53058770576 ) {
			// Repeatedly subtract one month.
			$dftmp -= 29.53058770576;
		}

		if ( $dftmp > 15.765294 ) {
			// If we are in the second half of the cycle, subtract one more month.
			$dftmp -= 29.53058770576;
		}

		$dtfm = 0;
		// Compute days until next full moon.
		while ( $dftmp <= 14.765294 ) {
			$dtfm++;
			$dftmp++;
		}

		$ash = $equinox->add( new \DateInterval( "P{$dtfm}D" ) );
		// Date of first full moon after spring equinox.
		while ( intval( $ash->format( 'w' ) ) !== 0 ) {
			// Find first sunday after that full moon.
			$ash = $ash->add( $odi );
		}

		$ash->sub( new \DateInterval( 'P46D' ) );
		// Subtract 46 days (that is Ash Wednesday).
		$week_in_ot = 1;

		// Add Ordinary Time Sundays until before Ash Wednesday.
		while ( $start->diff( $ash )->format( '%R' ) === '+' ) {
			if ( $start->format( 'w' ) === '0' ) {
				$week_in_ot++;
			}

			$calendar[ $start->format( 'd.m.Y' ) ] = $short_days[ (int) $start->format( 'w' ) ] . "iOT{$week_in_ot}";
			$start->add( $odi );
		}

		$start->sub( $odi );

		$days_until_pentecost = array(
			1 => array( 'WeAsh', 'ThaAW', 'FraAW', 'SaaAW', 'SuoL1', 'MooL1', 'TuoL1', 'WeoL1', 'ThoL1', 'FroL1', 'SaoL1', 'SuoL2', 'MooL2', 'TuoL2', 'WeoL2', 'ThoL2', 'FroL2', 'SaoL2', 'SuoL3', 'MooL3', 'TuoL3', 'WeoL3', 'ThoL3', 'FroL3', 'SaoL3', 'SuoL4', 'MooL4', 'TuoL4', 'WeoL4', 'ThoL4', 'FroL4', 'SaoL4', 'SuoL5', 'MooL5', 'TuoL5', 'WeoL5', 'ThoL5', 'FroL5', 'SaoL5', 'SuPalm', 'MooHW', 'TuoHW', 'WeoHW', 'ThHoly', 'FrGood', 'SaHoly', 'SuEaster', 'MooE1', 'TuoE1', 'WeoE1', 'ThoE1', 'FroE1', 'SaoE1', 'SuoE2', 'MooE2', 'TuoE2', 'WeoE2', 'ThoE2', 'FroE2', 'SaoE2', 'SuoE3', 'MooE3', 'TuoE3', 'WeoE3', 'ThoE3', 'FroE3', 'SaoE3', 'SuoE4', 'MooE4', 'TuoE4', 'WeoE4', 'ThoE4', 'FroE4', 'SaoE4', 'SuoE5', 'MooE5', 'TuoE5', 'WeoE5', 'ThoE5', 'FroE5', 'SaoE5', 'SuoE6', 'MooE6', 'TuoE6', 'WeoE6', 'AotL', 'FroE6', 'SaoE6', 'SuoE7', 'MooE7', 'TuoE7', 'WeoE7', 'ThoE7', 'FroE7', 'SaoE7', 'Pentecost' ),
			0 => array( 'WeAsh', 'ThaAW', 'FraAW', 'SaaAW', 'SuoL1', 'MooL1', 'TuoL1', 'WeoL1', 'ThoL1', 'FroL1', 'SaoL1', 'SuoL2', 'MooL2', 'TuoL2', 'WeoL2', 'ThoL2', 'FroL2', 'SaoL2', 'SuoL3', 'MooL3', 'TuoL3', 'WeoL3', 'ThoL3', 'FroL3', 'SaoL3', 'SuoL4', 'MooL4', 'TuoL4', 'WeoL4', 'ThoL4', 'FroL4', 'SaoL4', 'SuoL5', 'MooL5', 'TuoL5', 'WeoL5', 'ThoL5', 'FroL5', 'SaoL5', 'SuPalm', 'MooHW', 'TuoHW', 'WeoHW', 'ThHoly', 'FrGood', 'SaHoly', 'SuEaster', 'MooE1', 'TuoE1', 'WeoE1', 'ThoE1', 'FroE1', 'SaoE1', 'SuoE2', 'MooE2', 'TuoE2', 'WeoE2', 'ThoE2', 'FroE2', 'SaoE2', 'SuoE3', 'MooE3', 'TuoE3', 'WeoE3', 'ThoE3', 'FroE3', 'SaoE3', 'SuoE4', 'MooE4', 'TuoE4', 'WeoE4', 'ThoE4', 'FroE4', 'SaoE4', 'SuoE5', 'MooE5', 'TuoE5', 'WeoE5', 'ThoE5', 'FroE5', 'SaoE5', 'SuoE6', 'MooE6', 'TuoE6', 'WeoE6', 'ThoE6', 'FroE6', 'SaoE6', 'AotL', 'MooE7', 'TuoE7', 'WeoE7', 'ThoE7', 'FroE7', 'SaoE7', 'Pentecost' ),
		);

		// Now we add the days until the Pentecost Sunday.
		foreach ( $days_until_pentecost[ (int) $traditional_ascension ] as $day_abbr ) {
			$calendar[ $start->format( 'd.m.Y' ) ] = $day_abbr;
			$start->add( $odi );
		}

		// Now we need to know the beginning of Advent.
		$ce = new \DateTime( $liturgical_year . '-11-27' );
		while ( intval( $ce->format( 'w' ) ) !== 0 ) {
			$ce->add( $odi );
		}

		// Go to the last day of our liturgical year and reset Week in Ordinary Time to 35.
		// The current day is Saturday and our loop decreases WiOT by 1 every Saturday,
		// so we will be starting with 34th week.
		$ce->sub( $odi );
		$week_in_ot = 35;

		$days_tmp = array();
		// Now we go back in time.
		while ( $start->diff( $ce )->format( '%R' ) === '+' ) {
			if ( $ce->format( 'w' ) === '6' ) {
				$week_in_ot--;
			}

			if ( $start->diff( $ce )->format( '%a' ) === '6' ) {
				$days_tmp[ $ce->format( 'd.m.Y' ) ] = 'SotMHT';
			} else if ( $start->diff( $ce )->format( '%a' ) === '13' ) {
				$days_tmp[ $ce->format( 'd.m.Y' ) ] = 'SotMHBaBoC';
			} else {
				$days_tmp[ $ce->format( 'd.m.Y' ) ] = $short_days[ (int) $ce->format( 'w' ) ] . 'iOT' . $week_in_ot;
			}

			$ce->sub( $odi );
		}

		$calendar = \array_merge( $calendar, \array_reverse( $days_tmp, true ) );

		/**
		 * Undocumented var.
		 *
		 * @var \wpdb
		 */
		global $wpdb;
		foreach ( $calendar as $cal_date => $cal_ref ) {
			$wpdb->insert(
				$wpdb->prefix . 'calendar',
				array(
					'date' => $cal_date,
					'reference' => $cal_ref,
					'liturgical_year' => $liturgical_year,
				)
			);
		}
	}
}
