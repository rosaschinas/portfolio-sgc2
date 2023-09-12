<?php
/**
 * Business Info widget
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
class BusinessInfo extends API {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'mw_business_info',
			'name'    => $this->branding() . esc_html__( 'Business Info', 'more-widgets' ),
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
					'id'    => 'address',
					'label' => esc_html__( 'Address', 'more-widgets' ),
					'type'  => 'textarea',
				),
				array(
					'id'    => 'phone_number',
					'label' => esc_html__( 'Phone Number', 'more-widgets' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'phone_number_mobile',
					'label' => esc_html__( 'Mobile Phone Number', 'more-widgets' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'phone_number_tel_link',
					'label' => esc_html__( 'Add "tel" link to the phone number?', 'more-widgets' ),
					'type'  => 'checkbox',
				),
				array(
					'id'    => 'fax_number',
					'label' => esc_html__( 'Fax Number', 'more-widgets' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'email',
					'label' => esc_html__( 'Email', 'more-widgets' ),
					'type'  => 'text',
				),
				array(
					'id'          => 'email_label',
					'label'       => esc_html__( 'Email Label', 'more-widgets' ),
					'type'        => 'text',
					'description' => esc_html__( 'Will display your email by default if this field is empty.', 'more-widgets' ),
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

		$output .= '<div class="mw-binfo mw-clr">';

		// Address
		if ( $address ) {

			$output .= '<div class="mw-binfo-address mw-clr">';

				$output .= '<span class="mw-icon mw-icon-location" aria-hidden="true"></span>';

				$output .= '<div class="mw-binfo-v">' . wpautop( wp_kses_post( $address ) ) . '</div>';

			$output .= '</div>';

		}

		// Phone number
		if ( $phone_number ) {

			$output .= '<div class="mw-binfo-phone mw-clr">';

				$output .= '<span class="mw-icon mw-icon-phone" aria-hidden="true"></span>';

				if ( 'on' == $phone_number_tel_link ) {

					$output .= '<a href="tel:' . strip_tags( $phone_number ) . '">' . strip_tags( $phone_number ) . '</a>';

				} else {

					$output .= '<div class="mw-binfo-v">' . strip_tags( $phone_number ) . '</div>';

				}

			$output .= '</div>';

		}

		// Phone number mobile
		if ( $phone_number_mobile ) {

			$output .= '<div class="mw-binfo-phone-mobile mw-clr">';

				$output .= '<span class="mw-icon mw-icon-mobile" aria-hidden="true"></span>';

				$output .= '<div class="mw-binfo-v">';

					if ( 'on' == $phone_number_tel_link ) {

						$output .= '<a href="tel:' . strip_tags( $phone_number_mobile ) . '">' . strip_tags( $phone_number_mobile ) . '</a>';

					} else {

						$output .= strip_tags( $phone_number_mobile );

					}

				$output .= '</div>';

			$output .= '</div>';

		}

		// Fax number
		if ( $fax_number ) {

			$output .= '<div class="mw-binfo-fax mw-clr">';

				$output .= '<span class="mw-icon mw-icon-fax" aria-hidden="true"></span>';

				$output .= '<div class="mw-binfo-v">' . strip_tags( $fax_number ) . '</div>';

			$output .= '</div>';

		}

		// Email
		if ( $email ) {

			// Sanitize email
			$sanitize_email = sanitize_email( $email );
			$is_email       = is_email( $sanitize_email );

			// Spam protect email address
			$protected_email = $is_email ? antispambot( $sanitize_email ) : $sanitize_email;

			// Sanitize & fallback for email label
			$email_label = ( ! $email_label && $is_email ) ? $protected_email : $email_label;

			// Email title attribute
			$title_attr = $email_label ? $email_label : esc_html__( 'Email Us', 'more-widgets' );

			// Email output
			$output .= '<div class="mw-binfo-email mw-clr">';

				$output .= '<span class="mw-icon mw-icon-mail" aria-hidden="true"></span>';

				$output .= '<div class="mw-binfo-v">';

					if ( $is_email ) {

						$output .= '<a href="mailto:' . $protected_email . '" title="' . esc_attr( $title_attr ) . '">' . strip_tags( $email_label ) . '</a>';

					} else {

						$parse_email_url = parse_url( $email );

						if ( ! empty( $parse_email_url[ 'scheme' ] ) ) {
							$output .= '<a href="' . esc_url( $email ) . '" title="' . $email_label . '">' . $email_label . '</a>';
						} else {
							$output .= strip_tags( $email_label );
						}

					}

				$output .= '</div>';

			$output .= '</div>';

		}

		$output .= '</div>';

		// Echo output
		echo $output;

		// After widget hook
		echo $args[ 'after_widget' ];

	}

}
register_widget( 'MoreWidgets\BusinessInfo' );