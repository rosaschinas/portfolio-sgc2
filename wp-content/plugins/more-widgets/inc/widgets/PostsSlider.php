<?php
/**
 * Posts Slider Widget
 *
 * @package MoreWidgets
 * @version 1.0
 */

namespace MoreWidgets;
use WP_Query;

// Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

// Start class
class PostsSlider extends API {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'mw_posts_slider',
			'name' => $this->branding() . esc_html__( 'Posts Slider', 'more-widgets' ),
			'options' => array(
				'customize_selective_refresh' => true,
			),
			'fields'  => array(
				array(
					'id'    => 'title',
					'label' => esc_html__( 'Title', 'more-widgets' ),
					'type'  => 'text',
				),
				array(
					'id'      => 'posts_per_page',
					'label'   => esc_html__( 'Number', 'more-widgets' ),
					'type'    => 'number',
					'default' => 4,
				),
				array(
					'id'      => 'image_size',
					'label'   => esc_html__( 'Image Size', 'more-widgets' ),
					'type'    => 'select',
					'default' => 'full',
					'choices' => 'intermediate_image_sizes',
				),
				array(
					'id'      => 'auto_play',
					'label'   => esc_html__( 'Auto Play?', 'more-widgets' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				array(
					'id'      => 'show_arrows',
					'label'   => esc_html__( 'Show Arrows?', 'more-widgets' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				array(
					'id'      => 'show_dots',
					'label'   => esc_html__( 'Show Dots?', 'more-widgets' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'id'      => 'show_title',
					'label'   => esc_html__( 'Show Title?', 'more-widgets' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'id'      => 'show_category',
					'label'   => esc_html__( 'Show Category?', 'more-widgets' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'id'      => 'ignore_sticky_posts',
					'label'   => esc_html__( 'Ignore Sticky Posts?', 'more-widgets' ),
					'type'    => 'checkbox',
				),
				array(
					'id'       => 'post_type',
					'label'    => esc_html__( 'Post Type', 'more-widgets' ),
					'type'     => 'select',
					'choices'  => 'post_types',
					'default'  => 'post',
				),
				array(
					'id'      => 'orderby',
					'label'   => esc_html__( 'Order by', 'more-widgets' ),
					'type'    => 'select',
					'choices' => 'query_orderby',
					'default' => 'date',
				),
				array(
					'id'      => 'order',
					'label'   => esc_html__( 'Order', 'more-widgets' ),
					'type'    => 'select',
					'choices' => 'query_order',
					'default' => 'DESC',
				),
				array(
					'id'      => 'taxonomy',
					'label'   => esc_html__( 'Query By Taxonomy', 'more-widgets' ),
					'type'    => 'select',
					'choices' => 'taxonomies',
					'description' => esc_html__( 'This setting also works for the category tag so it can be used to alter the taxonomy displayed.', 'more-widgets' ),
				),
				array(
					'id'          => 'terms',
					'label'       => esc_html__( 'Include Terms', 'more-widgets' ),
					'type'        => 'text',
					'description' => esc_html__( 'Enter a comma seperated list of terms.', 'more-widgets' ),
				),
				array(
					'id'          => 'terms_exclude',
					'label'       => esc_html__( 'Exclude Terms', 'more-widgets' ),
					'type'        => 'text',
					'description' => esc_html__( 'Enter a comma seperated list of terms.', 'more-widgets' ),
				),
				array(
					'id'          => 'query_callback',
					'label'       => esc_html__( 'Custom Query Arguments Callback', 'more-widgets' ),
					'type'        => 'text',
					'description' => esc_html__( 'Enter a custom callback function for a more advanced query. This callback should return an array of arguments to pass onto WP_Query.', 'more-widgets' ),
				),
			),
		);

		$this->create_widget( $this->args );

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @since 1.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		// Parse and extract widget settings
		extract( $this->parse_instance( $instance ) );

		// Before widget hook
		echo wp_kses_post( $args['before_widget'] );

		// Display widget title
		$this->widget_title( $args, $instance );

		// Define widget output
		$output = '';

		// Custom query args
		if ( ! empty( $query_callback )
			&& is_callable( $query_callback )
			&& is_array( $custom_args = call_user_func( $query_callback ) )
		) {

			$query_args = $custom_args;

		} else {

			$post_type = ! empty( $post_type ) ? $post_type : 'post';

			// Query posts
			$query_args = array(
				'post_type'      => array( $post_type ),
				'posts_per_page' => $posts_per_page,
				'no_found_rows'  => true,
				'tax_query'      => array(
					'relation' => 'AND',
				),
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key' => '_thumbnail_id',
						'compare' => 'EXISTS'
					),
				),
			);

			// Ignore sticky posts
			if ( wp_validate_boolean( $ignore_sticky_posts ) ) {
				$query_args[ 'ignore_sticky_posts' ] = true;
			}

			// Order params - needs FALLBACK don't ever edit!
			if ( ! empty( $orderby ) ) {
				$query_args[ 'order' ]   = $order;
				$query_args[ 'orderby' ] = $orderby;
			} else {
				$query_args[ 'orderby' ] = $order; // THIS IS THE FALLBACK
			}

			// Exclude current post
			if ( is_singular() ) {
				$query_args[ 'post__not_in' ] = array( get_the_ID() );
			}

			// Tax Query
			if ( ! empty( $taxonomy ) ) {

				// Include Terms
				if (  ! empty( $terms ) ) {

					// Sanitize terms and convert to array
					$terms = str_replace( ', ', ',', $terms );
					$terms = explode( ',', $terms );

					// Add to query arg
					$query_args[ 'tax_query' ][] = array(
						'taxonomy' => $taxonomy,
						'field'    => 'slug',
						'terms'    => $terms,
						'operator' => 'IN',
					);

				}

				// Exclude Terms
				if ( ! empty( $terms_exclude ) ) {

					// Sanitize terms and convert to array
					$terms_exclude = str_replace( ', ', ',', $terms_exclude );
					$terms_exclude = explode( ',', $terms_exclude );

					// Add to query arg
					$query_args[ 'tax_query' ][] = array(
						'taxonomy' => $taxonomy,
						'field'    => 'slug',
						'terms'    => $terms_exclude,
						'operator' => 'NOT IN',
					);

				}

			}

		}

		// Query posts
		$mw_posts = new WP_Query( $query_args );

		// If there are posts loop through them
		if ( post_type_exists( $post_type ) && $mw_posts->have_posts() ) :

			// Enqueue slider scripts
			$this->scripts();

			// Define slider settings
			$slider_settings = apply_filters( 'mw_posts_slider_flickity_options', array(
				'adaptiveHeight'  => true,
				'imagesLoaded'    => true,
				'wrapAround'      => true,
				'autoPlay'        => wp_validate_boolean( $auto_play ),
				'prevNextButtons' => wp_validate_boolean( $show_arrows ),
				'pageDots'        => wp_validate_boolean( $show_dots ),
			) );

			// Begin entries output
			$output .= '<div class="mw-posts-slider mw-clr" data-flickity="' . htmlspecialchars( wp_json_encode( $slider_settings ) ) . '">';

					// Loop through posts
					while ( $mw_posts->have_posts() ) : $mw_posts->the_post();

						$output .= '<div class="mw-posts-slider-item">';

							// Display image and title inside a link tag
							$output .= '<a href="' . esc_url( get_permalink() ) . '" title="' . esc_attr( the_title_attribute( array( 'echo' => false ) ) ) . '" class="mw-posts-slider-link">';

								$output .= '<div class="mw-posts-slider-thumb">';

									$output .= '<img src="' . get_the_post_thumbnail_url( get_the_ID(), $image_size ) .'">';

								$output .= '</div>';

								// Display Title
								if ( wp_validate_boolean( $show_title ) ) {

									$output .= '<div class="mw-posts-slider-title">' . esc_html( get_the_title() ) . '</div>';

								}

							$output .= '</a>';

							// Display category
							$taxonomy = $taxonomy ? $taxonomy : 'category';
							if ( taxonomy_exists( $taxonomy ) && wp_validate_boolean( $show_category ) ) {

								$terms = get_the_terms( get_the_ID(), $taxonomy );

								if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {

									$first_term = array_pop( $terms );

									$first_term = apply_filters( 'mw_posts_slider_category', $first_term, get_post() );

									$output .= '<a href="' . esc_url( get_term_link( $first_term->slug, $taxonomy ) ) . '" class="mw-posts-slider-tag">' . esc_html( $first_term->name ) . '</a>';

								}

							}

						$output .= '</div>';

					endwhile;

			$output .= '</div>';

			// Reset post data
			wp_reset_postdata();

		endif;

		// Echo output
		echo $output;

		echo $args[ 'after_widget' ];

	}

	/**
	 * Enqueue Scripts
	 */
	public function scripts() {

		wp_enqueue_script(
			'flickity',
			MORE_WIDGETS_PLUGIN_DIR_URL . 'assets/lib/flickity/flickity.pkgd.min.js',
			array( 'jquery' ),
			'2.2.1',
			true
		);

	}

}
register_widget( 'MoreWidgets\PostsSlider' );