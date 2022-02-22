<?php 

/**
 * Custom  RentPress Search Class
 * Extends RentPress's rentPress_WPSearch class to easily construct WP meta_query requests for searching properties 
 */
class rentPress_Search_FloorPlans extends rentPress_Base_Search {
    
    /**
     * Array of user requested search arguments
     * @var array
     */ 
    public $userArgs;

    /**
     * Build the meta query search for properties 
     * @param integer $postsPerPage // number of posts to bring in per page
     * @param boolean $paged // check if they want pagination
     * @param string $status // status of the post
     * @return array // completed WP_Query argument array
     */
    public function buildArguments( $postsPerPage = -1, $paged = true, $status = 'publish' ) 
    {
        rentPress_SlackBot::send_deprecation_message('buildArguments', 'rentPress_Search_FloorPlans');
        
        $arguments = [
            'post_type' => 'floorplans',
            'post_status' => $status,
            'posts_per_page' => $postsPerPage
        ];

        $this->defaultQueries();

        return $this->respondWithArgs($arguments);
    } 

    public function defaultQueries()
    {
        $this->queryByPropertyTaxonomy();
    }

    public function queryByPropertyTaxonomy()
    {
        // If user queries with min beds, add the meta query
        if ( $this->isBeingRequested('taxterms') ) {
            $taxQuery = $this->constructTaxQuery('property_relationship', [$this->userArgs['taxterms']]);
            $this->addTaxQuery($taxQuery);
        }
    }

}
