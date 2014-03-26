<?php
class parseCli extends backgroundCli{
    
	function actionIndex(){

		$this->queue()->init();

		while(1){	
			
			if($this->isStop()){
				break;
			}
			
			$q = $this->queue()->flush();

			if(empty($q)){
				echo "no queue.. \r\n";
				sleep(5);
				continue;
			}

			if(isset($q['extents'])){
				$q['extents'] = json_decode($q['extents'],true);
			}

			if($q['type'] == 'list'){
				$this->doList($q);
			}elseif ($q['type'] == 'pages'){
				$this->doPages($q);
			}elseif ($q['type'] == 'art'){
				$this->doArt($q);
			}

			usleep(1000);			
		}
	}

	function doList($q){

		$doc = new spiderDoc($q['target']);

		if($doc->clawler($q['url']) === false){
			echo "skip ".$q['url']." \r\n";
			
		}else{
			$item = array('links');
			$data = $doc->getItems($item);

			foreach($data['links'] as $r){

				if(coserArt::model()->hasExist(array('title'=>$r['title']))){
					continue;
				}

				$art = array();
				$art['title'] = $r['title'];
				$art['posttime'] = TIMESTAMP;

				$art['coverurl'] = $doc->autoLink($r['coverurl'],$q['url']);
				$art['coser'] = '';

				if($artid = coserArt::model()->addResouce($art)){
					$art['aid'] = $artid;
					$d = array(
						'type' => 'pages',
						'target' => $q['target'],
						'url' => $doc->autoLink($r['url'],$q['url']),
						'status' => 0,
						'extents'=> json_encode($art),
						'dateline' => TIMESTAMP
					);

					$this->queue()->put($d);
					echo "add queue url ".$d['url']." \r\n";
				}else{
					echo "[Error]add faild: url ".$r['url']." \r\n";
				}
			}
		}

		$doc->clear();
	}

	function doPages($q){
		$doc = new spiderDoc($q['target']);		
		$host = $doc->getConfig('host');

		if($doc->clawler($q['url']) === false){
			echo "skip ".$q['url']." \r\n";	
		}else{

			$item = array('pages');
			$data = $doc->getItems($item);

			foreach($data['pages'] as $url){
				$d = array(
					'type' => 'art',
					'target' => $q['target'],
					'url' => $doc->autoLink($url,$q['url']),
					'status' => 0,
					'extents'=> json_encode($q['extents']),
					'dateline' => TIMESTAMP
				);

				$this->queue()->put($d);
				echo "add queue url ".$d['url']." \r\n";
			}
		}

		$doc->clear();	
	}	

	function doArt($q){

		$doc = new spiderDoc($q['target']);		
		$host = $doc->getConfig('host');

		if($doc->clawler($q['url']) === false){
			echo "skip ".$q['url']." \r\n";
		}else{

			$item = array('picurl');
			$data = $doc->getItems($item);

			if(isset($data['picurl']) && $data['picurl']){
				$art = array();
				$art['aid'] = isset($q['extents']['aid']) ? $q['extents']['aid']: 0;
				$art['attach_url'] = $doc->autoLink($data['picurl'],$q['url']);
				$art['createtime'] = TIMESTAMP;

				if(!coserAttach::model()->hasExist(array('attach_url'=>$art['attach_url'],'aid'=>$art['aid']))){
					coserAttach::model()->addAttach($art);
					echo "add art: url ".$art['attach_url']." for aid ".$art['aid']." \r\n";
				}else{
					echo "add art skip: has exist url ".$art['attach_url']." for aid ".$art['aid']." \r\n";
				}
			}
		}

		$doc->clear();
	}
}