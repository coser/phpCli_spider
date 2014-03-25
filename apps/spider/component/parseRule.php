<?php
class parseRule {
	
	public $html_data = null;
	
	function loadData($htmldata){
		$this->html_data = $htmldata;
	}
	
	function getHtmlData($html){
		return $this->html_data;
	}
	
	function getParseData($arr){
		$data = array();
		
		foreach($arr as $mothed){
			$class = 'get'.ucfirst($mothed);
			if( method_exists($this,$class)){
				$data[$mothed] = $this->$class();
			}
		}
		
		return $data;
	}
	
	function strip_tags_content($text, $tags = '', $invert = FALSE) {
	
	  preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
	  $tags = array_unique($tags[1]);
	   
	  if(is_array($tags) AND count($tags) > 0) {
		if($invert == FALSE) {
		  return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
		}
		else {
		  return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
		}
	  }
	  elseif($invert == FALSE) {
		return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
	  }
	  return $text;
	}
	
	function clear(){
		$this->html_data = null;
	}
	
	function __destruct(){
		$this->clear();
	}
}