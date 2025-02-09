<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Wp_Media_Category
 * @subpackage Wp_Media_Category/includes
 */

if ( ! class_exists( 'Wp_Media_Category' ) ) :

	/**
	 * The core plugin class.
	 *
	 * This is used to define internationalization, admin-specific hooks, and
	 * public-facing site hooks.
	 *
	 * Also maintains the unique identifier of this plugin as well as the current
	 * version of the plugin.
	 *
	 * @since      1.0.0
	 * @package    Wp_Media_Category
	 * @subpackage Wp_Media_Category/includes
	 * @author     Wbcom Designs <admin@wbcomdesigns.com>
	 */
	class Wp_Media_Category {

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Wp_Media_Category_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;

		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

			$this->plugin_name = 'wp-media-category';
			$this->version     = '1.0.0';

			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();

		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Wp_Media_Category_Loader. Orchestrates the hooks of the plugin.
		 * - Wp_Media_Category_i18n. Defines internationalization functionality.
		 * - Wp_Media_Category_Admin. Defines all hooks for the admin area.
		 * - Wp_Media_Category_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function load_dependencies() {

			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-media-category-loader.php';

			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-media-category-i18n.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-media-category-admin.php';

			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin-review.php';

			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-media-category-public.php';

			$this->loader = new Wp_Media_Category_Loader();

		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Wp_Media_Category_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale() {

			$plugin_i18n = new Wp_Media_Category_i18n();

			$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_admin_hooks() {

			$plugin_admin = new Wp_Media_Category_Admin( $this->get_plugin_name(), $this->get_version() );

			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'wpmc_enqueue_styles' );
			$this->loader->add_action( 'admin_footer', $plugin_admin, 'wpmc_enqueue_scripts' );
			$this->loader->add_action( 'init', $plugin_admin, 'wpmc_create_media_taxonomy' );
			$this->loader->add_action( 'admin_notices', $plugin_admin, 'wpmc_bulk_change_term_media_notices' );
			$this->loader->add_action( 'wp_ajax_list_terms', $plugin_admin, 'wpmc_list_terms' );
			$this->loader->add_action('pre_get_posts', $plugin_admin, 'filter_media_without_taxonomy');
			$this->loader->add_filter( 'bulk_actions-upload', $plugin_admin, 'wpmc_add_media_category_bulk_action', 10, 1 );
			$this->loader->add_filter( 'handle_bulk_actions-upload', $plugin_admin, 'wpmc_media_category_bulk_action_handler', 10, 3 );
			$this->loader->add_action( 'admin_notices', $plugin_admin, 'wpmc_updated_media_category' );
			$this->loader->add_action( 'restrict_manage_posts', $plugin_admin, 'wpmc_add_media_category_filter' );
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_public_hooks() {
			$plugin_public = new Wp_Media_Category_Public( $this->get_plugin_name(), $this->get_version() );

			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'wpmc_enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'wpmc_enqueue_scripts' );
			$this->loader->add_shortcode( 'wbmedia', $plugin_public, 'wpmc_media_category_shortcode' );
		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    Wp_Media_Category_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}

	}

endif;
