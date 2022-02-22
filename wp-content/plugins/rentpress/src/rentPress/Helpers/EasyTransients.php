<?php 

/**
* Easily manage wordpress transients
*/
class rentPress_Helpers_EasyTransients
{
	protected $hours;

	public function __construct()
	{
		$this->hours = null;
	}

	public function setForAnHour($key, $value)
	{
		$this->setTransient($key, $value);
	}

	public function setForACoupleHours($key, $value)
	{
		$this->setTransient($key, $value, 2 * HOUR_IN_SECONDS);
	}

	public function setForThisManyHours($hours, $key, $value)
	{
		$this->setHours($hours)->setTransient($key, $value);
	}

	public function setTransient($key, $value, $expiration = HOUR_IN_SECONDS)
	{
		if ( isset($this->hours) ) 
			$expiration = $this->hours * HOUR_IN_SECONDS;
		set_transient($key, $value, $expiration);
	}

	public function getTransient($key)
	{
		return get_transient($key);
	}

	public function removeTransient($key)
	{
		delete_transient($key);
	}

    /**
     * Sets the value of hours.
     *
     * @param mixed $hours the hours
     *
     * @return self
     */
    protected function setHours($hours)
    {
        $this->hours = $hours;
        return $this;
    }
}