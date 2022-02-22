<?php 
	class rentPress_Posts_PostTypeConnection {
		public static $input_meta_key='post_type_connection_to';

		public $connection_from;
		public $connection_to;
		public $multipleSelection = false;

		public function __construct($from, $to, $multipleSelection = false)
		{
			$this->connection_from=$from;
			$this->connection_to=$to;
			$this->multipleSelection=$multipleSelection;

	        add_action('add_meta_boxes', [ $this, 'addMetaBox' ], 0);
	        add_action('save_post_'.$this->connection_from, [ $this, 'save_connection' ], 10, 3);
	        add_action('query_vars', [ $this, 'addQueryVars' ], 0);
			add_action('pre_get_posts', [$this, 'pre_get_posts_connection'], 35, 1);
		}
		
		public function addMetaBox()
		{
	        global $_wp_post_type_features, $post;

			$to_post_type = get_post_type_object( $this->connection_to );

			add_meta_box(
	            'post_type_connection_to_'.$this->connection_to,
	            $to_post_type->labels->name,
	            [$this, 'defaultMetaBoxLayout'],
	            $this->connection_from,
	            'side',
	            'default'
	        );
	    }

	    public function defaultMetaBoxLayout($post) 
	    {
	        $checklist_posts=get_posts([
	            'post_type' => $this->connection_to,
	            'post_status' => 'any',
	            'orderby' => 'title',
	            'order' => 'ASC',
	            'nopaging' => true,
	        ]);

	        $input_name=self::$input_meta_key.'['. $this->connection_to .']';

	        $connected_to=get_post_meta($post->ID, 'post_type_connected_to_'.$this->connection_to, true);

	        /* Display input and nonce field */ ?>
	        <div class="rentPress-meta-container">
				<!-- <ul class="wp-tab-bar">
	                <li class="tabs"><a href="">All</a></li>
	            </ul> -->

	            <div style="" class="wp-tab-panel">
					<?php if ($this->multipleSelection) : ?>
	                	<input type="hidden" name="<?php echo $input_name; ?>" value="[]">
	                <?php endif; ?>

	                <ul>
	                    <?php foreach ($checklist_posts as $checklist_post) { ?>
	                        <li>
	                            <label>
	                                <?php if ($this->multipleSelection) : ?>
	                                	<input 
	                                		type="checkbox" 
	                                		name="<?php echo $input_name; ?>[]" 
	                                		value="<?php echo $checklist_post->ID; ?>"  
	                                		<?php if (is_array($connected_to)) {checked(true, in_array($checklist_post->ID, $connected_to)); } ?>

	                                	/>
	                                <?php else : ?>
	                                	<input 
	                                		type="radio" 
	                                		name="<?php echo $input_name; ?>" 
	                                		value="<?php echo $checklist_post->ID; ?>"  
	                                		<?php checked($checklist_post->ID, $connected_to); ?>
	                                	/>
	                                <?php endif; ?>

	                                <?php echo $checklist_post->post_title; ?>
	                            </label>
	                        </li>
	                    <?php } ?>
	                </ul>
	            </div>

	        </div> <?php
	    }

	    public function save_connection($post_id, $post, $updated) {
	    	if ($post->post_type != $this->connection_from) {
	    		return;
	    	}

	    	$is_autosave = wp_is_post_autosave( $post_id );
			$is_revision = wp_is_post_revision( $post_id );
			$is_valid_nonce = ( isset( $_POST[ 'example_nonce' ] ) && wp_verify_nonce( $_POST[ 'example_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
	 
			// Exits script depending on save status
			if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
				return;
			}

			if (isset($_POST['post_type_connection_to']) && isset($_POST['post_type_connection_to'][ $this->connection_to ])) {
				update_post_meta($post_id, 'post_type_connected_to_'.$this->connection_to, $_POST['post_type_connection_to'][ $this->connection_to ]);
			}
	    }

	    public function addQueryVars($vars) {

	    	$vars[]='post_type_connection_to';
	    	$vars[]='post_type_connection_value';

	    	return $vars;
		}

	    public function pre_get_posts_connection($query) {
	    	if ($query->get('post_type') == $this->connection_from && $query->get('post_type_connection_to') == $this->connection_to && $query->get('post_type_connection_value')) {
	    		$connections_meta_query=[];

	    		/*
	    			if ($this->multipleSelection) {
			    		$connections_meta_query[]=[
			    			'key' => 'post_type_connected_to_'. $query->get('post_type_connection_to'),
			    			'compare' => 'LIKE',
			    			'value' => $query->get('post_type_connection_value'),
			    		];
	    			}
	    		*/
	    		if (is_array($query->get('post_type_connection_value'))) {
		    		$connections_meta_query[]=[
		    			'key' => 'post_type_connected_to_'. $query->get('post_type_connection_to'),
		    			'compare' => 'IN',
		    			'value' => $query->get('post_type_connection_value'),
		    		];
	    		}
	    		elseif (rentPress_searchHelpers::if_query_var_check($query->get('post_type_connection_value'))) {
		    		$connections_meta_query[]=[
		    			'key' => 'post_type_connected_to_'. $query->get('post_type_connection_to'),
		    			'compare' => '=',
		    			'value' => $query->get('post_type_connection_value'),
		    		];	    			
	    		}

	    		if (count($connections_meta_query) >= 1) {
	    			$currentMetaQuery=$query->get('meta_query');
						if (is_array($currentMetaQuery)) {
							$currentMetaQuery[]=$connections_meta_query;
						}
						else {
							$currentMetaQuery=[ $connections_meta_query ];
						}

						$query->set('meta_query', $currentMetaQuery);
				}
	    	}

	    	return $query;
	    }



	}