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
class CommentsWithAvatars extends API {

	/**
	 * Register widget with WordPress.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->args = array(
			'id_base' => 'mw_recent_comments_avatars',
			'name'    => $this->branding() . esc_html__( 'Comments With Avatars', 'more-widgets' ),
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
					'id'      => 'number',
					'label'   => esc_html__( 'Number', 'more-widgets' ),
					'type'    => 'number',
					'default' => 3,
				),
				array(
					'id'          => 'avatar_size',
					'label'       => esc_html__( 'Avatar Size', 'more-widgets' ),
					'type'        => 'number',
					'default'     => 50,
					'placeholder' => 50,
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

		$output .= '<ul class="mw-comments-w-avatars clr">';

		// Query Comments
		$comments = get_comments( array (
			'number'      => $number,
			'status'      => 'approve',
			'post_status' => 'publish',
			'type'        => 'comment',
		) );

		if ( $comments ) {

			$avatar_size = $avatar_size ? $avatar_size : 50;

			// Loop through comments
			foreach ( $comments as $comment ) {

				// Get comment ID
				$comment_id   = $comment->comment_ID;
				$comment_link = get_permalink( $comment->comment_post_ID ) . '#comment-' . $comment_id;

				$output .= '<li class="mw-comment mw-clr">';

					$output .= '<a href="' . esc_url( $comment_link ) . '">';

						$output .= get_avatar( $comment->comment_author_email, $avatar_size );

						$output .= '<div class="mw-comment-excerpt mw-clr">';

							$output .= '<strong>' . get_comment_author( $comment_id ) . ':</strong>';

							$output .= wp_trim_words( $comment->comment_content, '10', '&hellip;' );

						$output .= '</div>';

					$output .= '</a>';

				$output .= '</li>';

			}

		// Display no comments notice
		} else {

			$output .= '<li>' . esc_html__( 'No comments yet.', 'more-widgets' ) . '</li>';

		}

		$output .= '</ul>';

		// Echo output
		echo $output;

		// After widget hook
		echo $args[ 'after_widget' ];

	}

}
register_widget( 'MoreWidgets\CommentsWithAvatars' );