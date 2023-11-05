<?php
/**
 * Undocumented file
 *
 * @package OrderOfMass
 */

namespace TMD\OrderOfMass\Plugin;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

/**
 * Undocumented class
 */
class Log extends AbstractLogger {
	/**
	 * Undocumented function
	 *
	 * @param mixed              $level   Level.
	 * @param string|\Stringable $message Message.
	 * @param array              $context Context.
	 * @throws InvalidArgumentException Level is wrong.
	 * @return void
	 */
	public function log( $level, string|\Stringable $message, array $context = array() ): void {
		if ( in_array( $level, array( LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG ), true ) !== true ) {
			throw new InvalidArgumentException( 'Paramer $level "' . esc_html( (string) $level ) . '" for function Log::log is incorrect.' );
		}
		$log_file = __DIR__ . '/../log.txt';
		$log_file_handle = fopen( $log_file, 'a' );
		if ( false === $log_file_handle ) {
			return;
		}
		try {
			$log_message_context = '';
			if ( count( $context ) > 0 ) {
				/**
				 * Using var_export is OK here.
				 *
				 * @psalm-suppress ForbiddenCode
				 */
				$log_message_context = PHP_EOL . var_export( $context, true );
			}
			$log_message = sprintf(
				'(%1$s)[%2$s] %3$s%4$s%5$s%5$s%6$s%5$s%5$s',
				gmdate( 'c' ),
				$level,
				is_string( $message ) ? $message : $message->__toString(),
				$log_message_context,
				PHP_EOL,
				str_repeat( '#', 80 )
			);
			fwrite( $log_file_handle, $log_message );
		} finally {
			fclose( $log_file_handle );
		}
	}
}
