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
	
	function setSuccess($house_guid){
		urlResouce::model()->updateResouce(array('status' => 1,'house_guid'=>$house_guid),array('id' => $this->resouce['id']));
	}
	
	function setVoid(){
		urlResouce::model()->updateResouce(array('status' => 2),array('id' => $this->resouce['id']));
	}
	
	function clear(){
		$this->parser && $this->parser()->clear();
		$this->resouce = null;
	}
}