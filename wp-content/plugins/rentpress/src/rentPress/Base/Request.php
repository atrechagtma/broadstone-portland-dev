<?php 
/**
* To allow RentPress to make requests to Top Line Connect
*
* This class contains methods that allow other classes within RentPress to make POST, and GET 
* requests to Top Line Connect to retrieve/store information. 
*
* @author Derek <derek@30lines.com>
*/

class rentPress_Base_Request
{
	public $log;
	public $options;
	private $serviceUrl;
	private $apiKey;
	private $apiUsername;
    protected $notifications;
    protected $successMessage;

	public function __construct()
	{
        $this->notifications = new rentPress_Notifications_Notification();
		$this->options = new rentPress_Options();
        $this->log = new rentPress_Logging_Log();
		$this->serviceUrl = defined('WP_RENTPRESS_ENV') && WP_RENTPRESS_ENV === 'local' ? 'http://192.168.33.10/api/v1' : 'https://toplineconnect.com/api/v1';
		$this->apiUsername = $this->options->getOption('api_username');
		$this->apiKey = $this->options->getOption('api_token');
		$this->urlOverride = null;
		$this->successMessage = null;
	}

	/**
	 * Send POST request
	 * @param  string $action     [TLC API URI]
	 * @param  array  $parameters [Feed specific parameters]
	 * @return JSON               [JSON Response of TLC properties]
	 */
	public function post($action, $parameters = [])
	{
		return $this->request('POST', $action, $parameters);
	}

	/**
	 * Send GET request
	 * @param  string $action     [TLC API URI]
	 * @param  array  $parameters [Feed specific parameters]
	 * @return JSON               [JSON Response of TLC properties]
	 */
	public function get($action, $parameters = [])
	{
		return $this->request('GET', $action, $parameters);
	}

    /**
     * Send JSON Request to RentPress
     *
     * @param string $action     // URI for target action
     * @param array  $parameters // Array of POST parameters to send through the request
     */
    private function request($method, $action, $parameters = array()) {
    	$request = isset($this->urlOverride) ? $this->urlOverride : $this->serviceUrl.$action;
        $this->log->info('Property request URL: '.$request);

        /* Send remote API request to RentPress */
        $results = wp_remote_request(
            $request, // build request url
            array(
                'method' => $method,
                'sslverify' => false,
                'body' => $parameters,
                'compress' => true,
                'headers' => array( /* set token and username */
                    'X-Topline-Token' => $this->apiKey,
                    'X-Topline-User' => $this->apiUsername
                ),
                'timeout' => 60
            )
        );

        /* Check for errors with wp_remote_request() */
        if ( is_wp_error( $results ) ) {
            $this->log->error('RentPress wp_remote_request(): Failed. Proceeding with another method.');
            // If that doesn't work, then we'll try file_get_contents
            $results = file_get_contents( $request );
            if ( false == $results ) {
                $this->log->error('RentPress file_get_contents(): Failed. Proceeding with another method.');
                // And if that doesn't work, then we'll try curl
                $results = $this->curl( $request, $parameters );
                if ( null == $results || ! $results ) {
                    $this->log->scream()->error('RentPress CURL: Failed. No more request options. Actual end result.'.json_encode($results));
                    return $results = (object) ['error' => 'Failed wp remote request, file_get_contents, and curl request.'];
                }
            } // end if
        } // end if

        /* Return body of results */
        return $results['body'];
    }

    /**
     * Creates curl request base off user parameters
     * @param  string $url
     * @param  array $request
     * @return RESPONSE
     */
    private function curl( $url, $request ) {
        $resCurl = curl_init( $url );

        curl_setopt( $resCurl, CURLOPT_HTTPHEADER,  array( 'Content-type: APPLICATION/JSON; CHARSET=UTF-8' ) );
        curl_setopt( $resCurl, CURLOPT_POSTFIELDS, $request );
        curl_setopt( $resCurl, CURLOPT_POST, true );
        curl_setopt( $resCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $resCurl, CURLOPT_USERAGENT, 'RentPress');
        curl_setopt( $resCurl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec( $resCurl );

        if( $result === false ) {
            $this->log->error('RentPress Error: cURL did not complete. Exact Result: '. json_encode($result));
        } else if( isset($result['response']) && $result['response']['error'] ) {
            $this->log->error(
                'RentPress Error: ' .
                $result['response']['error']['code'] .' - ' .
                $result['response']['error']['message'] .' Exact Request: '. json_encode($request)
            );
            $result = false;
        }

        curl_close( $resCurl );
        return $result;
    } // end curl

    /**
     * Sets the value of urlOverride.
     *
     * @param mixed $urlOverride the url override
     *
     * @return self
     */
    public function setUrlOverride($urlOverride)
    {
        $this->urlOverride = $urlOverride;
        return $this;
    }

    /**
     * Sets the value of apiKey.
     *
     * @param mixed $apiKey the api key
     *
     * @return self
     */
    private function _setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @param mixed $successMessage
     *
     * @return self
     */
    public function setSuccessMessage($successMessage)
    {
        $this->successMessage = $successMessage;
        return $this;
    }
}