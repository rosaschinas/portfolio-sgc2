<?php
/**
 * Widget Constructor Class makes it easier to build custom widgets for WP
 *
 * @package MoreWidgets
 * @version 1.1
 */

namespace MoreWidgets;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class API extends \WP_Widget {

	/**
	 * Widget name.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Widget id_base.
	 *
	 * @var string
	 */
	public $id_base = '';

	/**
	 * Widget fields.
	 *
	 * @var array
	 */
	private $fields = array();

	/**
	 * Return correct branding string.
	 *
	 * @since 1.0
	 *
	 * @access public
	 */
	public function branding() {
		return MORE_WIDGETS_BRANDING;
	}

	/**
	 * Create Widget.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array @args Widget arguments.
	 * @return void
	 */
	public function create_widget( $args ) {

		// Set widget vars
		$this->name    = wp_strip_all_tags( $args[ 'name' ] );
		$this->id_base = wp_strip_all_tags( $args[ 'id_base' ] );
		$this->options = isset( $args[ 'options' ] ) ? $args[ 'options' ] : '';
		$this->options = apply_filters( $this->id_base . '_widget_options', $this->options );
		$this->fields  = apply_filters( $this->id_base . '_widget_fields', $args[ 'fields' ] );

		// Call WP_Widget to create the widget
		parent::__construct(
			$this->id_base,
			$this->name,
			$this->options
		);

	}

	/**
	 * Return default values.
	 *
	 * @since 1.0
	 *
	 * @access   public
	 * @return   array $defaults Returns the default arguments for this widget.
	 */
	public function get_defaults() {
		if ( empty( $this->fields ) || ! is_array( $this->fields ) ) {
			return;
		}
		$defaults = array();
		foreach ( $this->fields as $field ) {
			if ( empty( $field[ 'default' ] ) && isset( $field[ 'choices' ] ) && is_array( $field[ 'choices' ] ) ) {
				reset( $field[ 'choices' ] );
				$field[ 'default' ] = key( $field[ 'choices' ] );
			}
			$defaults[$field[ 'id' ]] = isset( $field[ 'default' ] ) ? $field[ 'default' ] : '';
		}
		return $defaults;
	}

	/**
	 * Parse insance for live output.
	 *
	 * @since 1.0
	 *
	 * @access   public
	 * @return   array $instance Returns the current widget instance.
	 */
	public function parse_instance( $instance ) {
		$defaults = $this->get_defaults();
		$instance = wp_parse_args( $instance, $defaults );
		foreach ( $instance as $k => $v ) {
			if ( ! isset( $v ) && isset( $defaults[$k] ) ) {
				$instance[$k] = $defaults[$k];
			}
		}
		return $instance;
	}

