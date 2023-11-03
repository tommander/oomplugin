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
class Main {

	/**
	 * Undocumented variable
	 *
	 * @var Main|null
	 */
	private static ?Main $me = null;

	/**
	 * DI Container
	 *
	 * @var \DI\Container
	 */
	public $container;

	/**
	 * Creates a container
	 */
	public function __construct() {
		$container_builder = new \DI\ContainerBuilder();
		$container_builder->useAutowiring( false );
		// $container_builder->useAnnotations(false);
		$container_builder->addDefinitions(
			array(
				LoggerInterface::class => \DI\create( Log::class )->constructor(),
				BCP47::class => \DI\create( BCP47::class )->constructor(
					\DI\get( LoggerInterface::class )
				),
				Commands::class => \DI\create( Commands::class )->constructor(
					\DI\get( LoggerInterface::class )
				),
				Mysteries::class => \DI\create( Mysteries::class )->constructor(
					\DI\get( LoggerInterface::class )
				),
				Parameters::class => \DI\create( Parameters::class )->constructor(
					\DI\get( LoggerInterface::class )
				),
				Bible::class => \DI\create( Bible::class )->constructor(
					\DI\get( BCP47::class ),
					\DI\get( LoggerInterface::class ),
					\DI\get( Parameters::class )
				),
				Calendar::class => \DI\create( Calendar::class )->constructor(
					\DI\get( LoggerInterface::class )
				),
				Conditional::class => \DI\create( Conditional::class )->constructor(
					\DI\get( LoggerInterface::class ),
					\DI\get( Parameters::class )
				),
				Labels::class => \DI\create( Labels::class )->constructor(
					\DI\get( BCP47::class ),
					\DI\get( LoggerInterface::class ),
					\DI\get( Parameters::class )
				),
				Lectionary::class => \DI\create( Lectionary::class )->constructor(
					\DI\get( Calendar::class ),
					\DI\get( LoggerInterface::class ),
					\DI\get( Parameters::class )
				),
				VirtualPage::class => \DI\create( VirtualPage::class )->constructor(
					\DI\get( BCP47::class ),
					\DI\get( LoggerInterface::class )
				),
			)
		);
		$this->container = $container_builder->build();
	}//end __construct()

	/**
	 * Undocumented function
	 *
	 * @return Main
	 */
	public static function new() {
		if ( null === self::$me ) {
			self::$me = new self();
		}
		return self::$me;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function run() {
		$commands = $this->container->get( Commands::class );
		$conditional = $this->container->get( Conditional::class );
		$labels = $this->container->get( Labels::class );
		$lectionary = $this->container->get( Lectionary::class );
		$virtual_page = $this->container->get( VirtualPage::class );

		$commands->register();
		$conditional->register();
		$labels->register();
		$lectionary->register();
		$virtual_page->register();
	}
}
