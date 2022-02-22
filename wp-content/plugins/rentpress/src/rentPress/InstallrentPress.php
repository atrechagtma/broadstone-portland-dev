<?php 

/**
* Install the RentPress Plugin
*/
class rentPress_InstallrentPress extends rentPress_Base_Installation
{

	public function install()
	{
        // Initialize Plugin Options
        // $this->initOptions(); // deprecated as of 10/02/17

        // Initialize DB Tables used by the plugin
        $this->installDatabaseTables();

        // Initialize custom post types
        $this->installCustomPostTypes();

        // Set plugin to 'installed'
        $this->options->markAsInstalled();
	}

    /**
     * Initialize plugin options -- DEPRECATED
     * @return void
     */
	public function initOptions()
	{
        $options = $this->options->getOptions();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->options->addOption($key, $arr[1]);
                }
            }
        }
	}

	public function installDatabaseTables()
	{
        global $wpdb;
        $units_table_name = $wpdb->prefix . 'rp_units';
        $charset_collate = 'COLLATE utf8mb4_unicode_ci';

        /**
         * THIS IF STATEMENT NEEDS TO STAY HERE -- DO NOT REMOVE
         * IF IT IS NOT HERE, WE END UP FILLING UP THE SERVER LOGS WITH STUPID DB ERRORS AND WARNINGS 
         * BECAUSE THE WAY THAT WORDPRESS WORKS WHEN YOU ALLOW THIS FUNCTION TO CONTINUOUSLY RUN IS THAT IT WILL
         * TRY TO ALTER THE TABLE FOR EACH COLUMN, AND FOR COLUMNS LIKE THE PRIMARY KEY IT WILL THROW ERRORS
         * CAUSE THE PRIMARY KEY ALREADY EXISTS AND THEREFORE CAUSES CONFLICT. WE NEED TO TRY AND KEEP OUR
         * LOG FILES AS CLEAN AS OUR CODE AND THIS IS HOW WE DO IT.
         *
         * IF YOU WANT TO MAKE CHANGES TO THE TABLE YOU WILL CREATE ANOTHER SQL STATEMENT TO ALTER THE CURRENT TABLE
         * AND THEN YOU WILL WRAP THAT IN AN APPROPRIATE IF STATEMENT TO CHECK AND SEE IF IT NEEDS TO BE RAN OR NOT. 
         *
         * IF THIS GETS REMOVED IT WILL BE PUT IT BACK EVER SINGLE TIME, TRUST ME. IT'S TIME TO BE MORE RESPONSIBLE
         */
        if($wpdb->get_var("SHOW TABLES LIKE '$units_table_name'") != $units_table_name) {
            $sql = "CREATE TABLE $units_table_name (
                unit_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                unit_code VARCHAR(20),
                prop_code VARCHAR(50),
                fpID VARCHAR(20),
                is_available TINYINT(1) DEFAULT 0,
                is_available_on DATE,
                rent FLOAT UNSIGNED NOT NULL DEFAULT 0, 
                beds FLOAT UNSIGNED NOT NULL DEFAULT 0,
                baths FLOAT UNSIGNED NOT NULL DEFAULT 0,
                sqft FLOAT UNSIGNED NOT NULL DEFAULT 0,
                tpl_data LONGTEXT,

                UNIQUE KEY unit_code (unit_code),
                KEY prop_code (prop_code),
                KEY fpID (fpID),
                KEY is_available (is_available),
                KEY is_available_on (is_available_on),
                KEY prop_to_beds (prop_code, beds),
                KEY prop_is_available (prop_code, is_available),
                KEY fp_is_available (fpID, is_available)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            dbDelta( $sql );
            
            maybe_convert_table_to_utf8mb4( $units_table_name );
            // maybe_convert_table_to_utf8mb4( $wpdb->rp_units );
        }

        /** @ToDo: Add new INDEX ( KEY code_code_code (unit_code, fpID, prop_code) ) to the wp_rp_units table */

        $wpdb->rp_units = $units_table_name;
    }

    public function installCustomPostTypes()
    {
        $this->postTypes->setUpCustomPostTypes();
    }

}