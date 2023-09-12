<?php
/**
 * SocialProfiles widget
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
class SocialProfiles extends API {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->args = array(
			'id_base' => 'mw_social_profiles',
			'name' => $this->branding() . esc_html__( 'Social Profiles', 'more-widgets' ),
			'options' => array(
				'customize_selective_refresh' => true,
			),
			'fields' => array(
				array(
					'id'    => 'title',
					'label' => esc_html__( 'Title', 'more-widgets' ),
					'type'  => 'text',
				),
				array(
					'id'    => 'description',
					'label' => esc_html__( 'Description', 'more-widgets' ),
					'type'  => 'textarea',
				),
				array(
					'id'          => 'icon_dims',
					'label'       => esc_html__( 'Custom Icon Dimensions (in px)', 'more-widgets' ),
					'type'        => 'text',
					'placeholder' => '34px',
				),
				array(
					'id'          => 'icon_size',
					'label'       => esc_html__( 'Custom Icon Font Size (in px)', 'more-widgets' ),
					'type'        => 'text',
					'placeholder' => '16px',
				),
				array(
					'id'      => 'icon_shape',
					'label'   => esc_html__( 'Icon Shape', 'more-widgets' ),
					'type'    => 'select',
					'choices' => array(
						'rounded' => esc_html__( 'Rounded', 'more-widgets' ),
						'round'   => esc_html__( 'Round', 'more-widgets' ),
						'square'  => esc_html__( 'Square', 'more-widgets' ),
					),
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
					'id'    => 'profiles',
					'label' => esc_html__( 'Profiles', 'more-widgets' ),
					'type'  => 'repeater',
					'fields' => array(
						array(
							'id' => 'site',
							'label'   => esc_html__( 'Site', 'more-widgets' ),
							'type'    => 'select',
							'choices' => $this->social_profiles_choices(),
						),
						array(
							'id' => 'url',
							'label' => esc_html__( 'Link URL', 'more-widgets' ),
							'type'  => 'text',
						),
					),
				),
			),
		);

		$this->create_widget( $this->args );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		extract( $this->parse_instance( $instance ) );

		// Before widget hook
		echo $args[ 'before_widget' ];

		// Display widget title
		$this->widget_title( $args, $instance );

		// Description
		if ( $description ) {
			echo '<div class="mw-sp-desc">' . wpautop( wp_kses_post( $description ) ) . '</div>';
		}

		// Profiles
		if ( $profiles ) :

			$output = '<div id="mw-social-profiles" class="mw-clr">';

				$social_ops = $this->social_profiles_list();

				$output .= '<ul class="mw-sp-ul">';

					foreach ( $profiles as $k => $v ) :

						if ( is_customize_preview() ) {
							$url = isset( $v[ 'url' ] ) ?  esc_url( $v[ 'url' ] ) : '#';
						} else {
							$url = isset( $v[ 'url' ] ) ?  esc_url( $v[ 'url' ] ) : '';
						}

						if ( ! isset( $social_ops[ $v[ 'site' ] ] ) || ! $url ) {
							continue;
						}

						$inline_style = '';

						$icon_dims = absint( $icon_dims );

						if ( $icon_dims ) {
							$icon_dims = $icon_dims . 'px';
							$inline_style .= 'width:' . $icon_dims . ';height:' . $icon_dims . ';line-height: ' . $icon_dims .';';
						}

						if ( $icon_size ) {
							$inline_style .= 'font-size:' . absint( $icon_size ) . 'px;';
						}

						$output .= '<li class="mw-sp-' . esc_attr( $v[ 'site' ] ) . '">';

							$output .= '<a href="' . esc_url( $url ) . '"';

								$output .= ' class="mw-' . esc_attr( $icon_shape ) . '"';

								if ( wp_validate_boolean( $target_blank ) ) {
									$output .= ' target="_blank"';
								}

								if ( wp_validate_boolean( $nofollow ) ) {
									$output .= ' rel="nofollow"';
								}

								if ( $inline_style ) {
									$output .= ' style="' . esc_attr( $inline_style ) . '"';
								}

							$output .= '>';

								$output .= '<span class="' . $social_ops[ $v[ 'site' ] ][ 'icon_class' ] . '" aria-hidden="true"></span>';

								$output .= '<span class="screen-reader-text">' . $social_ops[ $v[ 'site' ] ][ 'name' ] . '</span>';

							$output .= '</a>';

						$output .= '</li>';

					endforeach;

				$output .= '</ul>';

			$output .= '</div>';

			echo $output;

		endif;

		echo $args[ 'after_widget' ];
	}

	/**
	 * Returns array of social options
	 *
	 * @return array
	 */
	public function social_profiles_list() {
		return apply_filters( 'mw_social_profiles_list', array(
			'buffer' => array(
				'name' => 'Buffer',
				'icon_class' => 'mw-icon mw-icon-buffer',
			),
			'dribbble' => array(
				'name' => 'Dribbble',
				'icon_class' => 'mw-icon mw-icon-dribbble',
			),
			'discord' => array(
				'name' => 'Discord',
				'icon_class' => 'mw-icon mw-icon-discord',
			),
			'email' => array(
				'name' => 'Email',
				'icon_class' => 'mw-icon mw-icon-email',
			),
			'facebook' => array(
				'name' => 'Facebook',
				'icon_class' => 'mw-icon mw-icon-facebook',
			),
			'flickr' => array(
				'name' => 'Flickr',
				'icon_class' => 'mw-icon mw-icon-flickr',
			),
			'instagram' => array(
				'name' => 'Instagram',
				'icon_class' => 'mw-icon mw-icon-instagram',
			),
			'linkedin' => array(
				'name' => 'Linkedin',
				'icon_class' => 'mw-icon mw-icon-linkedin',
			),
			'paypal' => array(
				'name' => 'Paypal',
				'icon_class' => 'mw-icon mw-icon-paypal',
			),
			'pinterest' => array(
				'name' => 'Pinterest',
				'icon_class' => 'mw-icon mw-icon-pinterest',
			),
			'pocket' => array(
				'name' => 'Pocket',
				'icon_class' => 'mw-icon mw-icon-pocket',
			),
			'reddit' => array(
				'name' => 'Reddit',
				'icon_class' => 'mw-icon mw-icon-reddit',
			),
			'rss' => array(
				'name' => 'RSS',
				'icon_class' => 'mw-icon mw-icon-rss',
			),
			'tiktok' => array(
				'name' => 'TikTok',
				'icon_class' => 'mw-icon mw-icon-tiktok',
			),
			'twitter' => array(
				'name' => 'Twitter',
				'icon_class' => 'mw-icon mw-icon-twitter',
			),
			'vimeo' => array(
				'name' => 'Vimeo',
				'icon_class' => 'mw-icon mw-icon-vimeo',
			),
			'vk' => array(
				'name' => 'VK',
				'icon_class' => 'mw-icon mw-icon-vk',
			),
			'xing' => array(
				'name' => 'Xing',
				'icon_class' => 'mw-icon mw-icon-xing',
			),
			'yelp' => array(
				'name' => 'Yelp',
				'icon_class' => 'mw-icon mw-icon-yelp',
			),
			'youtube' => array(
				'name' => 'YouTube',
				'icon_class' => 'mw-icon mw-icon-youtube',
			),
		) );
	}

	/**
	 * Parses social profiles list to return choices for widget form
	 *
	 * @return array
	 */
	public function social_profiles_choices() {
		$choices = array();
		$list = $this->social_profiles_list();
		foreach( $list as $k => $v ) {
			$choices[$k] = $v['name'];
		}
		return $choices;
	}

}
register_widget( 'MoreWidgets\SocialProfiles' );