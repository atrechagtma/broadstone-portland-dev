<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://wordkeeper.com
 * @since      1.0.0
 *
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/includes
 */

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
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/includes
 * @author     Lance Dockins <info@wordkeeper.com>
 */
class WordKeeper_System {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WordKeeper_System_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		$this->plugin_name = 'wordkeeper-system';
		$this->version = '1.1.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_universal_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WordKeeper_System_Loader. Orchestrates the hooks of the plugin.
	 * - WordKeeper_System_i18n. Defines internationalization functionality.
	 * - WordKeeper_System_Admin. Defines all hooks for the admin area.
	 * - WordKeeper_System_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordkeeper-system-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordkeeper-system-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wordkeeper-system-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wordkeeper-system-public.php';

		/**
		 * The utility class that provides useful functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordkeeper-utilities.php';

		/**
		 * The purge class provides useful cache purging functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordkeeper-system-purge.php';

		$this->loader = new WordKeeper_System_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WordKeeper_System_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WordKeeper_System_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

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

		$plugin_admin = new WordKeeper_System_Admin( $this->get_plugin_name(), $this->get_version() );
		$purge_class = new WordKeeper_System_Purge( $this->get_plugin_name(), $this->get_version() );

		$page = (empty($_GET['page'])) ? '' : $_GET['page'];
    	if($page == 'wordkeeper-system') {
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		}

		$this->loader->add_action( 'admin_init', $purge_class, 'bulk_operations_cache_purge' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_page' );

		$this->loader->add_action('publish_future_post', $purge_class, '_purge_post');
		$this->loader->add_action('transition_post_status', $purge_class, 'handle_post_status_transition', 10, 3);

		$this->loader->add_action( 'after_setup_theme', $purge_class, '_purge_theme' );

		$this->loader->add_action( 'wp_before_admin_bar_render', $plugin_admin, 'register_cache_purge_menu', 10 );
		$this->loader->add_action( 'wp_loaded', $plugin_admin, 'post_processing_after_page_loaded');

		if (((function_exists('wp_doing_ajax') && wp_doing_ajax()) || (defined( 'DOING_AJAX' ) && DOING_AJAX)) && isset($_POST['action']) && $_POST['action'] == 'wordkeeper_admin_ajax') {
			$this->loader->add_action('wp_ajax_wordkeeper_admin_ajax', $plugin_admin, '_ajax');
			//return;
		}
		else {
			if(!is_admin() || ((function_exists('wp_doing_ajax') && wp_doing_ajax()) || (defined( 'DOING_AJAX' ) && DOING_AJAX))){
				$this->loader->add_action('woocommerce_variation_set_stock_status', $purge_class, '_purge_post', 10, 1);
				$this->loader->add_action('woocommerce_product_set_stock_status', $purge_class, '_purge_post', 10, 1);
			}

			if(is_admin() || ((function_exists('wp_doing_ajax') && wp_doing_ajax()) || (defined( 'DOING_AJAX' ) && DOING_AJAX))) {

				// Post changes, hooks pass ID of post
				$this->loader->add_action('save_post', $purge_class, '_purge_post');
				$this->loader->add_action('delete_post', $purge_class, '_purge_post', 10);

				// Comment changes, hooks pass ID of comment
				$this->loader->add_action('transition_comment_status', $purge_class, '_purge_comment_transition', 10, 3);
				$this->loader->add_action('comment_post', $purge_class, '_purge_comment', 10, 2);
				$this->loader->add_action('edit_comment', $purge_class, '_purge_comment', 10, 1);
				$this->loader->add_action('untrashed_comment', $purge_class, '_purge_comment', 10, 1);
				$this->loader->add_action('delete_comment', $purge_class, '_purge_comment', 10, 1);

				// Term/taxonomy changes, hooks pass ID of terms
				$this->loader->add_action('edit_terms', $purge_class, '_purge_term_processor', 10, 2);
				$this->loader->add_action('delete_term', $purge_class, '_purge_term_processor', 10, 5);
			}
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WordKeeper_System_Public( $this->get_plugin_name(), $this->get_version() );
		$purge_class = new WordKeeper_System_Purge( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_public, 'heartbeat_control', 100 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'heartbeat_control', 100 );
		$this->loader->add_filter( 'heartbeat_settings', $plugin_public, 'heartbeat_frequency' );

		$this->loader->add_filter( 'robots_txt', $plugin_public, 'robots', 99, 2 );
		$this->loader->add_filter( 'wp', $plugin_public, 'cache_control', 99 );

		if(defined('WP_ROCKET_VERSION')){
			add_filter( 'do_rocket_generate_caching_files', '__return_false' );
		}

		if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) && 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
			$this->loader->add_action( 'woocommerce_reduce_order_stock', $purge_class, 'purge_caches_after_product_stock_updates' );
		}
	}

	/**
	 * Register all of the univeral hooks
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_universal_hooks(){
		if((!defined('WP_CLI') || !WP_CLI) && (!defined('DOING_CRON') || !DOING_CRON)){
			add_filter('pre_update_option', function ($new, $name, $old){
				$filter = array(
					'siteurl' => true,
					'home' => true,
					'active_plugins' => true,
					'users_can_register' => true,
					'admin_email' => true,
					'comments_notify' => true,
					'comment_moderation' => true,
					'comment_registration' => true,
					'mailserver_url' => true,
					'mailserver_login' => true,
					'mailserver_pass' => true,
					'mailserver_port' => true,
					'default_comment_status' => true,
					'default_ping_status' => true,
					'default_role' => true,
					'blog_public' => true,
					'use_trackback' => true,
					'upload_path' => true,
					'upload_url_path' => true,
					'blog_public' => true,
					'wp_user_roles' => true,
					'cron' => true,
					'current_theme' => true,
				);

				if(isset($filter[$name])){
					if(!function_exists('current_user_can')){
						require_once(ABSPATH . '/wp-includes/user.php');
						require_once(ABSPATH . '/wp-includes/pluggable.php');
						require_once(ABSPATH . '/wp-includes/capabiliies.php');
					}
					elseif(!function_exists('wp_get_current_user')){
						require_once(ABSPATH . '/wp-includes/pluggable.php');
					}
					return (current_user_can('manage_options')) ? $new : $old;
				}
				else{
					return $new;
				}
			}, 100, 3);
		}
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
	 * @return    WordKeeper_System_Loader    Orchestrates the hooks of the plugin.
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