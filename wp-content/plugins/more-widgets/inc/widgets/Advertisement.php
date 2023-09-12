<?php
/**
 * Advertisement widget
 *
 * @package MoreWidgets
 * @version 1.0
 */

namespace MoreWidgets;

// Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

// Start class
class Advertisement extends API {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'mw_advertisement',
			'name' => $this->branding() . esc_html__( 'Advertisement', 'more-widgets' ),
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
					'id'      => 'columns',
					'label'   => esc_html__( 'Columns', 'more-widgets' ),
					'type'    => 'select',
					'choices' => array(
						'1' => '1',
						'2' => '2',
					),
				),
				array(
					'id'      => 'columns_gap',
					'label'   => esc_html__( 'Column Gap', 'more-widgets' ),
					'type'    => 'select',
					'choices' => 'grid_gaps',
					'default' => 10,
				),
				array(
					'id'    => 'stretch_img',
					'label' => esc_html__( 'Stretch Images?', 'more-widgets' ),
					'type'  => 'checkbox',
					'description' => esc_html__( 'Force a 100% width on the advertisement images so they fill up the parent container.', 'more-widgets' ),
				),
				array(
					'id'    => 'nofollow',
					'label' => esc_html__( 'Add "nofollow" to links?', 'more-widgets' ),
					'type'  => 'checkbox',
				),
				array(
					'id'    => 'target_blank',
					'label' => esc_html__( 'Open links in a new tab?', 'more-widgets' ),
					'type'  => 'checkbox',
				),
				array(
					'id'    => 'ads',
					'label' => esc_html__( 'Ads', 'more-widgets' ),
					'type'  => 'repeater',
					'fields' => array(
						array(
							'id' => 'url',
							'label' => esc_html__( 'Link URL', 'more-widgets' ),
							'type'  => 'text',
						),
						array(
							'id' => 'image',
							'label' => esc_html__( 'Image', 'more-widgets' ),
							'type'  => 'media_upload',
						),
					),
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
		echo $args[ 'before_widget' ];

		// Display widget title
		$this->widget_title( $args, $instance );

		// Define widget output
		$output = '';

		if ( $ads && is_array( $ads ) ) {

			$output .= '<div class="mw-adverts mw-row mw-rs-' . esc_attr( $columns_gap ) .' mw-clr">';

			$count = 0;

			foreach ( $ads as $ad ) :

				if ( empty( $ad[ 'url' ] ) && empty( $ad[ 'image' ] ) ) {
					continue;
				}

				$count ++;

				if ( $columns > 1 ) {
					if ( 1 == $count ) {
						$position = ' mw-first';
					} elseif ( $columns == $count ) {
						$position = ' mw-last';
					} else {
						$position = '';
					}
				} else {
					$position = '';
				}

				$output .= '<div class="mw-col mw-col-' . esc_attr( $columns ) . $position . '">';

				$image = $ad[ 'image' ];
				$alt = '';

				if ( is_numeric( $image ) ) {
					$alt = get_post_meta( $image, '_wp_attachment_image_alt', TRUE );
					$image = wp_get_attachment_url( $image );
				}

				if ( $image ) {

					// Add link tag
					$output .= '<a href="' . esc_url( $ad[ 'url' ] ) . '"';

						if ( wp_validate_boolean( $target_blank ) ) {

							$output .= ' target="_blank"';

						}

						if ( wp_validate_boolean( $nofollow ) ) {

							$output .= ' rel="nofollow"';

						}

					$output .= '>';

					// Display Image
					$output .= '<img src="' . esc_url( $image ) . '"';

						if ( $alt ) {

							$output .= ' alt="' . esc_attr( $alt ) . '"';

						}

						if ( wp_validate_boolean( $stretch_img ) ) {

							$output .= ' class="mw-stretch-img"';

						}

					$output .= '/>';

					$output .= '</a>';

				}

				$output .= '</div>';

			endforeach;

			if ( $columns == $count ) {
				$count = 0;
			}

			$output .= '</div>';

		}


		// Echo output
		echo $output;

		// After widget hook
		echo $args[ 'after_widget' ];

	}

}
register_widget( 'MoreWidgets\Advertisement' );