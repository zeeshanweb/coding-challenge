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
				$post_count       = ( 'attachment' === $post_type_slug ) ? wp_count_posts( $post_type_slug )->inherit : wp_count_posts( $post_type_slug )->publish;
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
				$post_id = ! empty( get_the_ID() ) ? get_the_ID() : 0;
				/* translators: %d is post id */
				echo sprintf( esc_html__( 'The current post ID is %d', 'site-counts' ), absint( $post_id ) );
				?>
			</p>
			<?php
				$posts_per_page    = 5;
				$meta_value_filter = 'Accepted';
				$tag_filter        = 'foo';
				$cat_filter        = 'baz';
				$post_after_hour   = 9;
				$post_before_hour  = 17;
				$query             = new WP_Query(
					array(
						'post_type'      => array( 'post', 'page' ),
						'posts_per_page' => $posts_per_page,
						'post_status'    => 'any',
						'date_query'     => array(
							array(
								'hour'    => $post_after_hour,
								'compare' => '>=',
							),
							array(
								'hour'    => $post_before_hour,
								'compare' => '<=',
							),
						),
						'tag'            => $tag_filter,
						'category_name'  => $cat_filter,
						'post__not_in'   => array( $post_id ),
						'meta_value'     => $meta_value_filter,
					)
				);
			if ( $query->have_posts() ) :
				?>
				<h2>
				<?php
				/* translators: 1: total no of posts 2: tag name 3: category name 4: meta value 5: pots after hour 6: post before hour */
				echo sprintf( esc_html__( 'Any %1$d posts with the tag of %2$s and the category of %3$s where the custom field value is %4$s, regardless of the custom field key and posts between %5$sAM to %6$sPM', 'site-counts' ), absint( $posts_per_page ), esc_html( $tag_filter ), esc_html( $cat_filter ), esc_html( $meta_value_filter ), esc_html( $post_after_hour ), esc_html( $post_before_hour ) );
				?>
				</h2>
				<ul>
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
					<li><?php the_title(); ?></li>
					<?php
				endwhile; else :
					?>
				<p><?php esc_html_e( 'Sorry, no posts matched your criteria.', 'site-counts' ); ?></p>
				</ul>
					<?php
			endif;
				wp_reset_postdata();
				?>
		</div>
		<?php
		return ob_get_clean();
	}
}
