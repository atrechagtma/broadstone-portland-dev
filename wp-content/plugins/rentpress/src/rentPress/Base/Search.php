<?php

/**
 * Custom WP Meta Search class 
 * Helps construct meta_query arrays for WP_Query calls
 */
abstract class rentPress_Base_Search {

    /**
     * WP Meta Query array 
     * @var array // two-dimensional
     */
    public $metaQueries = [];

    /**
     * WP Tax Query array
     * @var array // two-dimensional
     */
    public $taxQueries = [];

    /**
     * Array of user requested search arguments
     * @var array
     */ 
    public $userArgs;

    public function __construct($searchArgs = []) {
        $this->userArgs = $searchArgs;
    }

    /**
     * Function needed on every implementation
     * @param integer $postsPerPage // number of posts to bring in per page
     * @param boolean $paged // check if they want pagination
     * @param string $status // status of the post
     * @return array // completed WP_Query argument array
     */
    public abstract function buildArguments($postsPerPage = -1, $paged = true, $status = 'publish');

    /**
     * Construct meta query for WP_Query
     * @param $defaultArgs // current query args 
     * @param array $queries // array of meta queries
     * @param string $relation // query relationship ( AND, OR, etc )
     * @return array 
     */
    public function setMetaQuery($defaultArgs, $queries, $relation = 'AND') {
        $defaultArgs['meta_query'] = [
            'relation' => $relation
        ];
        // insert all meta queries
        foreach($queries as $metaQuery) :
            array_push($defaultArgs['meta_query'], $metaQuery);
        endforeach;
        return $defaultArgs;
    }

    /**
     * Construct tax query for WP_Query
     * @param $defaultArgs // current query args 
     * @param array $queries // array of meta queries
     * @param string $relation // query relationship ( AND, OR, etc )
     * @return array 
     */
    public function setTaxQuery($defaultArgs, $queries, $relation = 'AND') {
        $defaultArgs['tax_query'] = [
            'relation' => $relation
        ];
        // insert all meta queries
        foreach($queries as $taxQuery) :
            array_push($defaultArgs['tax_query'], $taxQuery);
        endforeach;
        return $defaultArgs;
    }

    /**
     * Get any meta queries that were constructed by the user's request
     * @return array
     */
    public function getMetaQueries() {
        return $this->metaQueries;
    }

    /**
     * Get any tax queries that were constructed by the user
     * @return array
     */
    public function getTaxQueries() {
        return $this->taxQueries;
    }

    /**
     * Add a meta query to the meta queries array
     * @param array $query // another meta query
     * @return array // newly constructed meta query array
     */
    public function addMetaQuery(array $query) {
        array_push($this->metaQueries, $query);
        return $this;
    }

    /**
     * Add meta query that handles looking for options between the rent ranges provided
     * @param array  $metaQueries  [Array of meta queries]
     * @param string $relation     [Relationship between the provided meta queries]
     */
    public function addRentMetaQuery($metaQueries, $relation = 'OR') 
    {
        $rentMetaQuery = ['relation' => $relation];
        foreach ($metaQueries as $query) array_push($rentMetaQuery, $query);
        array_push($this->metaQueries, $rentMetaQuery);
        return $this;
    }

    /**
     * Add tax query to the tax queries array
     * @param array $query // another tax query
     * @return array // new full tax query
     */
    public function addTaxQuery(array $query) {
        array_push($this->taxQueries, $query);
        return $this;
    }

    /**
     * Construct your own custom meta query 
     * @param string $key // meta key
     * @param mixed $value // meta val
     * @param string $comparison // comparison operator 
     * @return array
     */
    public function constructMetaQuery($key = null, $value = null, $comparison = '=') {
        return [
            'key' => $key,
            'value' => $value,
            'compare' => $comparison
        ];
    }

    /**
     * Construct your own custom tax query
     * @param string $taxonomy // name of the taxonomy you wish to query with
     * @param string $field // field of post to target
     * @param mixed $terms // Array or string value. These are the values to search against
     * @return array
     */
    public function constructTaxQuery($taxonomy = 'property_relationship', $terms = [], $field = 'slug', $operator = 'IN') {
        return [
            'taxonomy' => $taxonomy,
            'field' => $field,
            'terms' => $terms,
            'operator' => $operator
        ];
    }

    /**
     * Construct tax query for pet taxonomy
     * @param  string $taxonomy [Taxonomy name]
     * @param  array  $terms    [Pet terms to search with]
     * @param  string $field    [Field of taxonomy to check against]
     * @return array            [Taxonomy array item]
     */
    public function constructPetTaxQuery($taxonomy = 'prop_pet_restrictions', $terms = [], $field = 'slug')
    {
        $termList = ['Dog', 'Cat'];
        if ( count($terms) > 0 ) $termList = array_merge($termList, $terms);
        return [
            'taxonomy' => $taxonomy,
            'field' => 'slug',
            'terms' => $termList
        ];
    }

    /**
     * Indicates user is requesting a search that is paginated
     * @return BOOLEAN 
     */
    public function rentPress_isPaged($paged) {
        if($paged) return true;
        return false;
    }

    /**
     * Get current page for pagination 
     * @return integer // page #
     */
    public function rentPress_getPaged() {
        return $page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
    }

    /**
     * Check if certain key is being requested
     * @param string $key // key of element to check for in user args
     * @return BOOLEAN
     */
    public function isBeingRequested($key) {
        if ( 
            isset($this->userArgs[$key])
            && $this->userArgs[$key] != ''
            && $this->userArgs[$key] != 'default'
            && $this->userArgs[$key] != 'null'
            && !empty($this->userArgs[$key])
        ) return true;
        return false;
    }

    public function respondWithArgs($defaultArgs, $metaQueryRelation = 'AND', $taxQueryRelation = 'AND') {
        // Get meta queries
        $metaQueries = $this->getMetaQueries();
        // Set meta query if there are queries requested
        if ( count($metaQueries) > 0 ) $defaultArgs = $this->setMetaQuery($defaultArgs, $metaQueries, $taxQueryRelation);

        // Get tax queries
        $taxQueries = $this->getTaxQueries();
        // Set meta query if there are queries requested
        if ( count($taxQueries) > 0 ) $defaultArgs = $this->setTaxQuery($defaultArgs, $taxQueries, $taxQueryRelation);

        return $defaultArgs;
    }

    /**
     * Sets the Array of user requested search arguments.
     * @param array $userArgs the user args
     * @return self
     */
    public function setSearchArgs($userArgs)
    {
        $this->userArgs = $userArgs;
        return $this;
    }
}
