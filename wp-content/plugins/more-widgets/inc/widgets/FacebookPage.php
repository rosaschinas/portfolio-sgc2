<?php
/**
 * FacebookPage widget
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
class FacebookPage extends API {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0'
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'mw_facebook_page',
			'name'    => $this->branding() . esc_html__( 'Facebook Page', 'more-widgets' ),
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
					'id'      => 'facebook_url',
					'label'   => esc_html__( 'Facebook Page URL', 'more-widgets' ),
					'type'    => 'text',
					'default' => 'https://www.facebook.com/WPExplorerThemes/'
				),
				array(
					'id'      => 'language',
					'label'   => esc_html__( 'Language Locale', 'more-widgets' ),
					'type'    => 'text',
					'default' => 'en_US'
				),
				array(
					'id'      => 'tabs',
					'label'   => esc_html__( 'Tabs', 'more-widgets' ),
					'type'    => 'select',
					'choices' => array(
						''                => esc_html__( '— None —', 'more-widgets' ),
						'timeline'        => esc_html__( 'Timeline', 'more-widgets' ),
						'events'          => esc_html__( 'Events', 'more-widgets' ),
						'timeline,events' => esc_html__( 'Timeline & Events', 'more-widgets' ),
					),
				),
				array(
					'id'    => 'small_header',
					'label' => esc_html__( 'Use small header', 'more-widgets' ),
					'type'  => 'checkbox',
				),
				array(
					'id'    => 'hide_cover',
					'label' => esc_html__( 'Hide Cover Photo', 'more-widgets' ),
					'type'  => 'checkbox',
				),
				array(
					'id'      => 'show_facepile',
					'label'   => esc_html__( 'Show Faces', 'more-widgets' ),
					'type'    => 'checkbox',
					'default' => 'on',
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

		// Show nothing in customizer to keep it fast
		if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {

			esc_html_e( 'Facebook widget does not display in the Customizer because it can slow things down.', 'more-widgets' );

		} elseif ( $facebook_url ) {

			$attrs = array(
				'class'                      => 'fb-page',
				'data-href'                  => esc_url( do_shortcode( $facebook_url ) ),
				'data-small-header'          => esc_attr( $small_header ),
				'data-adapt-container-width' => 'true',
				'data-hide-cover'            => esc_attr( $hide_cover ),
				'data-show-facepile'         => esc_attr( $show_facepile ),
				'data-width'                 => 500,
			);

			if ( $tabs ) {
				$attrs[ 'data-tabs' ] = $tabs;
			}

			$output = '<div';

				foreach ( $attrs as $name => $value ) {
					$output .= ' ' . $name . '=' . '"' . esc_attr( $value ) . '"';
				}

			$output .= '></div>';

			echo $output; ?>

			<div id="fb-root"></div>
			<script>(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.async=true; js.src = "//connect.facebook.net/<?php echo wp_strip_all_tags( $language ); ?>/sdk.js#xfbml=1&version=v4.0";
				fjs.parentNode.insertBefore(js, fjs);
			} ( document, 'script', 'facebook-jssdk' ) );</script>

		<?php }

		// After widget hook
		echo $args[ 'after_widget' ];

	}

}
register_widget( 'MoreWidgets\FacebookPage' );