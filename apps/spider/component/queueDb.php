<?php
class queueDb implements iList {
	
	function init(){
		urlQueue::model()->buildTable();

		if(!$this->count()){
			urlQueue::model()->truncate();
		}		
	}
	
	function isExist($where){
		if(urlQueue::model()->count($where) > 0){
			return true;
		}
		
		return false;
	}
	
	function put($data){
		if(($qid = urlQueue::model()->add($data)) > 0)
			return true;
		
		return false;
	}
	
	function flush(){
		$data = urlQueue::model()->flush();
		if(!empty($data))
			return $data;
		
		return false;
	}
	
	function count(){
		return urlQueue::model()->count();
	}
	
	function remove($pos){
		if(urlQueue::model()->remove(array('qid' => $pos)))
			return true;
		
		return false;
	}
}