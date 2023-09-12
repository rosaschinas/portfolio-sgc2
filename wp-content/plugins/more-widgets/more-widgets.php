<?php
/**
 * Plugin Name: More Widgets
 * Plugin URI: https://wordpress.org/plugins/more-widgets/
 * Description: Adds extra custom widgets to use with your widgetized areas. Use this plugin instead of built-in theme widgets so if you ever switch themes you can keep your widgets.
 * Version: 1.1
 * Author: WPExplorer
 * Author URI: https://www.wpexplorer.com/
 * Text Domain: more-widgets
 * Domain Path: /languages/
 * Requires at least: 5.7
 * Requires PHP: 7.0
 *
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main MoreWidgets Class.
 *
 * @since 1.0
 */
if ( ! class_exists( 'MoreWidgets' ) ) {

	final class MoreWidgets {

		/**
		 * MoreWidgets constructor.
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->define_constants();
			$this->add_actions();
			$this->require_files();
		}

		/**
		 * Define plugin constants.
		 *
		 * @since  1.1
		 * @access public
		 * @return void
		 */
		public function define_constants() {
			define( 'MORE_WIDGETS_BRANDING', apply_filters( 'more_widgets_branding', 'MW' ) . ': ' );
			define( 'MORE_WIDGETS_MAIN_FILE_PATH', __FILE__ );
			define( 'MORE_WIDGETS_PLUGIN_DIR_PATH', plugin_dir_path( MORE_WIDGETS_MAIN_FILE_PATH ) );
			define( 'MORE_WIDGETS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Add actions.
		 *
		 * @since  1.1
		 * @access public
		 * @return void
		 */
		public function add_actions() {

			// Load Text Domain
			add_action( 'init', array( $this, 'load_text_domain' ) );

			// Load Widgets
			add_action( 'widgets_init', array( $this, 'include_widgets' ) );

			// Admin Scripts
			add_action( 'admin_print_scripts-widgets.php', array( $this, 'admin_scripts' ) );

			// Front-end Scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'widgets_css' ) );

		}

		/**
		 * Require plugin files.
		 *
		 * @since  1.1
		 * @access public
		 * @return void
		 */
		public function require_files() {
			require_once MORE_WIDGETS_PLUGIN_DIR_PATH . 'inc/API.php';
		}

		/**
		 * Load Text Domain.
		 *
		 * @since  1.1
		 * @access public
		 * @return void
		 */
		public function load_text_domain() {
			load_plugin_textdomain(
				'more-widgets',
				false,
				dirname( plugin_basename( __FILE__ ) ) . '/languages'
			);
		}

		/**
		 * Load widget files.
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 */
		public function include_widgets() {
			$widgets = apply_filters( 'more_widgets_classes_list', array(
				'About',
				'Advertisement',
				'AdvancedRecentPosts',
				'BusinessInfo',
				'CommentsWithAvatars',
				'FacebookPage',
				'GoogleMap',
				'Newsletter',
				'PostsSlider',
				'SocialProfiles',
				'Users',
			) );

			foreach( $widgets as $widget ) {
				require_once MORE_WIDGETS_PLUGIN_DIR_PATH . 'inc/widgets/' . $widget . '.php';
			}
		}

		/**
		 * Load admin scripts for this widget.
		 *
		 * @since  1.0
		 * @access public
		 * @return null
		 */
		public function admin_scripts( $hook ) {
			wp_enqueue_style(
				'more-widgets-admin',
				MORE_WIDGETS_PLUGIN_DIR_URL . 'assets/css/more-widgets-admin.css',
				array(),
				'1.1'
			);

			wp_enqueue_script(
				'more-widgets-admin',
				MORE_WIDGETS_PLUGIN_DIR_URL . 'assets/js/more-widgets-admin.js',
				array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-tabs' ),
				'1.0',
				true
			);

			wp_localize_script( 'more-widgets-admin', 'moreWidgets', array(
				'repeaterConfirm' => esc_html__( 'Do you really want to delete this item?', 'more-widgets' ),
			) );
		}

		/**
		 * Loads widgets CSS on front-end
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 */
		public function widgets_css() {
			if ( apply_filters( 'more_widgets_enqueue_styles', true ) ) {
				wp_enqueue_style(
					'more-widgets-front',
					MORE_WIDGETS_PLUGIN_DIR_URL . 'assets/css/more-widgets-front.css',
					array(),
					'1.1'
				);
			}

			if ( is_active_widget( false, false, 'mw_social_profiles' )
				|| is_active_widget( false, false, 'mw_business_info' )
				|| is_customize_preview()
			) {

				wp_enqueue_style(
					'more-widgets-icons',
					MORE_WIDGETS_PLUGIN_DIR_URL . 'assets/css/more-widgets-icons.css'
				);

			}
		}

	}

	new MoreWidgets;

}