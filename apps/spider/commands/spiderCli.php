<?php
class spiderCli extends backgroundCli{
	
	function actionIndex($target,$limit = 0){

		$nodes = new spiderNode($target);
		
		$this->queue()->init();
		
		$page = 1;
		$max_break_num = 10;
		
		$break = 0;
		do{
			$url = $nodes->getListUrl($page);
			echo "flash page $page : $url\r\n";
			if($nodes->clawler($url)){
					
				if($this->queue()->isExist(array('url'=>$url,'target'=>$target))){
					continue 1;
				}

				$data = array(
					'type' => 'list',
					'target' => $target,
					'url' => $url,
					'status' => 0,
					'extents' => '',
					'dateline' => TIMESTAMP
				);
				$this->queue()->put($data);
			}
			
			$page++;
			
			usleep(1000);
			
		}while(!$limit || $page <= $limit);
	}
}