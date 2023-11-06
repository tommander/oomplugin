<?php
/**
 * PHP Stub Cleaner
 *
 * @package OrderOfMass
 */

/**
 * Undocumented.
 *
 * @param string $namespace Namespace.
 * @param string $text      Text.
 * @param string $new_file  New file.
 *
 * @return void
 */
function extract_namespace( string $namespace, string $text, string &$new_file ): void {
	echo 'Searching namespace "' . esc_js( $namespace ) . '"...' . PHP_EOL;
	$matches = array();
	$regex = '/^([ \t]*)namespace ' . str_replace( '\\', '\\\\', $namespace ) . ' \{$.*?^\1\}/ms';
	echo 'Regex ' . esc_js( $regex ) . PHP_EOL;
	$match_result = preg_match( $regex, $text, $matches );
	if ( 1 !== $match_result ) {
		echo '[ERROR] Namespace not found (error 1)!' . PHP_EOL;
		exit( 1 );
	}
	if ( 0 === count( $matches ) ) {
		echo '[ERROR] Namespace not found (error 2)!' . PHP_EOL;
		exit( 1 );
	}

	echo 'Namespace found.' . PHP_EOL;
	$new_file .= $matches[0] . PHP_EOL;
}

try {
	echo 'PHP Stub Cleaner v1' . PHP_EOL . PHP_EOL;

	/**
	 * Undocumented.
	 *
	 * @var array{input: string,output:string,output-min:string}
	 */
	$opt = getopt(
		'',
		array( 'input:', 'output:', 'output-min:' )
	);

	if ( true !== file_exists( $opt['input'] ) ) {
		echo '[ERROR] Stub file does not exist!' . PHP_EOL;
		exit( 2 );
	}

	echo 'Input file: "' . esc_js( $opt['input'] ) . '".' . PHP_EOL;
	echo 'Output min file: "' . esc_js( $opt['output-min'] ) . '".' . PHP_EOL;
	echo 'Output file: "' . esc_js( $opt['output'] ) . '".' . PHP_EOL;

	echo 'Loading input file...' . PHP_EOL;
	$original_file = file_get_contents( $opt['input'] );

	$new_file = '<?php' . PHP_EOL;

	extract_namespace( 'TMD\OrderOfMass\Plugin', $original_file, $new_file );

	echo 'Saving to output min file...' . PHP_EOL;
	file_put_contents( $opt['output-min'], $new_file );

	extract_namespace( 'Invoker', $original_file, $new_file );
	extract_namespace( 'Invoker\Exception', $original_file, $new_file );
	extract_namespace( 'DI', $original_file, $new_file );
	extract_namespace( 'DI\Definition', $original_file, $new_file );
	extract_namespace( 'DI\Definition\Exception', $original_file, $new_file );
	extract_namespace( 'DI\Definition\Helper', $original_file, $new_file );
	extract_namespace( 'DI\Definition\ObjectDefinition', $original_file, $new_file );
	extract_namespace( 'DI\Definition\Resolver', $original_file, $new_file );
	extract_namespace( 'DI\Definition\Source', $original_file, $new_file );
	extract_namespace( 'DI\Factory', $original_file, $new_file );
	extract_namespace( 'DI\Proxy', $original_file, $new_file );

	echo 'Saving to output file...' . PHP_EOL;
	file_put_contents( $opt['output'], $new_file );

	echo 'Done.' . PHP_EOL;
	exit( 0 );
} catch ( Exception $exc ) {
	echo '[ERROR] ' . esc_js( $exc->__toString() ) . PHP_EOL;
}
