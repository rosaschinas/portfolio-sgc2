<?php
/**
 * Advanced Recent Posts Widget
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
class AdvancedRecentPosts extends API {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'mw_advanced_recent_posts',
			'name' => $this->branding() . esc_html__( 'Advanced Recent Posts', 'more-widgets' ),
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
					'id'      => 'style',
					'label'   => esc_html__( 'Style', 'more-widgets' ),
					'type'    => 'select',
					'default' => 'small-image',
					'choices' => array(
						'small-image' => esc_html__( 'Small Image', 'more-widgets' ),
						'full-image'  => esc_html__( 'Full Image', 'more-widgets' ),
					),
				),
				array(
					'id'      => 'image_shape',
					'label'   => esc_html__( 'Image Shape', 'more-widgets' ),
					'type'    => 'select',
					'choices' => array(
						'square'  => esc_html__( 'Square', 'more-widgets' ),
						'rounded' => esc_html__( 'Rounded', 'more-widgets' ),
						'round'   => esc_html__( 'Round', 'more-widgets' ),
					),
				),
				array(
					'id'      => 'image_size',
					'label'   => esc_html__( 'Image Size', 'more-widgets' ),
					'type'    => 'select',
					'default' => 'thumbnail',
					'choices' => 'intermediate_image_sizes',
				),
				array(
					'id'      => 'image_width',
					'label'   => esc_html__( 'Image Width (in pixels)', 'more-widgets' ),
					'type'    => 'text',
					'placeholder' => '60px',
					'description' => esc_html__( 'Enter a custom width for the image. This will not alter the image itself but the container the image is located in.', 'more-widgets' ),
				),
				array(
					'id'      => 'show_title',
					'label'   => esc_html__( 'Show Title?', 'more-widgets' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'id'      => 'show_date',
					'label'   => esc_html__( 'Show Date?', 'more-widgets' ),
					'type'    => 'checkbox',
					'default' => true,
				),
				array(
					'id'      => 'show_excerpt',
					'label'   => esc_html__( 'Show Excerpt?', 'more-widgets' ),
					'type'    => 'checkbox',
					'default' => false,
				),
				array(
					'id'          => 'excerpt_length',
					'label'       => esc_html__( 'Custom Excerpt Length', 'more-widgets' ),
					'type'        => 'text',
					'description' => esc_html__( 'Number of words for a custom excerpt. Leave empty to use the default WordPress get_excerpt() function.', 'more-widgets' ),
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

			// Begin entries output
			$output .= '<div class="mw-advanced-recent-posts mw-clr';

				if ( $style ) {

					$output .= ' mw-style-' . esc_attr( $style );

				}

			$output .= '">';

					// Loop through posts
					while ( $mw_posts->have_posts() ) : $mw_posts->the_post();

						// Check thumb
						$has_thumb = has_post_thumbnail();

						// Item classes
						$item_classes = 'mw-advanced-recent-post mw-clr';
						if ( ! $has_thumb ) {
							$item_classes .= ' mw-no-thumb';
						}

						// Output entry
						$output .= '<div class="' . esc_attr( $item_classes ) . '">';

							// Get post link
							$post_link = get_permalink();

							// Entry thumbnail
							if ( $has_thumb ) {

								$output .= '<div class="mw-advanced-recent-post-thumb"';

									$image_width = absint( $image_width );

									if ( ! empty( $image_width ) ) {

										$output .= ' style="width:' . $image_width . 'px"';

									}

								$output .= '>';

									$output .= '<a href="' . esc_url( $post_link ) . '" title="' . esc_attr( the_title_attribute( array( 'echo' => false ) ) ) . '">';

									$image_attr = array();

									if ( $image_shape && in_array( $image_shape, array( 'rounded', 'round' ) ) ) {

										$image_attr[ 'class' ] = esc_attr( 'mw-' . $image_shape );

									}

									$output .= get_the_post_thumbnail( get_the_ID(), $image_size, $image_attr );

									$output .= '</a>';

								$output .= '</div>';

							}

							// Entry details
							$output .= '<div class="mw-advanced-recent-post-content mw-clr">';

								if ( wp_validate_boolean( $show_title ) ) {

									$output .= '<div class="mw-advanced-recent-post-title""><a href="' . esc_url( $post_link ) . '">' . esc_html( get_the_title() ) . '</a></div>';

								}

								// Display date if enabled
								if ( wp_validate_boolean( $show_date ) ) {

									$output .= '<div class="mw-advanced-recent-post-date">' . esc_html( get_the_date() ) . '</div>';

								}

								// Display excerpt
								if ( wp_validate_boolean( $show_excerpt ) ) {

									if ( $excerpt_length ) {

										$excerpt = wp_trim_words( get_the_content(), absint( $excerpt_length ) );

									} else {

										$excerpt = get_the_excerpt();

									}

									if ( $excerpt ) {

										$output .= '<div class="mw-recent-post-excerpt">' . wp_kses_post( $excerpt ) . '</div>';

									}

								}

							$output .= '</div>';

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

}
register_widget( 'MoreWidgets\AdvancedRecentPosts' );