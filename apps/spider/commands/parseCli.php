<?php
class parseCli extends backgroundCli{
    
	function actionIndex(){

		$this->queue()->init();

		while(1){
			/*
			urlResouce::model()->buildTable();
			
			if($this->isStop()){
				break;
			}
			
			$q = $this->queue()->flush();

			if(empty($q)){
				sleep(5);
				continue;
			}
			*/
		$q['type'] = 'list';
		$q['target'] = 'cosplay8_chinacos';
		$q['url'] = 'http://www.cosplay8.com/pic/chinacos/list_22_1.html';
		
			if($q['type'] == 'list'){
				$this->doList($q);
			}elseif ($q['type'] == 'art'){
				$this->doArt($q);
			}
			exit;
			usleep(1000);			
		}
	}

	function doList($q){

		$doc = new spiderDoc($q['target']);

		if($doc->clawler($q['url']) === false){
			echo "skip ".$q['qid']." on doc ".$doc->resouce['id']." \r\n";
			
		}else{
			$item = array('links');
			$data = $doc->getItems($item);

			foreach($data['links'] as $r){
				$art = array();
				$art['title'] = $r['title'];
				$art['posttime'] = TIMESTAMP;
				$art['coverurl'] = 'http://www.cosplay8.com'.$r['coverurl'];
				$art['coser'] = '';
				
				if($artid = coserArt::model()->addResouce($art)){
					$art['aid'] = $artid;
					$d = array(
						'type' => 'art',
						'target' => 'cosplay8_chinacos',
						'url' => 'http://www.cosplay8.com'.$r['url'],
						'status' => 0,
						'extents'=> json_encode($art),
						'dateline' => TIMESTAMP
					);

					$this->queue()->put($d);
					echo "add queue url ".$r['url']." \r\n";
				}else{
					echo "[Error]add faild: url ".$r['url']." \r\n";
				}

				
			}


		}
	}

	function doArt($q){

		$q['target'] = 'cosplay8_chinacos';
		$q['url'] = 'http://www.cosplay8.com/pic/chinacos/2014/0212/57000.html';
		
		$target_arr = explode('_',$q['target']);
		
		$doc = new spiderDoc($q['target']);		

		if($doc->spider($q['url']) === false){
			$data = array(
				'target' => $q['target'],
				'url' => $q['url'],
				'status' => 0,
				'dateline' => TIMESTAMP
			);
			$this->queue()->put($data);
			echo "skip ".$q['qid']." on doc ".$doc->resouce['id']." \r\n";
			
		}else{
			$item = array('title','picnum','picurl');
			$data = $doc->getItems($item);

			if(empty($data)){
				echo "void ".$doc->resouce['id']."\r\n";
				$doc->setVoid();
			}else{
				
				if(isset($res['status']) && $res['status'] == '0000'){
					echo "parsed ".$doc->resouce['id']."\r\n";
					$doc->setSuccess($res['data']['house_guid']);
				}else{
					print_r($res);
				}
			}
		}
		exit;
		$doc->clear();
	}
}