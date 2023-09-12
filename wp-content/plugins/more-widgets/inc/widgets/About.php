<?php
/**
 * About widget
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
class About extends API {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'mw_about',
			'name' => $this->branding() . esc_html__( 'About', 'more-widgets' ),
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
					'id'    => 'image',
					'label' => esc_html__( 'Image', 'more-widgets' ),
					'type'  => 'media_upload',
				),
				array(
					'id'      => 'img_style',
					'label'   => esc_html__( 'Image Style', 'more-widgets' ),
					'type'    => 'select',
					'choices' => array(
						''        => esc_html__( 'Default', 'more-widgets' ),
						'rounded' => esc_html__( 'Rounded', 'more-widgets' ),
						'round'   => esc_html__( 'Round', 'more-widgets' ),
					),
				),
				array(
					'id'      => 'alignment',
					'label'   => esc_html__( 'Alignment', 'more-widgets' ),
					'type'    => 'select',
					'choices' => array(
						''       => esc_html__( 'Default', 'more-widgets' ),
						'left'   => esc_html__( 'Left', 'more-widgets' ),
						'center' => esc_html__( 'Center', 'more-widgets' ),
						'right'  => esc_html__( 'Right', 'more-widgets' ),
					),
				),
				array(
					'id'    => 'description',
					'label' => esc_html__( 'Description', 'more-widgets' ),
					'type'  => 'textarea',
				),
				array(
					'id'    => 'wpautop',
					'label' => esc_html__( 'Automatically add paragraphs', 'more-widgets' ),
					'type'  => 'checkbox',
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
		echo $args['before_widget'];

		// Display widget title
		$this->widget_title( $args, $instance );

		// Define widget output
		$output = '';

		// Wrap classes
		$classes = 'mw-about mw-clr';
		if ( $alignment ) {
			$classes .= ' mw-txt-' . $alignment;
		}

		// Begin widget wrap
		$output .= '<div class="' . esc_attr( $classes ) . '">';

		// Allow ID based images.
		if ( is_numeric( $image ) ) {
			$image = wp_get_attachment_image_url( $image, $img_size );
		}

		// Display the image
		if ( $image ) {

			// Image classes
			$img_class = ( 'round' == $img_style || 'rounded' == $img_style ) ? ' class="mw-' . $img_style . '"' : '';

			$output .= '<div class="mw-about-image">';

				$output .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr( $title ) . '"' . $img_class . ' />';

			$output .= '</div>';

		}

		// Display the description
		if ( $description ) {

			$output .= '<div class="mw-about-description mw-clr">';

				if ( 'on' == $wpautop ) {
					$output .= wpautop( wp_kses_post( $description ) );
				} else {
					$output .= wp_kses_post( $description );
				}

			$output .= '</div>';

		}

		// Close widget wrap
		$output .= '</div>';

		// Echo output
		echo $output;

		// After widget hook
		echo $args['after_widget'];

	}

}
register_widget( 'MoreWidgets\About' );