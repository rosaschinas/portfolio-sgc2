<?php
/**
 * Newsletter widget
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
class Newsletter extends API {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'mw_newsletter',
			'name'    => $this->branding() . esc_html__( 'Newsletter Form', 'more-widgets' ),
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
					'id'      => 'form_action',
					'label'   => esc_html__( 'Form Action URL', 'more-widgets' ),
					'type'    => 'text',
					'default' => '//wpexplorer.us1.list-manage.com/subscribe/post?u=9b7568b7c032f9a6738a9cf4d&id=7056c37ddf',
				),
				array(
					'id'      => 'placeholder_text',
					'label'   => esc_html__( 'Placeholder Text', 'more-widgets' ),
					'type'    => 'text',
					'default' => esc_html__( 'Your email address', 'more-widgets' ),
				),
				array(
					'id'      => 'button_text',
					'label'   => esc_html__( 'Button Text', 'more-widgets' ),
					'type'    => 'text',
					'default' => esc_html__( 'Sign Up', 'more-widgets' ),
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

		extract( $this->parse_instance( $instance ) );

		echo $args[ 'before_widget' ];

		$this->widget_title( $args, $instance );

		$output = '<div class="mw-newsletter mw-clr">';

			$output .= '<form action="'. esc_attr( $form_action ) .'" method="post" class="validate" target="_blank" novalidate>';

				$output .= '<span class="screen-reader-text">' . esc_html( $placeholder_text ) . '</span>';

				$output .= '<input type="email" name="EMAIL" placeholder="' . esc_attr( $placeholder_text ) . '" autocomplete="off">';

				$output .= apply_filters( 'mw_newsletter_widget_form_extras', null );

				$output .= '<button type="submit" value="" name="subscribe">' . wp_strip_all_tags( $button_text ) . '</button>';

			$output .= '</form>';

		$output .= '</div>';

		echo $output;

		echo $args[ 'after_widget' ];

	}

}
register_widget( 'MoreWidgets\Newsletter' );