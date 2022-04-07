<?php
/**
 * The file that defines WordKeeper Purge functions
 *
 * @link       http://wordkeeper.com
 * @since      1.0.0
 *
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/includes
 */

/**
 * The WordKeeper System Purge class
 *
 * @since      1.0.0
 * @package    WordKeeper_System
 * @subpackage WordKeeper_System/includes
 * @author     Lance Dockins <info@wordkeeper.com>
 */
class WordKeeper_System_Purge {
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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version){
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
	}

	public static function purge_by_url($pages){
		$user = explode('/', trim(__DIR__, '/'));
		$user = $user[1];

		if(is_array($pages)){
			if(isset($pages['pages']) && is_array($pages['pages'])){
				$partial = $pages;
				unset($partial['pages']);
				$pages = array_unique(array_merge($partial, $pages['pages']));
				unset($partial);
			}
			$pages = json_encode($pages);
		}

		$response = WordKeeper_Utilities::http_post(
			'http://localhost/purge.php?cache=url',
			http_build_query(
				array(
					'user'  => $user,
					'cache' => 'url',
					'auth'  => CACHE_AUTH,
					'pages' => $pages,
					'path' => ABSPATH,
				)
			)
		);
		return $response;
	}

	public static function purge_all(){
		$user = explode('/', trim(__DIR__, '/'));
        $user = $user[1];

        $response = WordKeeper_Utilities::http_post(
            'http://localhost/purge.php?cache=all',
            http_build_query(
                array(
                    'user'  => $user,
                    'cache' => 'all',
                    'auth'  => CACHE_AUTH,
                    'path' => ABSPATH,
                )
            )
        );
		return $response;
	}

	/**
     * purge cache
     * @return void
     */
    public static function purge_cache() {
		$response = self::purge_all();
        if (!empty($response) && $response['response'] == 'OK') {
            //All caches purged successfully
        }
    }

	/**
	 * _purge_theme function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function _purge_theme() {
		add_action( 'fl_builder_after_save_layout', function() {
			self::purge_cache();
		} );
	}

	/**
	 * purge_post function.
	 *
	 * @access public
	 * @param mixed $post_id
	 * @return void
	 */
	public static function _purge_post($post_id) {
		if((is_admin() && is_user_logged_in() && current_user_can('publish_posts') && current_user_can('edit_posts')) || wp_doing_cron()) {
			if(is_numeric($post_id)) {

		        if(false !== wp_is_post_revision($post_id)) {
		            return;
		        }

				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
					return;
				}

				$post = get_post($post_id);

				if($post->post_type == 'shop_order') {
					return;
				}

				if($post->post_type == 'acf-field-group'){
					$response = self::purge_all();
					if (!empty($response) && $response['response'] == 'OK') {
						//All caches purged successfully
					}
					return;
				}

				if($post->post_status == 'auto-draft' || $post->post_status == 'draft'){
					return;
				}

				$url = get_permalink($post_id);

				$pages = array();

				if(!filter_var($url, FILTER_VALIDATE_URL) === false) {
					$pages[] = $url;
				}

                if ( class_exists( 'WooCommerce' ) && $post->post_type == 'product') {
                  $shop_page_url = wc_get_page_permalink( 'shop' );
                  $pages[] = $shop_page_url;
                }

                $home = rtrim(get_option('home'), '/');
                $scheme = (strpos($home, 'https://') !== false) ? 'https://' : 'http://';
                $switchscheme = (strpos($home, 'https://') !== false) ? 'http://' : 'https://';

				$pages[] = $home;
				$pages[] = $home . '/';
				$pages[] = $home . '/feed';
				$pages[] = $home . '/feed/';
				$pages[] = $home . '/feed/rss2';
				$pages[] = $home . '/feed/rss2/';
				$pages[] = $home . '/feed/rss';
				$pages[] = $home . '/feed/rss/';
				$pages[] = $home . '/feed/rdf';
				$pages[] = $home . '/feed/rdf/';
				$pages[] = $home . '/feed/atom';
				$pages[] = $home . '/feed/atom/';
				$pages[] = $home . '/robots.txt';
				$pages[] = $home . '/wp-json/wp/v2/posts';
				$pages[] = $home . '/wp-json/wp/v2/posts/';
				$pages[] = $home . '/wp-json/wp/v2/posts/' . $post->ID;
				$pages[] = $home . '/wp-json/wp/v2/posts/' . $post->ID . '/';
				$pages[] = $home . '/sitemap.xml';
				$pages[] = $home . '/sitemap_index.xml';

				$taxonomies = get_object_taxonomies($post);
				$categories = array();
				foreach($taxonomies as $taxonomy) {
					$terms = get_the_terms($post_id, $taxonomy);

					if(!is_wp_error($terms)) {
						if(is_array($terms)) {
							foreach($terms as $term) {
								$url = get_term_link($term, $taxonomy);

								if(!is_wp_error($url)) {
									$categories[] = $url;
								}
							}
						}
					}
				}

				foreach($categories as $category) {
					if(!filter_var($category, FILTER_VALIDATE_URL) === false) {
						$pages[] = $category;
						$pages[] = rtrim($category, '/') . '/feed';
						$pages[] = rtrim($category, '/') . '/feed/';
						$pages[] = rtrim($category, '/') . '/page/2';
						$pages[] = rtrim($category, '/') . '/page/2/';
					}
				}

				foreach($pages as $page) {
					if(strpos($page, '/wp-json/') !== false) {
						continue;
					}
                }

                $pages = apply_filters('wordkeeper_filter_purge_pages', $pages, $post);

                foreach($pages as $page) {
                    $pages[] = str_replace($scheme, $switchscheme, $page);
                }

				$pages = json_encode($pages);

				$response = self::purge_by_url($pages);

		        if (!empty($response) && $response['response'] == 'OK') {
		            //All caches purged successfully
		        }
			}

			clean_post_cache($post_id);
		}
	}

	/**
	 * _purge_comment function.
	 *
	 * @access public
	 * @param mixed $comment_id
	 * @return void
	 */
	public static function _purge_comment($comment_id) {
        if((is_admin() && is_user_logged_in() && current_user_can('publish_posts') && current_user_can('edit_posts')) || wp_doing_cron()) {
          if(is_numeric($comment_id)) {
              $comment = get_comment($comment_id);
              $post_id = $comment->comment_post_ID;

              self::_purge_post($post_id);
          }
      }
    }

	/**
	 * _purge_comment_transition function.
	 *
	 * @access public
	 * @param string $new_status
	 * @param string $old_status
	 * @param object $comment
	 * @return void
	 */
	public static function _purge_comment_transition($new_status, $old_status, $comment){
		if((is_admin() && is_user_logged_in() && current_user_can('publish_posts') && current_user_can('edit_posts')) || wp_doing_cron()) {
			$post_id = $comment->comment_post_ID;

            self::_purge_post($post_id);
		}
	}

    /**
	 * _purge_term_processor function.
	 *
	 * @access public
	 * @return void
	 */
    public static function _purge_term_processor() {
        if((is_admin() && is_user_logged_in() && current_user_can('publish_posts') && current_user_can('edit_posts')) || wp_doing_cron()) {
            $args_count = func_num_args();
            $args = func_get_args();

            if($args_count == 2) {
                // Triggered by edit_terms
                self::_purge_term($args[0], $args[1]);
            }
            else if($args_count == 5) {
                // Triggered by delete_term
                self::_purge_term($args[0], $args[2]);
            }
        }
    }

	/**
	 * _purge_term function.
	 *
	 * @access public
	 * @param mixed $term_id
	 * @return void
	 */
	public static function _purge_term($term_id, $taxonomy) {
        if(is_numeric($term_id)) {

            $args = array(
                'post_type' => 'post',
                'tax_query' => array(
                    array(
                        'taxonomy' => $taxonomy,
                        'field'    => 'term_id',
                        'terms'    => $term_id,
                    ),
                ),
            );

            $query = new WP_Query( $args );

            foreach ($query->posts as $post) {
                self::_purge_post($post->ID);
            }
        }
    }

    /**
	 * bulk_operations_cache_purge function.
	 *
	 * @access public
	 * @return void
	 */
    public function bulk_operations_cache_purge(){
        global $pagenow;
        $process_purge = false;

        $pages = array();

        // Need to process both bulk comments and page updates separately
        if($pagenow == 'edit-comments.php' && isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('approve', 'unapprove', 'trash', 'untrash', 'delete'))){
            $comments = $_REQUEST['delete_comments'];
            $unique_posts = array();
            if($comments && is_array($comments)){
                foreach($comments as $k => $c){
                    $comment = get_comment($c);
                    $post_id = $comment->comment_post_ID;

                    if(!in_array($post_id, $unique_posts)){
                        array_push($unique_posts, $post_id);
                    }
                }
            }

            if(count($unique_posts) > 0){
                foreach($unique_posts as $p){
                    $post_url = get_permalink($p);
                    if(!filter_var($post_url, FILTER_VALIDATE_URL) === false) {
                        $pages[] = $post_url;
                        $pages[] = $post_url . '/feed';
                        $pages[] = $post_url . '/feed/';
                    }
                }
                $process_purge = true;
            }
        }

        else if($pagenow == 'edit.php' && isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('edit', 'trash', 'untrash', 'delete'))){
            if(isset($_REQUEST['post']) && count($_REQUEST['post']) > 0){
                foreach($_REQUEST['post'] as $post_id){
                    $post_url = get_permalink($post_id);
                    if(!filter_var($post_url, FILTER_VALIDATE_URL) === false) {
                        $pages[] = $post_url;
                        $pages[] = $post_url . '/feed';
                        $pages[] = $post_url . '/feed/';
                    }
                }
                $process_purge = true;
            }
        }

        if($process_purge === false){
            return;
        }

        $pages = json_encode($pages);

		$response = self::purge_by_url($pages);

		if (!empty($response) && $response['response'] == 'OK') {
			//All caches purged successfully
		}
    }

    /**
	 * handle_post_status_transition
	 *
	 * @param  mixed $new_status
     * @param  mixed $old_status
     * @param  mixed $post
	 * @return void
	 */
    public function handle_post_status_transition ($new_status, $old_status, $post) {
        if (($old_status != 'publish') && ($new_status == 'publish')) {
            self::_purge_post($post->ID);
        }
    }

	/**
	 * purge_caches_after_product_stock_updates function.
	 *
	 * Purge all caches whenever the stock levels change after an order is placed.
	 *
	 * @access public
	 * @param mixed $order
	 * @return void
	 */
	function purge_caches_after_product_stock_updates($order){

		$items = $order->get_items();
		$pages = array();

		foreach( $items as $item ) {
			$url = get_permalink($item['product_id']);

			if(!filter_var($url, FILTER_VALIDATE_URL) === false) {
				$pages[] = $url;
				$pages[] = $url . '/feed';
				$pages[] = $url . '/feed/';
			}
		}

		$pages = json_encode($pages);
		$response = self::purge_by_url($pages);
		if (!empty($response) && $response['response'] == 'OK') {
			//All caches purged successfully
		}
	}
}
