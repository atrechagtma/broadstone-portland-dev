<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://wordkeeper.com
 * @since      1.0.0
 *
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/admin
 * @author     Lance Dockins <info@wordkeeper.com>
 */
class WordKeeper_System_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

	/**
     * The settings of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $settings    The settings of this plugin.
     */
	private $settings;

	/**
     * The key/value pair options to show in the drop down.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $options    The key/value pair options to show in the drop down.
     */
	private $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version     = $version;
		$settings          = get_option('wordkeeper-system-settings');
        $this->options           = array(
            'heartbeat-frequency'  => array(
                'default' => 'WordPress Default',
                '30' => '30',
                '60' => '60',
                '300' => '300',
            ),
            'heartbeat-permission' => array(
                'default'                      => 'WordPress Default',
                'disable-heartbeat-completely' => 'Disable Completely',
                'disable-heartbeat-dashboard'  => 'Disable on Dashboard',
                'allow-heartbeat-post-edit'    => 'Allow Only on Post Edit Pages',
            ),
        );

		$schema = array(
			'heartbeat-frequency' => isset($settings['heartbeat-frequency']) && array_key_exists($settings['heartbeat-frequency'], $this->options['heartbeat-frequency']) ? $settings['heartbeat-frequency'] : 'default',
			'heartbeat-permission' => isset($settings['heartbeat-permission']) && array_key_exists($settings['heartbeat-permission'], $this->options['heartbeat-permission']) ? $settings['heartbeat-permission'] : 'default',
			'ahrefs' => isset($settings['ahrefs']) && $settings['ahrefs'] == 'on' ? true : false,
			'moz' => isset($settings['moz']) ? $settings['moz'] : false,
			'semrush' => isset($settings['semrush']) ? $settings['semrush'] : false,
			'screaming-frog' => isset($settings['screaming-frog']) ? $settings['screaming-frog'] : false,
			'majestic' => isset($settings['majestic']) ? $settings['majestic'] : false,
			'dataforseo' => isset($settings['dataforseo']) ? $settings['dataforseo'] : false,
			'yandex' => isset($settings['yandex']) ? $settings['yandex'] : false,
			'baidu' => isset($settings['baidu']) ? $settings['baidu'] : false,
			'huawei' => isset($settings['huawei']) ? $settings['huawei'] : false,
			'seznam' => isset($settings['seznam']) ? $settings['seznam'] : false,
			'mailru' => isset($settings['mailru']) ? $settings['mailru'] : false,
			'qwant' => isset($settings['qwant']) ? $settings['qwant'] : false,
			'sogou' => isset($settings['sogou']) ? $settings['sogou'] : false,
			'coccoc' => isset($settings['coccoc']) ? $settings['coccoc'] : false
		);
		update_option('wordkeeper-system-settings', $schema, false);
		$this->settings = $schema;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        /**
         *
         * An instance of this class should be passed to the run() function
         * defined in WordKeeper_System_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The WordKeeper_System_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wordkeeper-system-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        /**
         *
         * An instance of this class should be passed to the run() function
         * defined in WordKeeper_System_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The WordKeeper_System_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        $screen = get_current_screen();
        if($screen->id == 'toplevel_page_wordkeeper-system') {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wordkeeper-system-admin.js', array('jquery'), $this->version, false);
			wp_enqueue_script('sweetalert-core', plugin_dir_url(__FILE__) . 'js/sweetalert.min.js', array('jquery'), $this->version, false);
			wp_enqueue_script('wordkeeper-system-core', plugin_dir_url(__FILE__) . 'js/wordkeeper.js', array('jquery'), $this->version, false);
        }
    }

	/**
	 * _ajax
	 *
	 * Validates and routes admin ajax actions to the right dispatch
	 *
	 * @return void
	 */
	public function _ajax() {
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		if(is_admin() && is_user_logged_in() && current_user_can('publish_posts') && current_user_can('edit_posts')) {
			if (!empty($_POST)) {
				error_reporting(E_ERROR | E_PARSE);

				$validate = check_ajax_referer('wordkeeper_ajax', 'wp_nonce', false);
				if(!$validate) {
					$response = array(
						'status' => 'OK',
						'response' => 'Failure',
						'message' => 'Could Not Validate Nonce',
						'nonce' => wp_create_nonce('wordkeeper_ajax')
					);
					$response = json_encode($response);
					echo $response;
					wp_die();
				}

				switch ($_POST['form']) {
					case 'purge-form':
						switch ($_POST['purge']) {
							case 'purge-all':
								$response = WordKeeper_System_Purge::purge_all();

								if (!empty($response) && $response['response'] == 'OK') {
									$response = array(
										'status' => 'OK',
										'response' => 'Success',
										'message' => 'All caches purged successfully',
										'nonce' => wp_create_nonce('wordkeeper_ajax')
									);
								}
								else{
									$response = array(
										'status' => 'OK',
										'response' => 'Failed',
										'message' => $response['response'],
										'nonce' => wp_create_nonce('wordkeeper_ajax')
									);
								}

								$response = json_encode($response);
								echo $response;
								wp_die();
								break;
							default:
								$response = null;
								break;
						}
						break;
					case 'settings-form':

						if($this->save_settings($_POST)){
							$response = array(
								'status' => 'OK',
								'response' => 'Success',
								'message' => 'Settings saved',
								'nonce' => wp_create_nonce('wordkeeper_ajax')
							);
							$response = json_encode($response);
							echo $response;
							wp_die();
						}

						break;
					default:
						break;
				}
			}
		}
	}

    /**
     * Add settings page
     *
     * @since  1.0.0
     */
    public function add_settings_page()
    {
        $data = get_userdata( get_current_user_id() );
      	if (current_user_can('manage_options')){
      		$cap = 'manage_options';
      	} else {
      		$cap = 'publish_pages';
      	}

        $this->plugin_screen_hook_suffix = add_menu_page(
    			__('WordKeeper Settings', 'wordkeeper-system'),
    			__('WordKeeper', 'wordkeeper-system'),
    			$cap,
    			$this->plugin_name,
    			array($this, 'display_settings_page'),
    			plugin_dir_url(dirname(__FILE__)) . 'wordkeeper-system.svg',
    			3
    		);
    }

    /**
     * Render the settings page for plugin
     *
     * @since  1.0.0
     */
    public function display_settings_page()
    {
        $message               = '';
        $settings = $this->settings;
        $options = $this->options;

        include_once 'partials/wordkeeper-system-admin-display.php';
    }

	/**
     * Save settings in the database
     *
     * @since  1.0.0
     */
	public function save_settings($post){
		$schema = array(
			'heartbeat-frequency' => isset($post['heartbeat-frequency']) && array_key_exists($post['heartbeat-frequency'], $this->options['heartbeat-frequency']) ? $post['heartbeat-frequency'] : $this->settings['heartbeat-frequency'],
			'heartbeat-permission' => isset($post['heartbeat-permission']) && array_key_exists($post['heartbeat-permission'], $this->options['heartbeat-permission']) ? $post['heartbeat-permission'] : $this->settings['heartbeat-permission'],
			'ahrefs' => isset($post['ahrefs']) && $post['ahrefs'] == 'on' ? true : false,
			'moz' => isset($post['moz']) && $post['moz'] == 'on' ? true : false,
			'semrush' => isset($post['semrush']) && $post['semrush'] == 'on' ? true : false,
			'screaming-frog' => isset($post['screaming-frog']) && $post['screaming-frog'] == 'on' ? true : false,
			'majestic' => isset($post['majestic']) && $post['majestic'] == 'on' ? true : false,
			'dataforseo' => isset($post['dataforseo']) && $post['dataforseo'] == 'on' ? true : false,
			'yandex' => isset($post['yandex']) && $post['yandex'] == 'on' ? true : false,
			'baidu' => isset($post['baidu']) && $post['baidu'] == 'on' ? true : false,
			'huawei' => isset($post['huawei']) && $post['huawei'] == 'on' ? true : false,
			'seznam' => isset($post['seznam']) && $post['seznam'] == 'on' ? true : false,
			'mailru' => isset($post['mailru']) && $post['mailru'] == 'on' ? true : false,
			'qwant' => isset($post['qwant']) && $post['qwant'] == 'on' ? true : false,
			'sogou' => isset($post['sogou']) && $post['sogou'] == 'on' ? true : false,
			'coccoc' => isset($post['coccoc']) && $post['coccoc'] == 'on' ? true : false
		);

		$hash_existing = md5(serialize($this->settings));
		$hash_new = md5(serialize($schema));

		if($hash_existing != $hash_new){
			// Something changed. Purge robots.txt cache
			$home = get_option('home');
			$pages = array();
			$pages[] = rtrim($home, '/') . '/robots.txt';
			$pages = json_encode($pages);

			$response = WordKeeper_System_Purge::purge_by_url($pages);

			if (!empty($response) && $response['response'] == 'OK') {
				//All caches purged successfully
			}
		}

		update_option('wordkeeper-system-settings', $schema, false);
		return true;
	}

	/**
	 * Register the cache purge menu
	 *
	 * @return void
	 */
	public function register_cache_purge_menu(){
		global $wp_admin_bar, $pagenow;
		// 1. If there's no speed plugin, only add one main menu entry
		// 2. If there's speed plugin active, add a drop down and first entry should be from this plugin.

		if(!class_exists('Wordkeeper_Speed_Config')){
			if((!is_admin() || (is_admin() && $pagenow == 'post.php')) && (current_user_can('publish_posts') || current_user_can('publish_pages'))){
				$current_page = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				$current_page = preg_replace('#[\?&]wordkeeper_purge_this_page#', '', $current_page);
				if(strpos($current_page, '?') !== false) {
					$wordkeeper_purge_this_page = $current_page . '&wordkeeper_purge_this_page';
				}
				else{
					$wordkeeper_purge_this_page = $current_page . '?wordkeeper_purge_this_page';
				}

				$wp_admin_bar->add_menu( array(
					'parent' => 'top-secondary', // use 'false' for a root menu, or pass the ID of the parent menu
					'id' => 'wordkeeper_purge_this_page', // link ID, defaults to a sanitized title value
					'title' => __('Purge this Page'), // link title
					'href' => $wordkeeper_purge_this_page, // name of file
					'meta' => false // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' );
				));
			}
		}
		else{
			// This action will be excuted from the speed plugin
			add_action('wordkeeper_register_cache_purge_sub_menu', array($this, 'register_cache_purge_sub_menu'), 10, 2);
		}
	}

	/**
	 * Register the cache purge submenu
	 *
	 * @param object $wp_admin_bar
	 * @param string $parent
	 * @return void
	 */
	public function register_cache_purge_sub_menu(&$wp_admin_bar, $parent){
		global $pagenow;

		if((!is_admin() || (is_admin() && $pagenow == 'post.php')) && (current_user_can('publish_posts') || current_user_can('publish_pages'))){
			$current_page = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$current_page = preg_replace('#[\?&]wordkeeper_purge_this_page#', '', $current_page);
			if(strpos($current_page, '?') !== false) {
				$wordkeeper_purge_this_page = $current_page . '&wordkeeper_purge_this_page';
			}
			else{
				$wordkeeper_purge_this_page = $current_page . '?wordkeeper_purge_this_page';
			}

			$wp_admin_bar->add_menu( array(
				'parent' => $parent, // use 'false' for a root menu, or pass the ID of the parent menu
				'id' => 'wordkeeper_purge_this_page', // link ID, defaults to a sanitized title value
				'title' => __('Purge this Page'), // link title
				'href' => $wordkeeper_purge_this_page, // name of file
				'meta' => false // array of any of the following options: array( 'html' => '', 'class' => '', 'onclick' => '', target => '', title => '' );
			));
		}
	}

	/**
	 * Process necesarry cache purges after a page is loaded with a given cache purge query param
	 * This method is fired after the page is loaded and is used to check if purge cache action was requested.
	 *
	 * @return void
	 */
	function post_processing_after_page_loaded() {
		global $pagenow;

		$wordkeeper_purge_this_page = isset($_GET['wordkeeper_purge_this_page']);

		if((current_user_can('publish_posts') || current_user_can('publish_pages')) && $wordkeeper_purge_this_page){

			$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

			if(is_admin() && $pagenow == 'post.php'){
				$current_page = get_permalink((int) $_GET['post']);
			}
			else{
				$current_page = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
				$current_page = preg_replace('#[\?&]wordkeeper_purge_this_page#', '', $current_page);
			}

			$scheme = (strpos($current_page, 'https://') !== false) ? 'https://' : 'http://';
			$switchscheme = (strpos($current_page, 'https://') !== false) ? 'http://' : 'https://';

			$pages = array();
			$pages[] = $current_page;
			$pages[] = str_replace($scheme, $switchscheme, $current_page);

			$pages = json_encode($pages);

			$response = WordKeeper_System_Purge::purge_by_url($pages);

			if (!empty($response) && $response['response'] == 'OK') {
				//All caches purged successfully
			}
		}
    }

} //class end
