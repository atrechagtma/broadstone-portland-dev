<?php 

/**
* Custom Post Type initializer
*/
class rentPress_Posts_PostTypes
{

	public function __construct()
	{
		$this->taxonomies = new rentPress_Posts_Taxonomy_Taxonomies();
		$this->metaBoxes = new rentPress_Posts_Meta_MetaBoxes();

        $this->properties_searchAddon = new rentPress_Properties_SearchAddon();
        $this->properties_wpQueryDistanceAddon = new rentPress_Properties_WpQueryDistanceAddon();
        $this->properties_wpQueryFloorplansRelationshipAddon = new rentPress_Properties_WpQueryFloorplansRelationshipAddon();
        $this->properties_wpQueryUnitsRelationshipAddon = new rentPress_Properties_WpQueryUnitsRelationshipAddon();

        $this->properties_wpAdminAddon = new rentPress_Properties_WpAdminAddon();
        $this->properties_restApi = new rentPress_Properties_RestApi();

        $this->neighborhoods_restApi = new rentPress_Neighborhoods_RestApi();
        $this->neighborhoods_wpAdminAddon = new rentPress_Neighborhoods_WpAdminAddon();
        
        $this->floorplans_searchAddon = new rentPress_FloorPlans_SearchAddon();
        $this->floorplans_wpQueryUnitsRelationshipAddon = new rentPress_FloorPlans_WpQueryUnitsRelationshipAddon();

        $this->floorplans_wpAdminAddon = new rentPress_FloorPlans_WpAdminAddon();
        $this->floorplans_restApi = new rentPress_FloorPlans_RestApi();
       
        $this->units_restApi = new rentPress_Units_RestApi();
        $this->units_wpAdminPage = new rentPress_Units_WpAdminPage();
        $this->units_wpAdminAddPage = new rentPress_Units_WpAdminAddUnit();
	}

	public function setUpCustomPostTypes()
	{
		/* Custom Post Types */
		add_action( 'init', [ $this, 'addPropertyPosts' ], 0);
		add_action( 'init', [ $this, 'addFloorplanPostType' ], 0);
        add_action( 'init', [ $this, 'addNeighborhoodPostType' ], 0);

		/* Taxonomies */
		$this->taxonomies->create();

		/* Meta Boxes */
		$this->metaBoxes->create();

        /* Properties Search Addons */
        $this->properties_searchAddon->run();
        $this->properties_wpQueryDistanceAddon->run();
        $this->properties_wpQueryFloorplansRelationshipAddon->run();
        $this->properties_wpQueryUnitsRelationshipAddon->run();        

        $this->properties_wpAdminAddon->run();
        $this->properties_restApi->run();

        /* Neighborhoods Search Addons */
        $this->neighborhoods_restApi->run();
        $this->neighborhoods_wpAdminAddon->run();

        /* Floorplans Search Addons */
        $this->floorplans_searchAddon->run();
        $this->floorplans_wpQueryUnitsRelationshipAddon->run();

        $this->floorplans_wpAdminAddon->run();
        $this->floorplans_restApi->run();

        /* Units WP REST And Admin Pages */
        $this->units_restApi->run();

        $this->units_wpAdminPage->run();
        $this->units_wpAdminAddPage->run();

        /* Post-Type Connections */
        $p2p_from_properties_to_neighborhoods=new rentPress_Posts_PostTypeConnection(RENTPRESS_PROPERTIES_CPT, RENTPRESS_NEIGHBORHOODS_CPT, false);
	}

    /**
     * Property Post Type Registration
     */
    public function addPropertyPosts() {

        $args = $this->buildCustomPostTypeArgs('Property', 'Properties', [
            'hierarchical'        => false,
            'menu_position'       => 6,
            'menu_icon'           => RENTPRESS_PLUGIN_ASSETS . '/images/apartments-icon.png',
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
            'rewrite'             => array( 'slug' => 'apartments' ), // Could cause breaks in legacy sites
        ]);

        register_post_type( RENTPRESS_PROPERTIES_CPT, $args );
    }

    /**
     *  Floor Plan Post Type Registration
     */
    public function addFloorplanPostType() {

        $args = $this->buildCustomPostTypeArgs('Floor Plan', 'Floor Plans', [
            'hierarchical'        => false,
            'menu_position'       => 7,
            'menu_icon'           => 'dashicons-admin-multisite',
            'has_archive'         => false,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        ]);
        register_post_type( RENTPRESS_FLOORPLANS_CPT, $args );
    }

    public function addNeighborhoodPostType() {

        $args = $this->buildCustomPostTypeArgs('Neighborhood', 'Neighborhoods', [
            'hierarchical'        => false,
            'public'              => true,
            'menu_position'       => 8,
            'menu_icon'           => 'dashicons-location-alt',
            'can_export'          => true,
            'has_archive'         => false,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page'
        ]);
        
         if ( ! post_type_exists(RENTPRESS_NEIGHBORHOODS_CPT) && ! taxonomy_exists(RENTPRESS_NEIGHBORHOODS_CPT)) {
            register_post_type( RENTPRESS_NEIGHBORHOODS_CPT, $args );
        }
    }

    public function customPostTypeLabels($singular, $plural)
    {
        return [
            'name'                => _x( $plural, 'Post Type General Name', RENTPRESS_LANG_KEY ),
            'singular_name'       => _x( $singular, 'Post Type Singular Name', RENTPRESS_LANG_KEY ),
            'menu_name'           => __( $plural, RENTPRESS_LANG_KEY ),
            'parent_item_colon'   => __( "Parent $singular:", RENTPRESS_LANG_KEY ),
            "all_items"           => __( "All $plural", RENTPRESS_LANG_KEY ),
            "view_item"           => __( "View $singular", RENTPRESS_LANG_KEY ),
            "add_new_item"        => __( "Add New $singular", RENTPRESS_LANG_KEY ),
            "add_new"             => __( "Add New $singular", RENTPRESS_LANG_KEY ),
            "edit_item"           => __( "Edit $singular", RENTPRESS_LANG_KEY ),
            "update_item"         => __( "Update $singular", RENTPRESS_LANG_KEY ),
            "search_items"        => __( "Search $plural", RENTPRESS_LANG_KEY ),
            "not_found"           => __( "No $plural found", RENTPRESS_LANG_KEY ),
            "not_found_in_trash"  => __( "Not found in Trash", RENTPRESS_LANG_KEY ),
        ];
    }

    public function buildCustomPostTypeArgs($singular, $plural, $args = [])
    {
        $labels = $this->customPostTypeLabels($singular, $plural);
        return array_merge([
            'label'               => __( $plural, RENTPRESS_LANG_KEY ),
            'description'         => __( "$plural post type", RENTPRESS_LANG_KEY ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'thumbnail' ),
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 6,
            'can_export'          => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        ], $args);
    }
}