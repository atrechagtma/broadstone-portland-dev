<?php 

/**
* Custom response class used to make a standardized response for any method success or failure
* which will create a predictable response environment; therefore making it easier to check for errors
*/
class rentPress_Helpers_Responder
{
	public function success($message, $meta = [])
	{
		return $this->respondSuccess($message, 200, $meta);
	}

	public function insufficientData($message, $meta = [])
	{
		return $this->respondError('Insufficient Data', $message, 422, $meta);
	}

	public function error($message, $code = 500, $meta = [])
	{
		return $this->respond('Something went wrong!', $message, $code, $meta);
	}

	private function respondSuccess($message, $code, $meta = [])
	{
		$response = new stdClass;
		$response->success = (object) [
			'message' => $message, 
			'code' => $code
		];
		if ( is_array($meta) && count($meta) > 0 ) 
			$response->meta = (object) ['meta' => $meta];
		return $response;
	}

	private function respondError($reason, $message, $code, $meta = [])
	{
		$response = new stdClass;
		$response->error = (object) [
			'reason' => $reason,
			'message' => $message, 
			'code' => $code
		];
		if ( is_array($meta) && count($meta) > 0 ) 
			$response->meta = (object) ['meta' => $meta];
		return $response;
	}
}