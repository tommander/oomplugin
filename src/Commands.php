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
class Commands {

	/**
	 * Undocumented variable
	 *
	 * @var Labels
	 */
	private Labels $labels;
	/**
	 * Undocumented variable
	 *
	 * @var LoggerInterface
	 */
	private LoggerInterface $log;

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
	 * @param Labels          $labels Labels.
	 * @param LoggerInterface $log    Log.
	 */
	public function __construct( Labels $labels, LoggerInterface $log ) {
		$this->labels = $labels;
		$this->log = $log;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function init() {
		add_shortcode( 'oomcommand', array( $this, 'shortcode' ) );
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
	public function shortcode( $atts, $content, $shortcode_tag ): string {
		$a = shortcode_atts(
			array(
				'block' => 'n',
			),
			$atts,
			$shortcode_tag
		);
		$prefix = 'fas';
		$name = '';
		$text = '';

		switch ( $content ) {
			case 'cross':
				$name = 'fa-cross';
				break;
			case 'walk':
				$name = 'fa-hiking';
				break;
			case 'peace':
				$prefix = 'far';
				$name = 'fa-handshake';
				break;
			case 'bible':
				$prefix = 'fas';
				$name = 'fa-book-bible';
				break;
			case 'reader':
				$prefix = 'fas';
				$name = 'fa-book-reader';
				break;
			case 'stand':
				$name = 'fa-person';
				$text = $this->labels->get_label( 'Standing' );
				break;
			case 'pray':
				$name = 'fa-person-praying';
				$text = $this->labels->get_label( 'Silent prayer' );
				break;
			case 'sit':
				$name = 'fa-chair';
				$text = $this->labels->get_label( 'Sitting' );
				break;
			case 'kneel':
				$name = 'fa-pray';
				$text = $this->labels->get_label( 'Kneeling' );
				break;
			case 'commbread':
				$name = 'fa-cookie-bite';
				$text = $this->labels->get_label( 'Holy Communion' );
				break;
			case 'commwine':
				$name = 'fa-wine-glass-alt';
				$text = $this->labels->get_label( 'Holy Communion' );
				break;
			case 'homily':
				$prefix = 'far';
				$name = 'fa-comment';
				$text = $this->labels->get_label( 'Homily' );
				break;
		}//end switch

		$cmd = sprintf(
			'<span class="command"><i class="%1$s %2$s"></i> %3$s</span>',
			$prefix,
			$name,
			$text
		);

		if ( 'y' === $a['block'] ) {
			$cmd = '<p>' . $cmd . '</p>';
		}

		return $cmd;
	}
}
