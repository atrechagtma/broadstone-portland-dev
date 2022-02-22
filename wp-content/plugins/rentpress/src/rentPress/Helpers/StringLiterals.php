<?php 

/**
* To help store duplicated string literals in constants and reduce duplicate string literal usage across the plugin
*/
class rentPress_Helpers_StringLiterals
{
	/* ================================ General ================================ */

	// True/false strings
	public static $rp_true = 'true';
	public static $rp_false = 'false';

	// Activation strings
	public static $activeStateKey = '_activated';

	// Access control strings
	public static $canRefresh = 'can_refresh';

	/* ================================ RentPress Transient Keys ================================ */

	public static $refreshPropertiesKey = 'rentPress_refresh_feed_cron';
	public static $overrideMetaPrefix = 'override_meta_';

	/* ================================ Query Vars ================================ */

	// Units Query parameters
	public static $property_ids = 'property_ids';
	public static $floorplan_ids = 'floorplan_ids';



}