<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wordkeeper.com
 * @since      1.0.0
 *
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/public
 * @author     Lance Dockins <info@wordkeeper.comm>
 */
class WordKeeper_System_Public {

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
	 * The settings for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $settings    The settings for the plugin
	 */
	private $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $wordkeeper_system       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $wordkeeper_system, $version ) {
		$this->wordkeeper_system = $wordkeeper_system;
		$this->version = $version;
		$this->settings = get_option('wordkeeper-system-settings');
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		// There are no public styles for this
		// wp_enqueue_style( $this->wordkeeper_system, plugin_dir_url( __FILE__ ) . 'css/wordkeeper-system-public.css', array(), $this->version, 'all' );
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// There are no public scripts for this
		// wp_enqueue_script( $this->wordkeeper_system, plugin_dir_url( __FILE__ ) . 'js/wordkeeper-system-public.js', array( 'jquery' ), $this->version, false );
	}


	/**
	* Disallow my account, cart, checkout, and add to cart links
	*
	* @since    1.0.0
	* @param    string    $output		The robots.txt content
	* @param    string    $public		Whether the site is considered "public" or not
	*/
	public function robots( $output, $public ) {
		$add = array();
		$list = array();
		$botmap = array(
			'ahrefs' => array(
				'AhrefsBot',
				'AhrefsSiteAudit',
			),
			'moz' => 'rogerbot',
			'semrush' => array(
				'SemrushBot',
				'SemrushBot-SA',
				'SemrushBot-BA',
				'SemrushBot-SI',
				'SemrushBot-SWA',
				'SemrushBot-CT',
				'SemrushBot-BM',
				'SplitSignalBot',
			),
			'screaming-frog' => 'Screaming Frog SEO Spider',
			'majestic' => 'MJ12bot',
			'dataforseo' => 'DataForSeoBot',
			'yandex' => 'Yandex',
			'baidu' => 'Baiduspider',
			'huawei' => 'PetalBot',
			'seznam' => 'SeznamBot',
			'mailru' => 'Mail.Ru',
			'qwant' => 'Qwantify',
			'sogou' => array(
				'Sogou Spider',
				'Sogou blog',
				'Sogou inst spider',
				'Sogou News Spider',
				'Sogou Orion spider',
				'Sogou spider2',
				'Sogou web spider',
			),
			'coccoc' => 'coccoc',
		);

		$list[] = '/*?s=*';
		$list[] = '/*&s=*';
		$list[] = '/*?p=*';
		$list[] = '/*&p=*';
		$list[] = '/?author=*';
		$list[] = '/*wp-comments*';
		$list[] = '/*wp-trackback*';
		$list[] = '/*wp-feed*';
		$list[] = '/*replytocom=*';
		$list[] = '/*?preview=*';
		$list[] = '/*&preview=*';
		$list[] = '/*add-to-cart=*';
		$list[] = '/*add_to_wishlist=*';
		$list[] = '/*cart/*';
		$list[] = '/*checkout/*';
		$list[] = '/*my-account/*';
		$list[] = '/*myaccount/*';

		if(function_exists('wc_get_page_permalink')){
			$checkout = wc_get_page_permalink('checkout');
			$checkout = parse_url($checkout);
			$checkout = rtrim($checkout['path'], '/') . '/*';
			$list[] = $checkout;

			$cart = wc_get_page_permalink('cart');
			$cart = parse_url($cart);
			$cart = rtrim($cart['path'], '/') . '/*';
			$list[] = $cart;

			$account = wc_get_page_permalink('myaccount');
			$account = parse_url($account);
			$account = rtrim($account['path'], '/') . '/*';
			$list[] = $account;
		}

		if(class_exists('WooCommerce')){
			$list[] = '/*wc-ajax=add_to_cart';
			$list[] = '/*wc-ajax=remove_from_cart';

			$list[] = '/*orderby=price';
			$list[] = '/*orderby=rating';
			$list[] = '/*orderby=date';
			$list[] = '/*orderby=price-desc';
			$list[] = '/*orderby=popularity';
			$list[] = '/*orderby=title';
			$list[] = '/*orderby=desc';

			$list[] = '/*?filter=';
			$list[] = '/*&filter=';

			$list[] = '/*paged=&count=*';

			$list[] = '/*?count=*';
			$list[] = '/*&count=*';
		}

		foreach($list as $item){
			if(strpos($output, ltrim($item, '/')) === false || substr($item, 0, 2) == '//'){
				$add[$item] = true;
			}
		}

		if(count($add) > 0){
			$values = array_keys($add);
			$add = implode("\nDisallow: ", $values);
			$add = "# Stop bots from crawling junk URLs\nUser-agent: *\nDisallow: ". $add;
			$output .= "\n" . $add;
		}

		foreach($botmap as $bot => $useragent){
			if(!$this->settings[$bot]) {
				if(is_string($useragent)){
					$output .= "\n\nUser-agent: " . $useragent . "\n";
					$output .= "Disallow: /";
				}
				elseif(is_array($useragent)){
					foreach($useragent as $name){
						$output .= "\n\nUser-agent: " . $name . "\n";
						$output .= "Disallow: /";
					}
				}
			}
		}

		return $output;
	}


	/**
	 * Send relevant cache control headers
	 *
	 * @return void
	 */
	public function cache_control(){
		global $post;

		$path = parse_url($_SERVER['REQUEST_URI']);
		$path = (isset($path['path'])) ? rtrim($path['path'], '/') : '/';
		$disable = array();
		$cachetime = 10800;

		// Cache single posts for varying lengths by age of post
		if(is_singular()){
			$modified = get_the_modified_date('U', $post);
			$delta = strtotime('now') - $modified;

			// Less than a month
			if($delta < 2592000) {
				$cachetime = 86400;
			}
			// 1-6 months
			elseif($delta < 15768000 && $delta > 2592000){
				$cachetime = 2592000;
			}
			// 6-12 months
			elseif($delta < 31536000 && $delta > 15768000){
				$cachetime = 15768000;
			}
			else{
				$cachetime = 31536000;
			}
		}
		// Cache search page for 3 hours
		elseif(is_search()){
			// 3 hours
			$cachetime = 10800;
		}
		// Cache archive pages for 1 day
		elseif(is_archive()){
			// 24 hours
			$cachetime = 86400;
		}

		switch(trim($_SERVER['SCRIPT_NAME'], '/')){
			case 'wp-login.php':
				$cachetime = 0;
				break;
			default:
				break;
		}

		// Add Woo URLs to cache disable array
		if(function_exists('wc_get_page_permalink')){
			$checkout = wc_get_page_permalink('checkout');
			$checkout = parse_url($checkout);
			$checkout = rtrim($checkout['path'], '/');
			$disable[] = $checkout;

			$cart = wc_get_page_permalink('cart');
			$cart = parse_url($cart);
			$cart = rtrim($cart['path'], '/');
			$disable[] = $cart;

			$account = wc_get_page_permalink('myaccount');
			$account = parse_url($account);
			$account = rtrim($account['path'], '/');
			$disable[] = $account;
		}

		// Disable cache for any path in the disable array
		if(!empty($disable)){
			$check = str_replace($disable, '', $path);
			if($path != $check){
				$cachetime = 0;
			}
		}

		$cachetime = apply_filters( 'wordkeeper_filter_cachetime', $cachetime );

		header( 'X-Accel-Expires: ' . $cachetime );
	}


	/**
	 * heartbeat_control function.
	 *
	 * @since    1.0.0
	 * @access public
	 * @return void
	 */
	public function heartbeat_control() {
		global $pagenow;

		switch ($this->settings['heartbeat-permission']) {
			case 'disable-heartbeat-everywhere':
				wp_deregister_script('heartbeat');
				break;
			case 'disable-heartbeat-dashboard':
				if($pagenow == 'index.php') {
					wp_deregister_script('heartbeat');
				}
				break;
			case 'allow-heartbeat-post-edit':
				if($pagenow != 'post.php' && $pagenow != 'post-new.php') {
					wp_deregister_script('heartbeat');
				}
			break;
		default:
			break;
		}
	}


	/**
	 * heartbeat_frequency function.
	 *
	 * @since    1.0.0
	 * @access public
	 * @param mixed $settings
	 * @return void
	 */
	public function heartbeat_frequency($settings) {
		$settings['interval'] = $this->settings['heartbeat-frequency'];
		return $settings;
	}
}
