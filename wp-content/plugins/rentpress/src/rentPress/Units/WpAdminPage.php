<?php
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	// Commenting below line result in error.

	class RP_Units_WPA_Table extends WP_List_Table {
		/**
		* Constructor, we override the parent to pass our own arguments
		* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
		*/
		public function __construct() {
			parent::__construct( array(
				'singular'=> 'wp_list_rp_units', //Singular label
				'plural' => 'wp_list_rp_units', //plural label, also this well be one of the table css class
				'ajax'   => false //We won't support Ajax for this table
			) );
		}

		function get_columns() {
			$columns=[
				'cb' => '<input type="checkbox" />',
				'col_unit_code' => 'Unit Code',
				'col_prop_code' => 'Prop Code',
				'col_fpID' => __('FP ID'),
				'col_is_available' => __('Is Available'),
				'col_available_date' => __('A Date'),
				'col_rent' => __('Rent'),
				'col_beds' => __('Beds'),
				'col_baths' => __('Baths'),
				'col_sqft' => __('SqFt'),
				'col_tpl_data' => __('TPL Data')
			];
	
			return $columns;
		}

		public function get_sortable_columns() {
			$sortable_columns = array(
				'col_unit_code' => ['unit_code', true],
				'col_prop_code' => ['prop_code', true],
				'col_fpID' => ['fpID', true],
				'col_is_available' => ['is_available', true],
				'col_available_date' => ['is_available_on', true],
				'col_rent' => ['rent', true],
				'col_beds' => ['beds', true],
				'col_baths' => ['baths', true],
				'col_sqft' => ['sqft', true]
			);

			return $sortable_columns;
		}

		/**
		 * Prepare the table with different parameters, pagination, columns and table elements
		 */
		function prepare_items() {
			global $wpdb;


       		$this->process_bulk_action();

			$columns = $this->get_columns();
	        $hidden = $this->get_hidden_columns();
	        $sortable = $this->get_sortable_columns();

			/* -- Preparing your query -- */
			$units_args=[
				'return' => "*",
			];

			if (isset($_REQUEST['query'])) {
				$units_args=array_merge(
					$units_args, 
					$_REQUEST['query']
				);
			}

			/* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result
			$orderby = !empty($_GET["orderby"]) ? ($_GET["orderby"]) : 'ASC';
			$order = !empty($_GET["order"]) ? ($_GET["order"]) : '';
			
			if (!empty($orderby) & !empty($order)){
				$units_args['order_by'] = $orderby.':'.$order;
			}

			// Make Fany Query
			$fancy_query=new rentPress_Units_Query($units_args);

			/* -- Pagination parameters -- */
			//Number of elements in your table?	
			$totalitems = $wpdb->query($fancy_query->mysql_query); //return the total number of affected rows
			//How many to display per page?
			$perpage = $units_args['limit'] = 100;
		
			//Which page is this?
			$paged = !empty($_GET["paged"]) ? ($_GET["paged"]) : '';
			//Page Number
			if (empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; } //How many pages do we have in total? 
			
			$totalpages = ceil($totalitems/$perpage); //adjust the query to take pagination into account 
			
			if (!empty($paged) && !empty($perpage)){ 
				$offset = $units_args['offset'] = ($paged-1)*$perpage; 
			} 
			/* -- Register the pagination -- */ 
			$this->set_pagination_args( array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page" => $perpage,
			) );

			// The pagination links are automatically built according to those parameters
			$this->_column_headers = array($columns, [], $sortable);

			$fancy_query=new rentPress_Units_Query($units_args);

			$this->items = $wpdb->get_results($fancy_query->mysql_query);



		}


		/**
		 * Display the rows of records in the table
		 * @return string, echo the markup of the rows
		 */
		function display_rows() {

		   //Get the records registered in the prepare_items method
		   $records = $this->items;

		   //Get the columns registered in the get_columns and get_sortable_columns methods
		   $columns=$this->get_columns();

		   //Loop for each record
		   if(!empty($records)){foreach($records as $rec){

		      //Open the line
		      echo '<tr id="record_'.$rec->unit_id.'">';
		      foreach ( $columns as $column_name => $column_display_name ) {

		         //Style attributes for each col
		         $class = "class='$column_name column-$column_name'";
		         $style = "";
		         
		         $attributes = $class . $style;

		         //edit link
		         //$editlink  = '/wp-admin/link.php?action=edit&link_id='.(int)$rec->link_id;

		         //Display the cell
		         switch ( $column_name ) {
		         	case "cb": 
		         		echo '<td><input type="checkbox" class="checkbox" id="cb-select-'. $rec->unit_code .'" name="bulk_edit[]" value="'. $rec->unit_code .'"></td>';
		         		break;
		            case "col_unit_code": 
		            	echo '<td '.$attributes.'><b>'.stripslashes($rec->unit_code).'</b></td>'; 
		            	break;
		            
		            case "col_prop_code": 
		            	$link=get_admin_url(null, "edit.php?post_type=properties&property_code=".$rec->prop_code);

		            	echo '<td '.$attributes.'><a target="_blank" href="'. $link .'">'.stripslashes($rec->prop_code).'</a></td>';

		            	break;

		            case "col_fpID": 
		            	$link=get_admin_url(null, "edit.php?post_type=floorplans&fpID=".$rec->fpID);;

		            	echo '<td '.$attributes.'><a target="_blank" href="'. $link .'">'.stripslashes($rec->fpID).'</a></td>';

		            	break;
		            
		            case "col_is_available": echo '<td '.$attributes.'>'. (((int) $rec->is_available)?"TRUE":"FALSE") .'</td>'; break;
		            case "col_available_date": echo '<td '.$attributes.'>'.$rec->is_available_on.'</td>'; break;
		            case "col_rent": echo '<td '.$attributes.'>'.$rec->rent.'</td>'; break;
		            case "col_beds": echo '<td '.$attributes.'>'.$rec->beds.'</td>'; break;
		            case "col_baths": echo '<td '.$attributes.'>'.$rec->baths.'</td>'; break;
		            case "col_sqft": echo '<td '.$attributes.'>'.$rec->sqft.'</td>'; break;
		            case "col_tpl_data": echo '<td '.$attributes.'>
		            	<div id="'. $rec->unit_id .'_json_viewer"></div>

		            	<script type="text/javascript">
		            		jQuery("#'. $rec->unit_id .'_json_viewer").JSONView({data:'. $rec->tpl_data .'});
		            		jQuery("#'. $rec->unit_id .'_json_viewer").JSONView("collapse");
		            	</script>
		            </td>'; break;
		         }
		      }

		      //Close the line
		      echo'</tr>';
		   }}
		}

		public function get_bulk_actions() {

        	return array(
	            'delete' => 'Delete Selected Units',
	            'delete_every_unit' => 'Delete Every Unit!'
	        );

    	}


	    public function process_bulk_action() {
	    	global $wpdb;

	        // security check!
	        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

	            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
	            $action = 'bulk-' . $this->_args['plural'];

	            if ( ! wp_verify_nonce( $nonce, $action ) )
	                wp_die( 'Nope! Security check failed!' );

	        }

	        $action = $this->current_action();

	        switch ( $action ) {

	            case 'delete':

	            	if (isset($_POST['bulk_edit'])) {
	            		if (is_array($_POST['bulk_edit'])) {
		            		foreach ($_POST['bulk_edit'] as $unit_code) {
	            				$wpdb->delete($wpdb->rp_units, ['unit_code' => $unit_code]);
		            		}
	            		}

	            		echo "<span style='color: red;'>These Units Were Deleted, (". join(", ", $_POST['bulk_edit']) .").</span>";
	     	       	}

	                break;

	            case "delete_every_unit":
	            	$wpdb->query("TRUNCATE $wpdb->rp_units");
	            	wp_die("<span style='color: red;'>ALL UNITS DELETED</span>");
	            	break;

	            default:
	                // do nothing or something else
	                break;
	        }

	        return;
	    }


	}

	class rentPress_Units_WpAdminPage{
		function __construct() {
			$this->options=new rentPress_Options();
		}	

		public function run()
		{
			// Add main menu page
			add_action( 'admin_menu', [$this, 'unitAdminPages'], 99 );
		}


		public function unitAdminPages()
		{
			add_menu_page(
				'Units',
				'Units',
				'manage_options',
				'rp_units_viewer',
				[ $this, 'render_page' ],
				'dashicons-screenoptions',
				7
			);
		}

		public function render_page() {
			global $wpdb;

			$rp_units_admin_table = new RP_Units_WPA_Table();
			$rp_units_admin_table->prepare_items();

			$properties_and_floorplans=[];

			$properties=get_posts([
				'post_type' => 'properties', 
				'post_status' => ['publish', 'draft'], 
				'orderby' => 'title',
				'order' => 'ASC',
				'meta_query' => [
					[
						'key' => 'prop_code',
						'compare' => 'EXISTS',
					],
				],
				'nopaging' => true, 
			]);

			ob_start(); ?>

			<div class="wrap">
				<h1 class="wp-heading-inline">Units</h1>

				<?php if ($this->options->getOption('api_token') == '') : ?>
					<a href="<?php echo admin_url( 'admin.php?page=rp_unit_add' ); ?>" class="page-title-action">Add New Unit</a>
				<?php endif; ?>


				<form action="" method="get">
					<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
					<h4>Filters:</h4>
					
					<p>
						<select name="query[post_id]">
				        	<option value="">Property Or Floorplan</option>

							<?php
								$current_v = isset($_REQUEST['query']['post_id'])?$_REQUEST['query']['post_id']:'';

								foreach ($properties as $property) {
									echo "<option value='". $property->ID ."' ". selected($property->ID, $current_v) .">". $property->post_title ."</option>";

									$floorplans=get_posts([
										'post_type' => 'floorplans',
										'floorplans_of_property' => $property->ID,
										'orderby' => 'title',
										'order' => 'ASC',
										'nopaging' => true,
									]);

									foreach ($floorplans as $floorplan) {
										echo "<option value='". $floorplan->ID ."' ". selected($floorplan->ID, $current_v) .">&nbsp;&nbsp;&nbsp;". $floorplan->post_title ."</option>";
									}
								} 
							?>
						</select>
					</p>

					<p>
						<input type="text" name="query[unit_code]" placeholder="Unit Code" value="<?php echo (isset($_REQUEST['query']) && isset($_REQUEST['query']['unit_code']))?$_REQUEST['query']['unit_code']:""; ?>">
						
						<input type="text" name="query[prop_code]" placeholder="Property Code" value="<?php echo (isset($_REQUEST['query']) && isset($_REQUEST['query']['prop_code']))?$_REQUEST['query']['prop_code']:""; ?>">

						<input type="text" name="query[fpID]" placeholder="Floorplan Code" value="<?php echo (isset($_REQUEST['query']) && isset($_REQUEST['query']['fpID']))?$_REQUEST['query']['fpID']:""; ?>">
					</p>

					<p>
						<input type="number" name="query[beds]" min="0" placeholder="Bedrooms" value="<?php echo (isset($_REQUEST['query']) && isset($_REQUEST['query']['beds']))?$_REQUEST['query']['beds']:""; ?>">
						<input type="number" name="query[baths]" min="0" step="0.1" placeholder="Bathrooms" value="<?php echo (isset($_REQUEST['query']) && isset($_REQUEST['query']['baths']))?$_REQUEST['query']['baths']:""; ?>">
						<input type="date" name="query[available_by]" placeholder="Available By Date" value="<?php echo (isset($_REQUEST['query']) && isset($_REQUEST['query']['available_by']))?$_REQUEST['query']['available_by']:""; ?>">
					</p>

					<input type="submit" value="Filter">
				</form>				

				<form action="" method="post">
					<?php
						$rp_units_admin_table->display(); 
					?>

					<style>
						.column-col_tpl_data{width: 350px;}
					</style>
				</form>
			</div>

			<script type="text/javascript">
				var selectAll1 = document.getElementById("cb-select-all-1");
				var selectAll2 = document.getElementById("cb-select-all-2");
				var selectAllButtons = [selectAll1, selectAll2];
				var checkboxes = document.getElementsByClassName('checkbox');
				var checkboxArr = [].slice.call(checkboxes);
				var state = false;

				function changeState(){
					state = !state;
					for(var i = 0; i < checkboxes.length; i++) {
						checkboxes[i].checked = state;  
				  }
				}
				function clearSelectAll(){
					for(var i = 0; i < selectAllButtons.length; i++) {
						selectAllButtons[i].checked = false;  
				  	}
				}
				function checkIfAllChecked(){
					var checkedBoxes = 0;
					for(var i = 0; i < checkboxes.length; i++) {
						if (checkboxes[i].checked){
							checkedBoxes++;
						}  
				  	}
				  	if (checkedBoxes == checkboxes.length) {
				  		return true;
				  		state = true;
				  	} else {
				  		state = false;
				  	}
				}
				function toggleAll(){
					selectAllButtons.forEach(function(elem) {
					    elem.addEventListener("click", function() {
							changeState();
					    });
					});
				}				
				function sync(){
					checkboxArr.forEach(function(elem) {
					    elem.addEventListener("click", function() {
							checkIfAllChecked();
							if(true){
								clearSelectAll();
							}
					    });
					});
				}
				function setUp(){
					toggleAll();
					sync();
				}
				setUp();
			</script>

			<?php
			echo ob_get_clean();
		}
	}