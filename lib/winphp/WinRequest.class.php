<?php
class WinRequest
{
	public $params = array();
	
	public function __construct(){
		
		if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
		{
			if(isset($_GET))
				$_GET=$this->stripSlashes($_GET);
			if(isset($_POST))
				$_POST=$this->stripSlashes($_POST);
			//if(isset($_REQUEST))
			//	$_REQUEST=$this->stripSlashes($_REQUEST);
			if(isset($_COOKIE))
				$_COOKIE=$this->stripSlashes($_COOKIE);
		}

		$request = array(
			'GET'	=> $_GET,
			'POST'  => $_POST
		);

		foreach ($request as $t =>$params) {
			
			if(!empty($params)){

				foreach($params as $k=> $v){
					if($t == 'GET'){
						$v = $this->urldecode($v);
					}
					
					if (is_scalar($v)) {
						$this->setParam($k,$v);
					}	
				}
			}
		}
		
		unset($request);
	}
	
	public function getHeaders() {
		$retarr = array();
		$headers = array();
		
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
		} else {
			$headers = array_merge($_ENV, $_SERVER);
			
			foreach ($headers as $key => $val) {
				//we need this header
				if (strpos(strtolower($key), 'content-type') !== FALSE)
					continue;
				if (strtoupper(substr($key, 0, 5)) != "HTTP_")
					unset($headers[$key]);
			}
		}

		foreach ($headers AS $key => $value) {
			$key = preg_replace('/^HTTP_/i', '', $key);
			$key = str_replace(
					" ",
					"-",
					strtolower(str_replace(array("-", "_"), " ", $key))
				);
			
			$retarr[$key] = $value;
		}
		//ksort($retarr);
		return $retarr;
	}
	
	function urlencode($s){
		if (is_array($s)) {
			return array_map(array($this, 'urlencode'), $s);
		} else if (is_scalar($s)) {
			return str_replace( '+', ' ', str_replace('%7E', '~', rawurlencode($s)) );
		} else {
			return '';
		}
	}
	
	function urldecode ( $s ){
		return urldecode($s);
	}	

	public function stripSlashes(&$data)
	{
		return is_array($data)?array_map(array($this,'stripSlashes'),$data):stripslashes($data);
	}

	public function getParams(){
		return $this->params;
	}
	
	public function getParam($name,$default = null){
		if (isset($this->params[$name])) {
			$s = $this->params[$name];
		} else if (isset($this->params[$this->urlencode($name)])) {
			$s = $this->params[$this->urlencode($name)];
		} else {
			$s = $default;
		}
		if (!empty($s)) {
			if (is_array($s)) {
				$s = array_map(array($this,'urldecode'), $s);
			} else {
				$s = $this->urldecode($s);
			}
		}

		return $s;
	}
	
	public function setParam($name, $value){
		$name_encoded = $this->urlencode($name);
		if (is_array($value)) {
			foreach ($value as $v) {
				$this->params[$name_encoded][] = $this->urlencode($v);
			}
		}else {
			$this->params[$name_encoded] = $this->urlencode($value);
		}
	}
	
	public function getPost($name, $default = null){
		if (isset($_POST[$name])) {
			$s = $_POST[$name];
		} else {
			$s = $default;
		}
		
		return $s;
	}
	
	public function getRequestType()
	{
		return strtoupper(isset($_SERVER['REQUEST_METHOD'])?$_SERVER['REQUEST_METHOD']:'GET');
	}

	public function isPostRequest()
	{
		return isset($_SERVER['REQUEST_METHOD']) && !strcasecmp($_SERVER['REQUEST_METHOD'],'POST');
	}	
	
	public static function isAjaxRequest()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
	}
}
?>