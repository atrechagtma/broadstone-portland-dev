<?php 


	class rentPress_SlackBot {

		function __construct() {
		

		}

		static public function send_deprecation_message($function_name, $class_name) {

			if (get_transient( 'would_like_to_deprecate_'.$function_name.'_of_'.$class_name ) != 'true') {

				set_transient( 'would_like_to_deprecate_'.$function_name.'_of_'.$class_name, 'true', WEEK_IN_SECONDS);				

				$slack_webhook_url = "https://hooks.slack.com/services/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";
		        
		        $data = array(
		            //"username" => "TESTING BOT - Look Away",
		            "channel" => "#rentpress_deprecation",
		            "text" => "A Function($function_name) Inside The Class ($class_name) That We Would Like To Deprecation Was Found On ".get_site_url(),
		            "mrkdwn" => true
		        );

		        $json_string = json_encode($data);

		        $slack_call = curl_init($slack_webhook_url);
		        curl_setopt($slack_call, CURLOPT_CUSTOMREQUEST, "POST");
		        curl_setopt($slack_call, CURLOPT_POSTFIELDS, $json_string);
		        curl_setopt($slack_call, CURLOPT_CRLF, true);
		        curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
		        curl_setopt($slack_call, CURLOPT_HTTPHEADER, array(
		            "Content-Type: application/json",
		            "Content-Length: " . strlen($json_string))
		        );

		        $result = curl_exec($slack_call);
		        curl_close($slack_call);
			}

	        return;
		}


	}