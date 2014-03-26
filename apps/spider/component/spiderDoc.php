<?php
class spiderDoc extends spiderCrawler{
	
	public $parser;
	public $resouce;
	
	function parser(){

		$classname = $this->getConfig('parser');
		if(!is_file(ROOT_PRO_PATH.DIRECTORY_SEPARATOR.'parser'.DIRECTORY_SEPARATOR.$classname.'.php')){
			throw new Exception('no parser found');	
		}
		
		include_once(ROOT_PRO_PATH.DIRECTORY_SEPARATOR.'parser'.DIRECTORY_SEPARATOR.$classname.'.php');
		$this->parser = new $classname();
		$this->parser->loadData($this->getHtmlResult());

		return $this->parser;
	}
	
	function getItems($keys){
		
		$items = array();
		
		if(!$this->parser()->check404()){
			$items = $this->parser()->getParseData($keys);
		}

		return $items;
	}

	function autoLink($relative,$referer){

		$pos = strpos($relative,'#');
		if($pos >0)
			$relative = substr($relative,0,$pos);

		if(preg_match('~^http://~i',$relative))
			return $relative;

		preg_match("~((http)://([^/]*)(.*/))([^/#]*)~i", $referer, $preg_rs);
		$parentdir = $preg_rs[1];
		$petrol = $preg_rs[2].'://';
		$host = $preg_rs[3];

		if(preg_match('~^/~i',$relative))
			return $petrol.$host.$relative;
		
		return $parentdir.$relative;
	}
	
	function clear(){
		$this->parser && $this->parser()->clear();
		$this->resouce = null;
	}
}