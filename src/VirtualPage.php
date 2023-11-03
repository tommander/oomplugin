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
class VirtualPage {

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
	 * Undocumented function
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_filter( 'display_post_states', array( $this, 'filter_post_states' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'filter_the_content' ) );
		add_action( 'save_post', array( $this, 'save_post_meta' ) );
	}

	/**
	 * Undocumented function
	 *
	 * @param BCP47           $bcp47 BCP47.
	 * @param LoggerInterface $log   Log.
	 */
	public function __construct( BCP47 $bcp47, LoggerInterface $log ) {
		$this->bcp47 = $bcp47;
		$this->log = $log;
	}

	/**
	 * Undocumented function
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string
	 */
	public function is_virtual_page( int $post_id ): string {
		$oom_virtual_page = get_post_meta( $post_id, 'oom-virtualpage', true );
		if ( in_array( $oom_virtual_page, array( '0', '1' ), true ) !== true ) {
			$oom_virtual_page = '0';
		}
		return $oom_virtual_page;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function render_metabox() {
		$oom_virtual_page = '0';
		$curr_post = get_post();
		if ( is_a( $curr_post, \WP_Post::class ) === true ) {
			$oom_virtual_page = $this->is_virtual_page( $curr_post->ID );
		}

		printf(
			<<<EOS
        <div class="oomvirtualpage_meta_container">
            <label for="oomvirtualpage_meta">
                <input type="checkbox" id="oomvirtualpage_meta" name="oom-virtualpage" value="1"%1\$s>
                Is virtual parent?
            </label>
        </div>
        EOS,
			checked( '1', $oom_virtual_page, false ),
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param string $current_vp Current VP.
	 *
	 * @return void
	 */
	public function render_options( string $current_vp ) {
		$query = new \WP_Query( array( 'post_type' => 'page' ) );
		try {
			while ( $query->have_posts() === true ) {
				$query->the_post();
				$post = get_post();
				if ( is_a( $post, \WP_Post::class ) !== true ) {
					continue;
				}

				$meta = $this->is_virtual_page( $post->ID );

				if ( '1' === $meta ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>' . "\r\n",
						esc_attr( $post->post_name ),
						selected( $current_vp, $post->post_name, false ),
						esc_html( $post->post_title )
					);
				}
			}//end while
		} finally {
			wp_reset_postdata();
		}//end try
	}

	/**
	 * Undocumented function
	 *
	 * @param string $current_vp       Current VP.
	 * @param string $current_vp_child Current VP child.
	 *
	 * @return void
	 */
	public function render_options_children( string $current_vp, string $current_vp_child ) {
		$type_posts = get_posts(
			array(
				'name' => $current_vp,
				'post_type' => 'page',
			)
		);
		if ( count( $type_posts ) <= 0 || is_a( $type_posts[0], \WP_Post::class ) !== true ) {
			$this->log->warning(
				'Unable to find virtual page.',
				array(
					'currentVP' => $current_vp,
					'count' => count( $type_posts ),
					'typePosts0' => $type_posts[0],
				)
			);
			return;
		}

		$query = new \WP_Query(
			array(
				'post_type' => 'page',
				'post_parent' => $type_posts[0]->ID,
			)
		);
		try {
			while ( $query->have_posts() === true ) {
				$query->the_post();
				$post = get_post();
				if ( is_a( $post, \WP_Post::class ) !== true ) {
					continue;
				}
				echo '<option value="' . esc_attr( $post->post_name ) . '"' . selected( $post->post_name, $current_vp_child, false ) . '>' . esc_html( $this->bcp47->get_lang_name( $post->post_name ) ) . '</option>';
			}//end while
		} finally {
			wp_reset_postdata();
		}//end try
	}

	/**
	 * Undocumented function
	 *
	 * @param array    $post_states Post states.
	 * @param \WP_Post $post        Post.
	 *
	 * @return array
	 */
	public function filter_post_states( array $post_states, \WP_Post $post ) {
		if ( $this->is_virtual_page( $post->ID ) === '1' ) {
			$post_states['oom_virtual'] = __( 'Virtual Page', 'order-of-mass' );
		}
		return $post_states;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $content Content.
	 *
	 * @return string
	 */
	public function filter_the_content( string $content ) {
		if ( is_singular() !== true || in_the_loop() !== true || is_main_query() !== true ) {
			return $content;
		}

		$post = get_post();
		if ( is_a( $post, \WP_Post::class ) !== true || 'page' !== $post->post_type ) {
			return $content;
		}

		if ( $this->is_virtual_page( $post->ID ) !== '1' ) {
			return $content;
		}

		$my_query = new \WP_Query();
		$all_pages = $my_query->query( array( 'post_type' => 'page' ) );
		$all_pages = array_filter(
			$all_pages,
			function ( $element ) {
				return is_a( $element, \WP_Post::class );
			}
		);

		$children = get_page_children( $post->ID, $all_pages );
		foreach ( $children as $child ) {
			if ( strcasecmp( $child->post_name, 'en-us' ) === 0 ) {
				return '<script type="text/javascript">window.location="' . get_permalink( $child ) . '";</script>';
			}
		}

		return $content;
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function init() {
		register_post_meta(
			'page',
			'oom-virtualpage',
			array(
				'type' => 'string',
				'description' => 'Virtual Page for OoM',
				'single' => true,
				'default' => '0',
			)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			'metabox_oom_virtualpage',
			'Virtual Page',
			array( $this, 'render_metabox' ),
			'page',
			'side'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function save_post_meta( int $post_id ) {
		if ( true !== isset( $_POST['oom-virtualpage-nonce'] ) || true !== is_string( $_POST['oom-virtualpage-nonce'] ) ) {
			return;
		}
		if ( false === wp_verify_nonce( sanitize_key( wp_unslash( $_POST['oom-virtualpage-nonce'] ) ), 'save_vp_meta' ) ) {
			return;
		}
		$post_unslashed = wp_unslash( $_POST );
		if ( array_key_exists( 'oom-virtualpage', $post_unslashed ) === true ) {
			update_post_meta( $post_id, 'oom-virtualpage', $post_unslashed['oom-virtualpage'] );
		}
	}
}
