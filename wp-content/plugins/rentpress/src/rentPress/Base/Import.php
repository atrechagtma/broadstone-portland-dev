<?php

/**
* Base class for RentPress Connect import
*/
abstract class rentPress_Base_Import extends rentPress_Base_Request
{
    protected $propertyCount;
    protected $isAutoRefresh;

	public function __construct()
	{
        parent::__construct();
        $this->propertyCount = 0;
        $this->isAutoRefresh = null;
	}

    abstract public function import();

	public function persist(rentPress_Base_Repository $repository, $data)
	{
		return $repository->persist($data);
	}

    /**
     * Sets the value of isAutoRefresh.
     *
     * @param mixed $isAutoRefresh the is refreshing now
     *
     * @return self
     */
    public function setIsAutoRefresh($isAutoRefresh)
    {
        $this->isAutoRefresh = $isAutoRefresh;
        return $this;
    }

    /**
     * Check if plugin is currently refreshing properties
     * @return boolean
     */
    public function isRefreshing()
    {
        return $this->isAutoRefresh;
    }

}