	/**
	 * Output widget title
	 *
	 * @since 1.0
	 *
	 * @access   public
	 * @return   array $instance Returns the current widget instance.
	 */
	public function widget_title( $args, $instance ) {
		if ( ! empty( $instance[ 'title' ] ) ) {
			echo $args[ 'before_title' ] . apply_filters( 'widget_title', $instance[ 'title' ] ) . $args[ 'after_title' ];
		}
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 * @since 1.0
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		foreach ( $this->fields as $field ) {

			$field_id   = $field[ 'id' ];
			$field_type = $field[ 'type' ];
			$field_val  = isset( $new_instance[$field_id] ) ? $new_instance[$field_id] : null;
			$default    = $field[ 'default' ] ?? '';

			if ( 'notice' == $field_type ) {
				continue;
			}

			// Sanitize input
			if ( $field_val ) {

				// Sanitize checkbox
				if ( 'checkbox' == $field_type ) {

					$instance[$field_id] = (bool) true;

				} elseif ( 'select' == $field_type ) {

					$array_to_check = array();

					if ( is_array( $field[ 'choices' ] ) ) {
						$array_to_check = $field[ 'choices' ];
					} else {
						$method = 'choices_' . $field[ 'choices' ];
						if ( method_exists( $this, $method ) ) {
							$array_to_check = $this->$method( $field );
						}
					}

					$instance[$field_id] = ( array_key_exists( $field_val, $array_to_check ) ? $field_val : $default );

				} elseif ( 'repeater' == $field_type ) {

					$fields = $field[ 'fields' ];
					$new_val = array();

					foreach ( $fields as $field_k => $field_v ) {

						$subfield_id   = $field_v[ 'id' ];
						$subfield_type = $field_v[ 'type' ];
						$field_vals    = $field_val[$subfield_id];

						$count = 1;
						foreach( $field_vals as $field_vals_k => $field_vals_v ) {

							if ( $count == count( $field_vals ) ) {
								continue;
							}

							$count++;

							$field_vals_v = $this->sanitize_field( $subfield_type, $field_vals_v );

							$new_val[$field_vals_k][$subfield_id] = $field_vals_v;

						}

					}

					$instance[$field_id] = $new_val;

				} else {

					$instance[$field_id] = $this->sanitize_field( $field_type, $field_val );

				}

			} else {

				if ( 'checkbox' == $field_type ) {
					$instance[$field_id] = (bool) false; // save checkboxes as false since they may be enabled by default
				} else {
					$instance[$field_id] = '';
				}

			}

		}

		return $instance;

	}

	/**
	 * Back-end widget form.
	 *
	 * @since 1.0
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		echo '<div class="mw-settings-form">';

			foreach ( $this->fields as $field ) {
				$id             = $field[ 'id' ];
				$field[ 'class' ] = 'widefat';
				$field[ 'id' ]    = $this->get_field_id( $id );
				$field[ 'name' ]  = $this->get_field_name( $id );
				if ( empty( $instance ) ) {
					$default = isset( $field[ 'std' ] ) ? $field[ 'std' ] : ''; // new instance
					$default = isset( $field[ 'default' ] ) ? $field[ 'default' ] : $default;
				} else {
					$default = isset( $field[ 'default' ] ) ? $field[ 'default' ] : ''; // already saved instance
				}
				$field[ 'value' ] = isset( $instance[$id] ) ? $instance[$id] : $default;
				$this->add_field( $field );
			}

		echo '</div>';

	}

	/**
	 * Adds a new field to the admin form.
	 *
	 * @since 1.0
	 *
	 * @access public
	 * @param array $field Field parameters.
	 * @return string
	 */
	public function add_field( $field ) {
		$type = isset( $field[ 'type' ] ) ? $field[ 'type' ] : '';

		$method_name = 'field_' . $type;
		$description = '';

		if ( method_exists( $this, $method_name ) ) {

			if ( isset( $field[ 'description' ] ) && 'notice' != $type ) {
				$description = '<br /><small class="description" style="display:block;padding:6px 0 0;clear:both;">' . wp_kses_post( $field[ 'description' ] ) . '</small>';
			}

			echo '<p>' . $this->$method_name( $field ) . $description . '</p>';

		}
	}

