<?php
/**
 * The template for displaying the Page Tree page.
 *
 * Template name: Site Page Tree
 *
 * @package site-tree
 */

get_header();

$object_type = ! empty( $_GET['post-type'] ) ? $_GET['post-type'] : 'page';

global $current_page_id;

$current_page_id = get_the_ID();
?>
<div class="tree-wrap">
	<h1 class="page__title heading--leituranews heading">
		NU 2.0 Website Tree w/ Images
	</h1>
	<h4 class="heading heading--leituranews heading--three heading--underlined  mb-12">
		PDF Generated on: <?php echo esc_html( date( 'n-j-Y' ) ); ?>
	</h4>
	<?php
	generate_pages( $object_type, 0 );

	function generate_pages( $object_type, $level_index ) {
		global $current_page_id;

		$args  = array(
			'post_type'      => $object_type,
			'post_parent'    => 0 !== $level_index ? get_the_ID() : 0,
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'orderby'        => 'title',
		);
		$query = new WP_Query( $args );

		if ( $query->have_posts() ) :
			echo '<ul>';
			while ( $query->have_posts() ) :
				$query->the_post();

				$post_id = get_the_ID();

				// Check if this is the current page and skip it, so it doesn't get displayed in the tree.
				if ( $current_page_id === $post_id ) {
					continue;
				}

				$image          = nu_get_hero_image_data( $post_id );
				$children_pages = get_pages( 'child_of=' . $post_id );
				?>
				<li class="page-level page-level--<?php echo esc_attr( $level_index ); ?>">
					<a target="_blank" href="<?php the_permalink(); ?>"><span>(<?php echo get_the_ID(); ?>) -</span> <?php the_title(); ?></a>
					<img src="<?php echo esc_url( $image['src'] ); ?>" />

					<?php
					if ( count( $children_pages ) ) { // falsy value check.
						generate_pages( $object_type, $level_index + 1 );
					}
					?>
				</li>
				<?php
			endwhile;
			echo '</ul>';
		endif;
	}
	?>
</div>

<?php
get_footer();
