<?php

global $wpdb;
$table_name = $wpdb->prefix . 'rentpress_units';
$wpdb->query("DROP TABLE IF EXISTS $table_name");