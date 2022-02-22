<?php 
/**
* Logs RentPress Events, Errors, and General Process Information
*/
class rentPress_Logging_Log extends rentPress_Base_Logger
{
	public function event($message) 
	{
		$this->log($message, 'event');
	}

	public function info($message) 
	{
		$this->log($message, 'info');
	}

	public function error($message) 
	{
		$this->log($message, 'error');
	}

	public function warning($message) 
	{
		$this->log($message, 'warning');
	}
	
}