	/**
	 * Return field label for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function field_label( $field, $semicolon = true ) {
		$output = '<label for="' . esc_attr( $field[ 'id' ] ). '">';

			$output .= esc_html( $field[ 'label' ] );

			if ( $semicolon ) {
				$output .= ':';
			}

		$output .= '</label>';

		return $output;
	}

	/**
	 * Return text field for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function field_text( $field, $output = '' ) {
		$output .= $this->field_label( $field );

		$output .= '<input type="text"';

		if ( isset( $field[ 'class' ] ) ) {
			$output .= ' class="' . esc_attr( $field[ 'class' ] ) . '"';
		}

		$default = isset( $field[ 'default' ] ) ? $field[ 'default' ] : '';
		$value   = isset( $field[ 'value' ] ) ? $field[ 'value' ] : $default;

		$output .= ' id="' . esc_attr( $field[ 'id' ] ) . '" name="' . esc_attr( $field[ 'name' ] ) . '" value="' . esc_attr( $value ) . '"';

		if ( isset( $field[ 'size' ] ) ) {
			$output .= ' size="' . esc_attr( $field[ 'size' ] ) . '" ';
		}

		if ( ! empty( $field[ 'placeholder' ] ) ) {
			$output .= ' placeholder="' . esc_attr( $field[ 'placeholder' ] ) . '" ';
		}

		$output .= ' />';

		return $output;
	}

	/**
	 * Return url field for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function field_url( $field, $output = '' ) {
		$output .= $this->field_label( $field );

		$output .= '<input type="url"';

		if ( isset( $field[ 'class' ] ) ) {
			$output .= ' class="' . esc_attr( $field[ 'class' ] ) . '"';
		}

		$default = isset( $field[ 'default' ] ) ? $field[ 'default' ] : '';
		$value   = isset( $field[ 'value' ] ) ? esc_url( $field[ 'value' ] ) : $default;

		$output .= ' id="' . esc_attr( $field[ 'id' ] ) . '" name="' . esc_attr( $field[ 'name' ] ) . '" value="' . esc_attr( esc_url( $value ) ) . '" placeholder="http://"';

		if ( isset( $field[ 'size' ] ) ) {
			$output .= ' size="' . esc_attr( $field[ 'size' ] ) . '" ';
		}

		$output .= ' />';

		return $output;
	}

	/**
	 * Return textarea field for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function field_textarea( $field, $output = '' ) {
		$output .= $this->field_label( $field );

		$output .= '<textarea';

			if ( isset( $field[ 'class' ] ) ) {
				$output .= ' class="' . esc_attr( $field[ 'class' ] ) . '"';
			}

			$output .= ' id="' . esc_attr( $field[ 'id' ] ) . '" name="' . esc_attr( $field[ 'name' ] ) . '"';

			$rows = isset( $field[ 'rows' ] ) ? $field[ 'rows' ] : 5;
			$output .= ' rows="' . esc_attr( $rows ) . '"';

		$output .= '>';

		$default = isset( $field[ 'default' ] ) ? $field[ 'default' ] : '';
		$value   = isset( $field[ 'value' ] ) ? $field[ 'value' ] : $default;

		if ( $value ) {
			$output .= $this->sanitize_textarea( $value );
		}

		$output .= '</textarea>';

		return $output;
	}

	/**
	 * Return media upload field for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function field_media_upload( $field, $output = '' ) {
		wp_enqueue_media();
		wp_enqueue_script(
			'more-widgets-media-upload-btn',
			MORE_WIDGETS_PLUGIN_DIR_URL . 'assets/js/media-upload-btn.js',
			array(),
			'1.0',
			true
		);

		$output .= $this->field_label( $field );

		$output .= '<div class="mw-upload-field"><input type="text"';

		if ( isset( $field[ 'class' ] ) ) {
			$output .= ' class="' . esc_attr( $field[ 'class' ] ) . '"';
		}

		$value = $field[ 'value' ] ?? $field[ 'default' ] ?? '';

		$output .= ' id="' . esc_attr( $field[ 'id' ] ) . '" name="' . esc_attr( $field[ 'name' ] ) . '" value="' . esc_attr( $value ) . '"';

		if ( isset( $field[ 'size' ] ) ) {
			$output .= ' size="' . esc_attr( $field[ 'size' ] ) . '" ';
		}

		$output .= ' />';

		$output .= ' <input class="more-widgets-upload-button button" type="button" value="' . esc_html__( 'Select', 'more-widgets' ) . '" /></div>';

		return $output;
	}

	/**
	 * Return select field for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function field_select( $field, $output = '' ) {
		if ( empty( $field[ 'choices' ] ) ) {
			return;
		}

		$choices = $field[ 'choices' ];

		if ( ! is_array( $choices ) ) {
			$method = 'choices_' . $choices;
			if ( method_exists( $this, $method ) ) {
				$choices = $this->$method( $field );
			}
		}

		if ( empty( $choices ) || ! is_array( $choices ) ) {
			return;
		}

		$output .= $this->field_label( $field );

		$output .= '<select';

			if ( isset( $field[ 'class' ] ) ) {
				$output .= ' class="' . esc_attr( $field[ 'class' ] ) . '"';
			}

			$output .= ' id="' . esc_attr( $field[ 'id' ] ) . '" name="' . esc_attr( $field[ 'name' ] ) . '"';

		$output .= '>';

		$value = $field[ 'value' ] ?? $field[ 'default' ] ?? '';

		foreach( $choices as $id => $label ) {

			$output .= '<option value="' . esc_attr( $id ) . '" ' . selected( $value, $id, false ) . '>' .  esc_html( $label ) . '</option>';

		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Return select field for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function field_radio( $field, $output = '' ) {
		if ( empty( $field[ 'choices' ] ) ) {
			return;
		}

		$choices = $field[ 'choices' ];

		if ( ! is_array( $choices ) ) {
			$method = 'choices_' . $choices;
			if ( method_exists( $this, $method ) ) {
				$choices = $this->$method( $field );
			}
		}

		if ( empty( $choices ) || ! is_array( $choices ) ) {
			return;
		}

		$output .= $this->field_label( $field );

		$output .= '<fieldset><legend class="screen-reader-text">' . esc_html( $this->field_label( $field ) ) . '</legend>';

			$value = $field[ 'value' ] ?? $field[ 'default' ] ?? '';

			foreach( $choices as $id => $label ) {

				$output .= '<label><input type="radio" name="' . esc_attr( $field[ 'name' ] ) . '" value="' . esc_attr( $id ) . '" '. selected( $value, $id, false ) . '><span>' .  esc_html( $label ) . '</span></label><br />';

			}

		$output .= '</fieldset>';

		return $output;
	}

	/**
	 * Return checkbox field for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function field_checkbox( $field, $output = '' ) {
		$output .= '<input type="checkbox"';

			if ( isset( $field[ 'class' ] ) ) {
				$output .= ' class="' . esc_attr( $field[ 'class' ] ) . '"';
			}

			$value = $field[ 'value' ] ?? $field[ 'default' ] ?? 'off';

			$output .= ' id="' . esc_attr( $field[ 'id' ] ) . '" name="' . esc_attr( $field[ 'name' ] ) . '"';

			$output .= ' ' . checked( (bool) $value, true, false );

		$output .= ' />';

		$output .= $this->field_label( $field, false );

		return $output;
	}

	/**
	 * Return number field for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function field_number( $field, $output = '' ) {
		$output .= $this->field_label( $field );

		$output .= '<input type="number"';

		if ( isset( $field[ 'class' ] ) ) {
			$output .= ' class="' . esc_attr( $field[ 'class' ] ) . '"';
		}

		$default = $field[ 'default' ] ?? '';
		$value   = isset( $field[ 'value' ] ) ? floatval( $field[ 'value' ] ) : $default;
		$min     = $field[ 'min' ] ?? '';
		$max     = $field[ 'max' ] ?? '';
		$step    = $field[ 'step' ] ?? '';

		$output .= ' id="' . esc_attr( $field[ 'id' ] ) . '" name="' . esc_attr( $field[ 'name' ] ) . '" value="' . esc_attr( $value ) . '"';

		$output .= ' min="' . esc_attr( $min ) . '" ';
		$output .= ' max="' . esc_attr( $max ) . '" ';
		$output .= ' step="' . esc_attr( $step ) . '" ';

		$output .= ' />';

		return $output;
	}

	/**
	 * Return repeater field for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function field_repeater( $field, $output = '' ) {
		if ( empty( $field[ 'fields' ] ) ) {
			return;
		}

		$fields = $field[ 'fields' ];
		$value  = $field[ 'value' ] ?? $field[ 'default' ] ?? '';

		if ( empty( $fields ) || ! is_array( $fields ) ) {
			return;
		}

		$output .= '<h4>' . $this->field_label( $field ) . '</h4>';

		$output .= '<ul id="' . esc_attr( $field[ 'id' ] ) . '" class="mw-repeater-field">';

			// Show saved fields
			if ( is_array( $value ) ) {

				for ( $k = 0; $k < count( $value ); $k++ ) {

					$output .= '<li><span class="mw-rpf-remove dashicons dashicons-no-alt"></span>';

						foreach ( $fields as $subfield ) {

							$subfield[ 'name' ]  = $field[ 'name' ] . '[' . $subfield[ 'id' ] . '][]'; // same name for each
							$subfield[ 'value' ] = isset( $value[ $k ][ $subfield[ 'id' ] ] ) ? $value[ $k ][ $subfield[ 'id' ] ] : '';

							$method = 'field_' . $subfield['type'];

							if ( method_exists( $this, $method ) ) {

								$output .= '<p>' . $this->$method( $subfield ) . '</p>';

							}

						}

					$output .= '</li>';

				}

			}

		$output .= '</ul>';

		// Add button

		$output .= '<p><a href="#" class="mw-rpf-add button">' . esc_html__( 'Add New', 'more-widgets' ) . '</a></p>';

		// Add the cloner item
		$output .= '<div class="mw-rpf-clone"><span class="mw-rpf-remove dashicons dashicons-no-alt"></span>';

			foreach ( $fields as $subfield ) {

				$subfield[ 'name' ]  = $field[ 'name' ] . '[' . $subfield[ 'id' ] . '][]'; // same name for each
				$subfield[ 'value' ] = '';

				$method = 'field_' . $subfield['type'];

				if ( method_exists( $this, $method ) ) {

					$output .= '<p>' . $this->$method( $subfield ) . '</p>';

				}

			}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Return post_types choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function choices_post_types() {
		$types = array();
		$get_types = get_post_types( array(
			'public' => true,
		), 'objects', 'and' );
		foreach ( $get_types as $key => $val ) {
			$types[$key] = $val->labels->name;
		}

		return $types;
	}

	/**
	 * Return taxonomies choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function choices_taxonomies() {
		$taxonomies = array(
			'' => '&mdash; ' . esc_html( 'Select', 'more-widgets' ) . ' &mdash;'
		);

		$get_taxonomies = get_taxonomies( array(
			'public' => true,
		), 'objects' );

		foreach ( $get_taxonomies as $get_taxonomy ) {
			$taxonomies[ $get_taxonomy->name ] = ucfirst( $get_taxonomy->labels->singular_name );
		}

		return $taxonomies;
	}

	/**
	 * Return query_orderby choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function choices_query_orderby() {
		return array(
			'date'          => esc_html__( 'Date', 'more-widgets' ),
			'title'         => esc_html__( 'Title', 'more-widgets' ),
			'modified'      => esc_html__( 'Modified', 'more-widgets' ),
			'author'        => esc_html__( 'Author', 'more-widgets' ),
			'rand'          => esc_html__( 'Random', 'more-widgets' ),
			'comment_count' => esc_html__( 'Comment Count', 'more-widgets' ),
		);
	}

	/**
	 * Return query_order choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function choices_query_order() {
		return array(
			'desc' => esc_html__( 'Descending', 'more-widgets' ),
			'asc'  => esc_html__( 'Ascending', 'more-widgets' ),
		);
	}

	/**
	 * Return categories choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function choices_categories() {
		$choices = array(
			'' => '&mdash; ' . esc_html( 'Select', 'more-widgets' ) . ' &mdash;',
		);
		$terms = get_terms( 'category' );
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$choices[ absint( $term->term_id ) ] = esc_html( $term->name );
			}
		}
		return $choices;
	}

	/**
	 * Return intermediate_image_sizes choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function choices_intermediate_image_sizes( $field ) {
		$sizes     = array();
		$get_sizes = array_keys( $this->get_intermediate_sizes() );
		$sizes     = $sizes + array_combine( $get_sizes, $get_sizes );
		return $sizes;
	}

	/**
	 * Return intermediate_image_sizes choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return array
	 */
	private function get_intermediate_sizes() {
		$size = '';

		global $_wp_additional_image_sizes;

		$sizes = array(
			'full'  => array(
				'width'  => '9999',
				'height' => '9999',
				'crop'   => 0,
			),
		);
		$get_intermediate_image_sizes = get_intermediate_image_sizes();

		// Create the full array with sizes and crop info
		foreach( $get_intermediate_image_sizes as $_size ) {

			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
				$sizes[ $_size ][ 'width' ]  = get_option( $_size . '_size_w' );
				$sizes[ $_size ][ 'height' ] = get_option( $_size . '_size_h' );
				$sizes[ $_size ][ 'crop' ]   = (bool) get_option( $_size . '_crop' );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$sizes[ $_size ] = array(
					'width'     => $_wp_additional_image_sizes[ $_size ][ 'width' ],
					'height'    => $_wp_additional_image_sizes[ $_size ][ 'height' ],
					'crop'      => $_wp_additional_image_sizes[ $_size ][ 'crop' ]
				);
			}

		}

