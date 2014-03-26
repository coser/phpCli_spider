<?php
class spiderCrawler {
	
	public $target;
	public $config;
	public $resouce;
	public $charset = 'utf-8';
	public $htmlresult = null;
	public $curl_info = array();
	
	function __construct($target){
		$config = Config::getConfig('node',$target);

		if(empty($config)){
			throw new Exception('no target found');
		}
		
		$this->setCharset($config['charset']);
		
		$this->config = $config;
		$this->target = $target;

		urlResouce::model()->buildTable();
	}
	
	function getConfig($key){
		return isset($this->config[$key]) ? $this->config[$key] : null;
	}
	
	function getCurl($url){
		return new Curl($url);
	}
	
	function setCharset($charset){
		$this->charset = $charset;
	}
	
	function clawler($url,$method = 'get'){
		$this->clear();
		
		$resouceInfo = urlResouce::model()->getInfo(array('url'=>$url));
		if(empty($resouceInfo) || !$resouceInfo['download']){

			$curl = $this->getCurl($url);
			$res = $curl->$method();
			$this->curl_info = $curl->getHttpInfo();
			
			if(!$res){
				return false;
			}
			
			if($this->charset !== 'utf-8'){
				$res = iconv($this->charset,'utf-8',$res);
			}
			
			$this->setHtmlResult($res);
			unset($res);

			$attachment = $this->saveUrl($url);
			if(!$attachment){
				return false;
			}

			if(empty($resouceInfo)){
				$resouceInfo = array(
					'target' => $this->target,
					'url' => $url,
					'filepath' => $attachment,
					'download' => 1,
					'status' => 0,
					'dateline' => time()
				);
				
				$resouceInfo['id'] = urlResouce::model()->addResouce($resouceInfo);

			}else{
				urlResouce::model()->updateResouce(array('download'=>1),array('id' =>$resouceInfo['id'] ));
			}

		}else{
			if(!is_file(ROOT_PRO_PATH.'runtime'.DIRECTORY_SEPARATOR."html/".$resouceInfo['filepath'])){
				urlResouce::model()->updateResouce(array('download'=>0),array('id' =>$resouceInfo['id'] ));
				return false;
			}
	
			$data = file_get_contents(ROOT_PRO_PATH.'runtime'.DIRECTORY_SEPARATOR."html/".$resouceInfo['filepath']);
			$this->setHtmlResult($data);
		}

		$this->resouce = $resouceInfo;
		return true;
	}
	
	function saveUrl($url){
		static $file = null;
		$arr = pathinfo($url);
		
		if(!$file){
			$file = new FileManage('html');
		}
		return $file->saveFile($this->target,$arr['filename'].'.html',$this->getHtmlResult());
	}	

	function setHtmlResult($data){
		$this->htmlresult = $data;
	}
	
	function getHtmlResult($cut = false){
		$htmlresult = $this->htmlresult;
	
		if($cut && isset($this->config['html_area'])){
			$pattern = preg_quote($this->config['html_area']);
			$pattern = str_replace('\{\*\}','(.*)',$pattern);
			preg_match('~'.$pattern.'~is',$htmlresult,$preg_rs);
			$htmlresult = isset($preg_rs[1]) ? $preg_rs[1] : '';
		}
		return $htmlresult;
	}
	
	function clear(){
		$this->htmlresult = null;
	}
	
}