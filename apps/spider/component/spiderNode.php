<?php
class spiderNode extends spiderCrawler{
	
	function getLimit(){
		return $this->getConfig('limit');
	}
	
	function getListUrl($page){
		$host = $this->getConfig('host');
		$page_prefix = $this->getConfig('page_prefix');
		$page_rule = $this->getConfig('page_rule');
		return str_replace(array('{host}','{page}'), array($host,$page_prefix.$page), $page_rule);
	}

	function autoLink($relative,$referer){

		$pos = strpos($relative,'#');
		if($pos >0)
			$relative = substr($relative,0,$pos);

		if(preg_match('/^http:///i',$relative))
			return $relative;
		
		preg_match("/(http://([^/]*)(.*/))([^/#]*)/i", $referer, $preg_rs);
		$parentdir = $preg_rs[1];
		$petrol = $preg_rs[2].'://';
		$host = $preg_rs[3];
		
		if(preg_match('/^//i',$relative))
			return $petrol.$host.$relative;
		
		return $parentdir.$relative;
	}
}