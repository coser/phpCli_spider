<?php
class coserArt extends BaseDb
{
	
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
	
	function tablename(){
		return 'coser_art';
	}
	
	public function buildWhere($where = array()){
		$whereArr = array();
		if(isset($where['title'])){
			$whereArr[] = " title = '".$where['title']."' ";
		}
		return !empty($whereArr) ? ' WHERE '.join(' AND ',$whereArr ) : '';
	}
	
	public function count($where = array())
	{	
		$sql = "SELECT count(*) as count FROM ".$this->tablename().$this->buildWhere($where);
		$row = $this->fetch($sql);
		return $row['count'];
	}
	
	public function hasExist($where = array())
	{
		
		$sql = "SELECT count(*) as count FROM ".$this->tablename().$this->buildWhere($where);
		$row = $this->fetch($sql);

		return $row['count'];
	}

	public function getInfo($where = array())
	{	
		$sql = "SELECT * FROM ".$this->tablename().$this->buildWhere($where);
		return $this->fetch($sql);
	}
	
	public function addResouce($arr){
		return $this->insert($this->tablename(),$arr,true);
	}
	
	public function updateResouce($arr,$where){
		return $this->update($this->tablename(),$arr,$where);
	}

	public function deleteResouce($where){
		return $this->delete($this->tablename(),$where);
	}
}
?>