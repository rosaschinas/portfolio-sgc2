<?php
/**
 * Users widget
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
class Users extends API {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'mw_users',
			'name' => $this->branding() . esc_html__( 'Users', 'more-widgets' ),
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
					'id'      => 'order',
					'label'   => esc_html__( 'Order', 'more-widgets' ),
					'type'    => 'select',
					'choices' => 'query_order',
					'default' => 'ASC',
				),
				array(
					'id'      => 'orderby',
					'label'   => esc_html__( 'Orderby', 'more-widgets' ),
					'type'    => 'select',
					'choices' => array(
						'ID'           => esc_html__( 'ID', 'more-widgets' ),
						'login'        => esc_html__( 'Login', 'more-widgets' ),
						'nicename'     => esc_html__( 'Nicename', 'more-widgets' ),
						'email'        => esc_html__( 'Email', 'more-widgets' ),
						'url'          => esc_html__( 'URL', 'more-widgets' ),
						'registered'   => esc_html__( 'Registered', 'more-widgets' ),
						'display_name' => esc_html__( 'Display Name', 'more-widgets' ),
						'post_count'   => esc_html__( 'Post Count', 'more-widgets' ),
					),
					'default' => 'login',
				),
				array(
					'id'      => 'columns',
					'label'   => esc_html__( 'Columns', 'more-widgets' ),
					'type'    => 'select',
					'choices' => 'grid_columns',
					'default' => 4,
				),
				array(
					'id'      => 'columns_gap',
					'label'   => esc_html__( 'Column Gap', 'more-widgets' ),
					'type'    => 'select',
					'choices' => 'grid_gaps',
					'default' => 10,
				),
				array(
					'id'          => 'img_size',
					'label'       => esc_html__( 'Image Size', 'more-widgets' ),
					'type'        => 'text',
					'default'     => 50,
					'placeholder' => 50,
				),
				array(
					'id'    => 'admins',
					'label' => esc_html__( 'Include Administrators?', 'more-widgets' ),
					'type'  => 'checkbox',
					'std'   => 'on',
				),
				array(
					'id'    => 'editors',
					'label' => esc_html__( 'Include Editors?', 'more-widgets' ),
					'type'  => 'checkbox',
					'std'   => 'on',
				),
				array(
					'id'    => 'authors',
					'label' => esc_html__( 'Include Authors?', 'more-widgets' ),
					'type'  => 'checkbox',
					'std'  => 'on',
				),
				array(
					'id'    => 'contributors',
					'label' => esc_html__( 'Include Contributors?', 'more-widgets' ),
					'type'  => 'checkbox',
					'std'   => 'on',
				),
				array(
					'id'    => 'subscribers',
					'label' => esc_html__( 'Include Subscribers?', 'more-widgets' ),
					'type'  => 'checkbox',
				),
				array(
					'id'    => 'link_to_posts',
					'label' => esc_html__( 'Link to user posts page?', 'more-widgets' ),
					'type'  => 'checkbox',
					'std'   => 'on',
				),
				array(
					'id'    => 'show_name',
					'label' => esc_html__( 'Display Name?', 'more-widgets' ),
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

		// Query users
		$query_args = array(
			'orderby' => $orderby,
			'order'   => $order,
		);
		$role_in = array();
		if ( $admins ) {
			$role_in[] = 'administrator';
		}
		if ( $authors ) {
			$role_in[] = 'author';
		}
		if ( $contributors ) {
			$role_in[] = 'contributor';
		}
		if ( $subscribers ) {
			$role_in[] = 'subscriber';
		}
		if ( $role_in ) {
			$query_args[ 'role__in' ] = $role_in;
		}

		$get_users = get_users( $query_args );

		if ( $get_users ) {

			$columns     = $columns ? $columns : 4;
			$columns_gap = $columns_gap ? $columns_gap : 10;

			$output .= '<div class="mw-users mw-row mw-rs-' . esc_attr( $columns_gap ) .' mw-clr">';

				$count=0;

				foreach ( $get_users as $user ) :

					$count++;

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

						// Open link tag
						if ( $link_to_posts ) {

							$output .= '<a href="' . esc_url( get_author_posts_url( $user->ID, $user->user_nicename ) ) . '" title="' . esc_attr( $user->display_name ) . ' ' . esc_html__( 'Archive', 'more-widgets' ) . '">';

						}

						// Display avatar
						$output .= '<div class="mw-users-avatar">';

							$output .= get_avatar( $user->ID, $img_size, '', $user->display_name );

						$output .= '</div>';

						// Display name
						if ( $show_name ) {

							$output .= '<div class="mw-users-name">';

								$output .= esc_html( $user->display_name );

							$output .= '</div>';

						}

						// Close link
						if ( $link_to_posts ) {
							$output .= '</a>';
						}

					$output .= '</div>';

				// Clear columns
				if ( $columns == $count ) {
					$count = 0;
				}

				// End loop
				endforeach;

			// Close ul wrap
			$output .= '</div>';

		}

		// Echo output
		echo $output;

		// After widget hook
		echo $args[ 'after_widget' ];

	}

}
register_widget( 'MoreWidgets\Users' );