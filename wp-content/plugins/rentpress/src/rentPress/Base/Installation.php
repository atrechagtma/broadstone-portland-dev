<?php 

/**
* Installation base class
*/
abstract class rentPress_Base_Installation
{
    /** @var rentPress_Options [Helps us create our plugin options] */
    protected $options;

    public function __construct()
    {
        $this->options = new rentPress_Options();
        $this->postTypes = new rentPress_Posts_PostTypes();
    }

	// Initialize Plugin Options
	abstract public function initOptions();

	// Initialize DB Tables used by the plugin
	abstract public function installDatabaseTables();

    // Initialize custom post types
    abstract public function installCustomPostTypes();
}