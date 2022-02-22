<?php 

/**
* Construct notifications
*/
class rentPress_Notifications_Notification
{
	protected $type;

	public function __construct($type = 'success')
	{
		$this->type = $type;
		$this->log = new rentPress_Logging_Log();
	}

	public function successResponse($message)
	{
		if ( is_admin() || is_super_admin() ) {
			return $this->notification($message, 'success');
		}
	}

	public function errorResponse($message, $public = false, $kill = true)
	{
		if ( $public ) {
			echo $this->notification($message, 'error');
		} else if ( (is_admin() || is_super_admin()) ) {
			$this->log->error($message); // Log all the errors
			if ( $kill ) return $this->notification($message, 'error');
		}
	}

	public function notification($message, $type)
	{
		$background = $this->fetchNotificationBackgroundColor($type);
        return "<div style='background:{$background};padding:20px;color:white;'>".
            __($message, RENTPRESS_LANG_KEY)."</div>";
	}

	public function fetchNotificationBackgroundColor($type)
	{
		switch ( $type ) {
			case 'success': return '#0099cc';
			case 'error': return '#d32f2f';
			default: return '#efefef';
		}
	}

    /**
     * Sets the value of type.
     *
     * @param mixed $type the type
     *
     * @return self
     */
    protected function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}