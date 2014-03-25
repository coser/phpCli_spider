<?php
class urlQueue extends BaseDb
{
	
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
	
	function buildTable(){
		$table = $this->tablename();
		$sql = "
			CREATE TABLE IF NOT EXISTS `{$table}` (
			  `qid` bigint(30) unsigned NOT NULL AUTO_INCREMENT,
			  `type` varchar(10) NOT NULL,
			  `url` varchar(255) NOT NULL,
			  `target` varchar(50) NOT NULL,
			  `extents` TINYTEXT NOT NULL,
			  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`qid`)
			) ENGINE= MYISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		return $this->query($sql);
	}
	
	function truncate(){
		$this->query('TRUNCATE TABLE url_queue');
	}
	
	function tablename(){
		return 'url_queue';
	}
	
	function prev_table(){
		return 'url_queue_'.date("y_m",strtotime("-1 month"));
	}
	
	public function buildWhere($where = array()){
		$whereArr = array();
		
		if(isset($where['qid'])){
			$whereArr[] = " qid = '".$where['qid']."' ";
		}
		
		if(isset($where['url'])){
			$whereArr[] = " url = '".$where['url']."' ";
		}		
		
		if(isset($where['target'])){
			$whereArr[] = " target = '".$where['target']."' ";
		}	
		
		return !empty($whereArr) ? ' WHERE '.join(' AND ',$whereArr ) : '';
	}
	
	public function count($where = array())
	{	
		$sql = "SELECT count(qid) as count FROM ".$this->tablename().$this->buildWhere($where);
		$row = $this->fetch($sql);
		return $row['count'];
	}
	
	public function flush(){
		$this->tranStart();
		$this->query("UPDATE ".$this->tablename()." as a, (SELECT qid FROM ".$this->tablename()." WHERE status='0' ORDER BY qid ASC LIMIT 1  for update) tmp SET status='1' WHERE a.qid=LAST_INSERT_ID(tmp.qid)");
		$lastId = $this->lastId();
		$this->commit();
		$this->tranEnd();
		
		$sql = "SELECT * FROM ".$this->tablename() .$this->buildWhere(array('qid' => $lastId));
		$res = $this->fetch($sql);
		
		!empty($res) && $this->delete($this->tablename(),array('qid' => $lastId));
		return $res;
	}
	
	public function add($arr){
		return $this->insert($this->tablename(),$arr,true,true);
	}

	public function remove($where){
		return $this->delete($this->tablename(),$where);
	}
}
?>