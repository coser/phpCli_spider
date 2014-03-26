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
}