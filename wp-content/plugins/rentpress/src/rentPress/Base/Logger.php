<?php 
/**
* Logs RentPress Events, Errors, and General Process Information
*/
abstract class rentPress_Base_Logger
{
    protected $logPath;
    protected $shouldScream;
    protected $acceptedTypes;

    public function __construct() { // Bootstrap defaults
        $this->logPath = RENTPRESS_PLUGIN_DIR . 'Log/';
        $this->shouldScream = false;
        $this->acceptedTypes = [
            'error', 'event', 'warning', 'info'
        ];
    }

    /**
     * Logs to rentPress_<type>.log in plugin Log directory
     * @param  string $message  // Provide message for the log
     * @param  string $type     // What type of log is this?
     * @return null
     */
    public function log($message, $type = 'info') {
        if ( ! in_array($type, $this->acceptedTypes) )
            wp_die('Please select from the following types: '.json_encode($this->acceptedTypes));
        $logfile = $this->fetchFromLogs('rentPress_'.strtolower($type).'.log');
        if (! file_exists($logfile) ) {
            $logfile = fopen($logfile, "w");
            $logheading = date('[d-M-Y H:i:s e] ') ."\n ============= RentPress ".ucwords($type)." Logs ============= \n";
            $logheading .= $message;
            fwrite($logfile, $logheading);
            fclose($logfile);
        }
        if ( $this->shouldScream ) {
            $type = strtoupper($type);
            $screamMessage = "!==================== IMPORTANT {$type} ====================!";
        }
        $message = 'Raw Response: ' . $message;
        $message = $this->shouldScream ? $screamMessage . "\n" . $message : $message;
        error_log("\n". date('[d-M-Y H:i:s e] ') . $message . "\n", 3, $logfile);
    }

    private function fetchFromLogs($filename) {
        return $this->logPath . $filename;
    }

    public function scream()
    {
    	$this->shouldScream = true;
    	return $this;
    }

}
