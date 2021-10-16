<?php
/**
 * Block class.
 *
 * @package SiteCounts
 */

namespace XWP\SiteCounts;

use WP_Block;
use WP_Query;

/**
 * The Site Counts dynamic block.
 *
 * Registers and renders the dynamic block.
 */
class Block {

	/**
	 * The Plugin instance.
	 *
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * Instantiates the class.
	 *
	 * @param Plugin $plugin The plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Adds the action to register the block.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Registers the block.
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin->dir(),
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array    $attributes The attributes for the block.
	 * @param string   $content    The block content, if any.
	 * @param WP_Block $block      The instance of this block.
	 * @return string The markup of the block.
	 */
	public function render_callback( $attributes, $content, $block ) {
		$post_types = get_post_types( array( 'public' => true ) );
		$class_name = isset( $attributes['className'] ) ? $attributes['className'] : '';
		ob_start();
		?>
		<div class="<?php echo esc_attr( $class_name ); ?>">
			<h2><?php esc_html_e( 'Post Counts:', 'site-counts' ); ?></h2>
			<ul>
			<?php
			foreach ( $post_types as $post_type_slug ) {
				$post_type_object = get_post_type_object( $post_type_slug );
				$post_count       = ( 'media' === $post_type_slug ) ? wp_count_posts( $post_type_slug )->inherit : wp_count_posts( $post_type_slug )->publish;
				?>
				<li>
				<?php
				/* translators: 1: post count 2: post type label name */
				echo sprintf( esc_html__( 'There are %1$d %2$s', 'site-counts' ), absint( $post_count ), esc_html( $post_type_object->labels->name ) );
				?>
				</li>
				<?php
			}
			?>
			</ul>
			<p>
				<?php
				$post_id = isset( $_GET['post_id'] ) ? wp_unslash( $_GET['post_id'] ) : ''; // phpcs:ignore
				/* translators: %d is post id */
				echo sprintf( esc_html__( 'The current post ID is %d', 'site-counts' ), absint( $post_id ) );
				?>
			</p>
			<?php
				$query = new WP_Query(
					array(
						'post_type'      => array( 'post', 'page' ),
						'posts_per_page' => 5,
						'post_status'    => 'any',
						'date_query'     => array(
							array(
								'hour'    => 9,
								'compare' => '>=',
							),
							array(
								'hour'    => 17,
								'compare' => '<=',
							),
						),
						'tag'            => 'foo',
						'category_name'  => 'baz',
						'post__not_in'   => array( get_the_ID() ),
						'meta_value'     => 'Accepted',
					)
				);
			if ( $query->found_posts ) {
				?>
				<h2><?php esc_html_e( 'Any 5 posts with the tag of foo and the category of baz where the custom field value is ‘Accepted’, regardless of the custom field key and posts between 9AM to 5PM', 'site-counts' ); ?></h2>
				<ul>
				<?php
				foreach ( $query->posts as $post ) {
					?>
					<li><?php echo esc_html( $post->post_title ); ?></li>
					<?php
				}
				?>
				</ul>
				<?php
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}
}
