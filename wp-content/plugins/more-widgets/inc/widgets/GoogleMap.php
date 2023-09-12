<?php
/**
 * GoogleMap widget
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
class GoogleMap extends API {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'mw_gmap',
			'name'    => $this->branding() . esc_html__( 'Google Map', 'more-widgets' ),
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
					'id'    => 'place',
					'label' => esc_html__( 'Place (Location) Name', 'more-widgets' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'height',
					'label' => esc_html__( 'Custom Height (in pixels)', 'more-widgets' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'width',
					'label' => esc_html__( 'Custom Width (in pixels)', 'more-widgets' ),
					'type'  => 'text',
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
		echo $args[ 'before_widget' ];

		// Display widget title
		$this->widget_title( $args, $instance );

		// Define widget output
		$output = '';

		$output .= '<div class="mw-gmap">';

			if ( $description ) {

				$output .= '<div class="mw-gmap-description mw-clr">';

					if ( 'on' == $wpautop ) {
						$output .= wpautop( wp_kses_post( $description ) );
					} else {
						$output .= wp_kses_post( $description );
					}

				$output .= '</div>';

			} ?>

			<?php

			if ( $place ) {

				$width  = $width ? preg_replace( '/\D/', '', $width ) : '';
				$height = $height ? preg_replace( '/\D/', '', $height ) : '';

				$output .= '<div class="mapouter"><div class="gmap_canvas">';

					$output .= '<iframe width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" src="https://maps.google.com/maps?q=' . rawurlencode( esc_attr( $place ) ) . '&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>';

				$output .= '</div></div>';

			}

		$output .= '</div>';

		// Echo output
		echo $output;

		// After widget hook
		echo $args[ 'after_widget' ];

	}

}
register_widget( 'MoreWidgets\GoogleMap' );