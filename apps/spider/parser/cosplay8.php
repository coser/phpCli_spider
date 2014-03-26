<?php
class cosplay8 extends parseRule{

	public $htmlDom;
	public $items = array();
	
	function htmlDom(){
		if(!$this->htmlDom){
			$simpleDom = new simpleHtmlExt();
			$this->htmlDom = $simpleDom->str_get_html($this->html_data);
		}
		return $this->htmlDom;
	}
	
	function check404(){
		return $this->html_data=='';
	}

	function getLinks(){

		preg_match_all('/<a\shref="(\/pic\/chinacos\/\d+\/\d+\/[^\'\">]*\.html)"[^>]+><img src=\'([^\']+)\'[^>]+alt=\'([^>]+)\'><\/a>/is',$this->html_data,$preg_rs);

		$links = array_unique($preg_rs[1]);
		$res = array();

		$g = array(1=>'url',2=>'coverurl',3=>'title');
		foreach ($preg_rs as $i=>$rows){

			if(!isset($g[$i])){
				continue;
			}
			$key = $g[$i];
			foreach ($rows as $k => $v) {
				$res[$k][$key] = $v;
			}
		}

		return $res;
	}
	
	function getTitle(){
		$title = $this->htmlDom()->find('.showtu', 0)->find('h1', 0)->innertext;
		return strip_tags($title);
	}
	
	function getPicurl(){
		$url = $this->htmlDom()->find('.tbox img',0)->src;
		
		return $url;
	}

	function getPicnum(){
		$ps = $this->htmlDom()->find('.tset li',0)->find('a', 0)->innertext;
		$num = 0;
		if(0 < preg_match('/共(\d+)页:*/i', $ps, $extmatches)){
		   $num = $extmatches[1];
		}

		return $num;
	}

	function getPages(){

		$pages = array();
		$ps = $this->htmlDom()->find('.tset li',0)->find('a', 0)->innertext;
		$num = 0;
		if(0 < preg_match('/共(\d+)页:*/i', $ps, $extmatches)){
		   $num = $extmatches[1];
		}

		if($num > 1){
			$href = $this->htmlDom()->find('.tset li',-1)->find('a', 0)->href;
			list($aid,$t) = explode('_', $href);

			$pages[] = $aid.'.html';
			for($i = 2; $i<=$num; $i++){
				$pages[] = $aid.'_'.$i.'.html';
			}
		}else{
			$pages[] = $aid.'.html';
		}

		return $pages;
	}	

}