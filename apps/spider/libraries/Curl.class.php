<?php
class Curl{
    
	private $ci;
	
    public $header = array();
	public $response = '';

	public $url;
	
	public $error_code;
	public $error_string;
	public $info; 	
	
	public $timeout = 30;

	public $useragent;
	public $options = array();
	
	public $all_useragents = array(
		"Mozilla/5.0 (iPad; CPU OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B329 Safari/8536.25",
		"Mozilla/5.0 (iPad; CPU OS 6_1_2 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) CriOS/25.0.1364.124 Mobile/10B146 Safari/8536.25",
		"Mozilla/5.0 (iPad; CPU OS 6_1_2 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B147 Safari/8536.25",
		"Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A403 Safari/8536.25",
		"Mozilla/5.0 (iPad; CPU OS 5_1_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Mobile/9B206",
		"Mozilla/5.0 (iPod; CPU iPhone OS 5_1_1 like Mac OS X; nl-nl) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/21.0.1180.80 Mobile/9B206 Safari/7534.48.3",
		"Mozilla/5.0 (iPhone; CPU iPhone OS 5_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9B176 Safari/7534.48.3",
		"Mozilla/5.0 (iPhone; CPU iPhone OS 5_0_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A405 Safari/7534.48.3",
		"Mozilla/5.0 (iPhone; CPU iPhone OS 5_0_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A405 Safari/7534.48.3",
		"Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_3 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5",
		"Mozilla/5.0 (iPhone; CPU iPhone OS 5_0_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A405 Safari/7534.48.3",
		
		"Mozilla/5.0 (iPad; U; CPU OS 4_3 like Mac OS X; de-de) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8F191 Safari/6533.18.5",
		"Mozilla/5.0 (iPad; U; CPU OS 4_3_1 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8G4 Safari/6533.18.5",
		"Mozilla/5.0 (iPad; U; CPU OS 4_2_1 like Mac OS X; en-us) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148 Safari/6533.18.5",
	);
	
	public $all_ips = array(
		'city' => array(58),
		'area' => array(
			58 => array(32,33,34,35)
		),
	);
	
    function __construct($url){
		$this->ci = curl_init($url);
		if(!$this->ci){
			throw new Exception('curl init error');
		}
    }
	
	function setHeaders($headers){
		$this->options[CURLOPT_HTTPHEADER] = array_merge($this->headers,$headers);
	}
	
	function setAgents($agent){
		$this->options[CURLOPT_USERAGENT] = $agent;
	}
	
	function setSSL(){
		$this->options[CURLOPT_SSL_VERIFYPEER] =  true;
		$this->options[CURLOPT_SSL_VERIFYHOST] = 1;		
	}
    
	function setProxy($proxy_ip,$proxy_port){
		
	}
	
	function setCookie($cookie_file = ''){
		if(!$cookie_file){
			$cookie_file = tempnam ("/tmp", "CURLCOOKIE");
		}
		$this->options[CURLOPT_COOKIEJAR] =  $cookie_file;
		$this->options[CURLOPT_COOKIEFILE] = $cookie_file;
	}
	
	function get() {
		return $this->execute();
	}

	function post($parameters = array()) {
		curl_setopt($this->ci, CURLOPT_POST, TRUE);
		if (!empty($parameters)) {
			$postfields = http_build_query($parameters);
			curl_setopt($this->ci, CURLOPT_POSTFIELDS, $postfields);
		}
		return $this->execute();
	}

	function execute() {
		
		if (!isset($this->options[CURLOPT_TIMEOUT])){
			$this->options[CURLOPT_TIMEOUT] = $this->timeout;
		}
		
		if (!isset($this->options[CURLOPT_RETURNTRANSFER])){
			$this->options[CURLOPT_RETURNTRANSFER] = TRUE;
		}
		
		if (!isset($this->options[CURLOPT_FAILONERROR])){
			$this->options[CURLOPT_FAILONERROR] = TRUE;
		}		

		if (!ini_get('safe_mode') && ! ini_get('open_basedir')){
			if (!isset($this->options[CURLOPT_FOLLOWLOCATION])){
				$this->options[CURLOPT_FOLLOWLOCATION] = TRUE;
			}
		}
		
		if (!isset($this->options[CURLOPT_USERAGENT])){
			$this->useragent = $this->all_useragents[array_rand($this->all_useragents)];
			$this->options[CURLOPT_USERAGENT] = $this->useragent;
		}

		$city = $this->all_ips['city'][array_rand($this->all_ips['city'])];
		$area = $this->all_ips['area'][$city][array_rand($this->all_ips['area'][$city])];
		
		$ip_arr = array(
			$city,$area,rand(0,255),rand(0,255)
		);
		
		$ip = join('.',$ip_arr);

		$headers = array(
			'CLIENT-IP' => $ip,
			'X-FORWARDED-FOR' => $ip, 
			'REMOTE_ADDR' => $ip
		);

		foreach( $headers as $n => $v ) {
			$this->options[CURLOPT_HTTPHEADER][] = $n .':' . $v;
		}

		//	CURLOPT_HEADER => FALSE,
		//	CURLINFO_HEADER_OUT => TRUE,

		curl_setopt_array($this->ci, $this->options);

		//curl_setopt($this->ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		
		$this->response = curl_exec($this->ci);

		$this->info = curl_getinfo($this->ci);

		if (!$this->response){
			$errno = curl_errno($this->ci);
			$error = curl_error($this->ci);
			
			curl_close($this->ci);
			$this->set_defaults();
			
			$this->error_code = $errno;
			$this->error_string = $error;

			return FALSE;
		}else{
		
			curl_close($this->ci);
			$this->last_response = $this->response;
			$this->set_defaults();

			return $this->last_response;
		}
	}
	
	function getHttpInfo(){
		return $this->info;
	}

	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
		}
		return strlen($header);
	}
	
	public function set_defaults(){
		$this->response = '';
		$this->headers = array();
		$this->options = array();
		$this->error_code = NULL;
		$this->error_string = '';
		$this->ci = NULL;
	}
}


?>