		// Get only 1 size if found
		if ( $size ) {
			if ( isset( $sizes[ $size ] ) ) {
				return $sizes[ $size ];
			} else {
				return false;
			}
		}

		return $sizes;
	}

	/**
	 * Return menus choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return array
	 */
	private function choices_menus() {
		$menus = array();

		$get_menus = get_terms( 'nav_menu', array(
			'hide_empty' => false,
		) );

		if ( ! empty( $get_menus ) ) {
			foreach ( $get_menus as $menu ) {
				$menus[ absint( $menu->term_id ) ] = esc_html( $menu->name );
			}
		}

		return $menus;
	}

	/**
	 * Return posts choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return array
	 */
	private function choices_posts( $field ) {
		$posts = array();

		$ids = new WP_Query( array(
			'post_type'      => $field[ 'post_type' ],
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		) );

		if ( $ids->have_posts() ) {
			foreach ( $ids->posts as $post_id ) {
				$posts[ absint( $post_id ) ] = esc_html( get_post_field( 'post_title', $post_id, 'raw' ) );
			}
		}

		return $posts;
	}

	/**
	 * Return grid_columns choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return array
	 */
	private function choices_grid_columns() {
		return array(
			1 => 1,
			2 => 2,
			3 => 3,
			4 => 4,
			5 => 5,
			6 => 6,
		);
	}

	/**
	 * Return grid_gaps choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return array
	 */
	private function choices_grid_gaps() {
		return array(
			1   => 1,
			5   => 5,
			10  => 10,
			15  => 15,
			20  => 20,
			25  => 25,
			30  => 30,
			35  => 35,
			40  => 40,
			50  => 50,
			60  => 60,
			80  => 80,
			100 => 100,
		);
	}

	/**
	 * Return link_target choices for admin form.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return array
	 */
	private function choices_link_target() {
		return array(
			'_self'  => esc_html__( 'Current window', 'more-widgets' ),
			'_blank' => esc_html__( 'New window', 'more-widgets' ),
		);
	}

	/**
	 * Sanitize textarea field
	 *
	 * @since 1.0
	 *
	 * @access private
	 */
	private function sanitize_field( $type = '', $input = '' ) {
		$sanitize_method = 'sanitize_' . $type;

		if ( method_exists( $this, $sanitize_method ) ) {
			return $this->$sanitize_method( $input );
		} else {
			return wp_strip_all_tags( $input ); // at least strip all tags from all fields
		}
	}

	/**
	 * Sanitize textarea field
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function sanitize_textarea( $input = '' ) {
		return sanitize_textarea_field( $input );
	}

	/**
	 * Sanitize select field.
	 *
	 * @since 1.0
	 *
	 * @access private
	 * @return string
	 */
	private function sanitize_select( $input = '' ) {
		return sanitize_textarea_field( $input );
	}

}