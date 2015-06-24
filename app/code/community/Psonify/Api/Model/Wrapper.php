<?php

/**
 * 
 */
class Psonify_Api_Model_Wrapper {

	protected $apiUrl;
	protected $endPoints = array(
		"product/view",
		"cart/add",
		"cart/remove",
		"cart/products",
		"cart/update",
		"search/add",
		"address/add",
		"payment/add",
		"discount/add",
		"order/add"
	);

	/**
	 * [__construct description]
	 * @param [type] $apiUrl [description]
	 */
	public function __construct($apiUrl) {
		$this->apiUrl = $apiUrl;
	}

	/**
	 * [setApiUrl description]
	 * @param [type] $apiUrl [description]
	 */
	public function setApiUrl($apiUrl) {
		$this->apiUrl = $apiUrl;
	}

	/**
	* CallApi
	*
	* @param    endPoint  $endPoint partial end point url
	* @param    params  $params to be passed to api
	* @return   array response from api or general error form this class
	*/
	public function callApi( $endPoint , array $params ) {

		/* check if end point is available prior to make request */
		if(!in_array($endPoint,$this->endPoints)) {
			if(Mage::getSingleton("core/session")->getPsonifyDebug() == 'true') {
				echo "<br/><h1>End Point</h1><br/>";
				echo $endPoint;
				echo "<br/><h1>Sent Params</h1><br/>";
				Zend_Debug::dump($params );
				echo "<br/><h1>Received Result</h1><br/>";
				Zend_Debug::Dump($this->getValidationErrorResponse('End point is not valid'));exit;
			}
			return $this->getValidationErrorResponse('End point is not valid');
		} else {
			$params["token"] = isset($params["token"]) ? $params["token"] : $params["data"]["token"];
			/* initializing curl */
			$ch = curl_init();
			$curlConfig = array(
				CURLOPT_URL            => $this->apiUrl.'/'.$endPoint,
				CURLOPT_POST           => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS     => json_encode($params),
			);
			curl_setopt_array($ch, $curlConfig);
			$result = curl_exec($ch);
			if(Mage::getSingleton("core/session")->getPsonifyDebug() == 'true') {
				echo "<br/><h1>End Point</h1><br/>";
				echo $endPoint;
				echo "<br/><h1>Sent Params</h1><br/>";
				Zend_Debug::dump($params );
				echo "<br/><h1>Recieved Result</h1><br/>";
				Zend_Debug::Dump($result);exit;
			}
			curl_close($ch);
			return json_decode($result);
		}
	}

	/**
	* return validation error
	*
	* @param    message to be filled in error
	* @return   array of validation error
	*/
	public function getValidationErrorResponse($message){
		return array(
			'code' => 0 ,
			'data' => array( 'message' => $message ),
		);
	}
}

?>