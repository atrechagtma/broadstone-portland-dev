<?php 

/**
 * Custom  RentPress Search Class
 * Extends RentPress's rentPress_WPSearch class to easily construct WP meta_query requests for searching properties 
 */
class rentPress_Search_Properties extends rentPress_Base_Search {
    /**
     * Array of user requested search arguments
     * @var array
     */ 
    public $userArgs;
    public $config = [
        'sqftg' => true,
        'rent' => true,
        'beds' => true, 
        'baths' => true
    ];
    private static $instance = null;

    /**
     * Creates or returns an instance of this class.
     *
     * @return  rentPress_FloorPlans A single instance of this class.
     */
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    } // end get_instance;

    /**
     * Build the meta query search for properties 
     * @param integer $postsPerPage // number of posts to bring in per page
     * @param boolean $paged // check if they want pagination
     * @param string $status // status of the post
     * @return array // completed WP_Query argument array
     */
    public function buildArguments( $postsPerPage = -1, $paged = false, $status = 'publish' ) 
    {

        rentPress_SlackBot::send_deprecation_message('buildArguments', 'rentPress_Search_Properties');
        
        $arguments = [
            'post_type' => 'properties',
            'post_status' => $status,
            'posts_per_page' => $postsPerPage,
        ];
        if ( $paged ) $arguments['paged'] = $paged;
        else $arguments['nopaging'] = true;

        $this->defaultQueries();

        return $this->respondWithArgs($arguments);
    } 

    public function defaultQueries()
    {
        if ( $this->getConfig('sqftg') ) $this->queryBySqftg();
        if ( $this->getConfig('rent') ) $this->queryByRent();
        if ( $this->getConfig('beds') ) $this->queryByBeds();
        if ( $this->getConfig('baths') ) $this->queryByBaths();
    }

    public function queryBySqftg()
    {
        if ( $this->isBeingRequested('propMinSQFT') ) {
            $metaQuery = $this->constructMetaQuery('propMinSQFT', $this->userArgs['propMinSQFT'], '>=', 'numeric');
            $this->addMetaQuery($metaQuery);
            $metaQuery = $this->constructMetaQuery('propMaxSQFT', $this->userArgs['propMinSQFT'], '>=', 'numeric');
            $this->addMetaQuery($metaQuery);
        }
    }

    public function queryByRent()
    {
        // If user queries with maximum price, add the meta query
        if ( $this->isBeingRequested('propMaxRent') ) {
            $rentQueries = [];
            $rentQueries[] = $this->constructMetaQuery('propMaxRent', $this->userArgs['propMaxRent'], '<=');
            $rentQueries[] = $this->constructMetaQuery('propMinRent', $this->userArgs['propMaxRent'], '<=');
            $this->addRentMetaQuery($rentQueries);
        }

        // If user queries with maximum price, add the meta query
        if ( ! $this->isBeingRequested('propMaxRent') && $this->isBeingRequested('propMinRent') ) {
            $rentQueries = [];
            $rentQueries[] = $this->constructMetaQuery('propMinRent', $this->userArgs['propMinRent'], '>=');
            $this->addRentMetaQuery($rentQueries);
        }
    }

    public function queryByBeds()
    {
        // If user queries with min beds, add the meta query
        if ( $this->isBeingRequested('propMinBeds') && intval($this->userArgs['propMinBeds']) <= 1 ) {
            $metaQuery = $this->constructMetaQuery('propMinBeds', $this->userArgs['propMinBeds'], '='); 
            $this->addMetaQuery($metaQuery);
        }
        if ( $this->isBeingRequested('propMinBeds') && intval($this->userArgs['propMinBeds']) > 1 ) {
            $metaQuery = $this->constructMetaQuery('propMaxBeds', $this->userArgs['propMinBeds'], '>='); 
            $this->addMetaQuery($metaQuery);
        }
    }

    public function queryByBaths()
    {
        // If user queries with min baths, add the meta query
        if ( $this->isBeingRequested('propMaxBaths') && intval($this->userArgs['propMaxBaths']) <= 1 ) {
            $metaQuery = $this->constructMetaQuery('propMaxBaths', $this->userArgs['propMaxBaths'], '='); 
            $this->addMetaQuery($metaQuery);
        }
        // If user queries with min baths, add the meta query
        if ( $this->isBeingRequested('propMaxBaths') && intval($this->userArgs['propMaxBaths']) >= 2 ) {
            $metaQuery = $this->constructMetaQuery('propMaxBaths', $this->userArgs['propMaxBaths'], '<='); 
            $this->addMetaQuery($metaQuery);
        }
    }

    /**
     * Gets the value of config.
     *
     * @return mixed
     */
    public function getConfig($key)
    {
        $configKeys = array_keys($this->config);
        if ( ! in_array($key, $configKeys) ) {
            wp_die('Please provide a valid config key.');
        }
        return $this->config[$key];
    }

    /**
     * Sets the value of config.
     *
     * @param mixed $config the config
     *
     * @return self
     */
    public function setConfig($key, $config)
    {
        $configKeys = array_keys($this->config);
        if ( ! in_array($key, $configKeys) ) {
            wp_die('Please provide a valid config key.');
        }
        $this->config[$key] = $config;
        return $this;
    }
}
