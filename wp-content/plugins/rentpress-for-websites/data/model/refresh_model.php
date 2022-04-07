<?php

function rentpress_saveRefreshData($property_code, $response)
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'rentpress_refresh';
    $result = 'NoCode';

    if (isset($property_code) && $property_code != '') {
        $result = $wpdb->replace(
            $table_name,
            array(
                'property_code' => $property_code,
                'last_refresh_time' => time(),
                'property_response' => json_encode($response),

            )
        );
    }

    // TODO: @Ryan this needs to log the result for the user
    if ($result == 'false' || $result == 'NoCode') {
        // logger(json_encode($result));
    }
}

// TODO: make this function only delete a single property based on property code, for now truncated the table works fine
function rentpress_deleteRefreshData()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'rentpress_refresh';

    $wpdb->query("TRUNCATE TABLE $table_name");
}

function rentpress_makeRefreshDBTable()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'rentpress_refresh';

    $sql = "CREATE TABLE $table_name (
		property_code varchar(191) NOT NULL,
		last_refresh_time varchar(191),
		property_response longtext,

		UNIQUE KEY property_code (property_code)
	) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// TODO: 7.1 @Charles Make this run on plugin update, install, and delete
function rentpress_dropRefreshDBTable()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'rentpress_refresh';

    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
