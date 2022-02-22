<?php

/**
* RentPress Caching Class
*
* This class makes use of the transients API in Wordpress to "cache" any property information.
* In production, this is mostly used to cache 3rd party API calls or property calculations that run on multiple pages
*/
class rentPress_Base_Caching {
  public $cacheKeys = [
    'propertyMinRent',
    'propertyMaxRent',
    'allProperties',
    'allFeaturedProperties',
    'propertyMaxBeds',
    'propertyMaxBaths',
    'propertyRentMin'
  ];

  protected $timeout = DAY_IN_SECONDS;

  public function setTimeout($timout) {
    $this->timeout = $timout;
    return $this;
  }

  public function getTimeout()
  {
    return $this->timeout;
  }

  public function cacheReset()
  {
    $cacheKeys = $this->cacheKeys;
    foreach ($cacheKeys as $key) $this->removeCache($key);
  }

  public function setCache($name, $data, $timeout = null) {
    // If timeout is provided, use that instead
    if ( isset($timeout) ) $this->setTimeout($timeout);
    set_transient($name, $data, $this->timeout);
  }

  public function getCache($name) {
    $cachedItem = get_transient($name);
    $exists = $this->cacheExists($name);
    if ( isset($exists['error']) ) return $exists;
    return $cachedItem;
  }

  public function removeCache($name) {
    $exists = $this->cacheExists($name);
    if ( isset($exists['error']) ) return $exists['error'];
    delete_transient($name);
  }

  public function cacheExists($name) {
    $cachedItem = get_transient($name);
    // If requested cache item does not exist, return error response
    if ( !$cachedItem ) return $this->cacheDoesNotExist();
    return $this->cacheDoesExist();
  }

  public function cacheDoesNotExist() {
    return [
      'error' => [
        'Cached item does not exist'
      ]
    ];
  }

  public function cacheDoesExist() {
    return [
      'success' => [
        'Cached item does exist'
      ]
    ];
  }

}
