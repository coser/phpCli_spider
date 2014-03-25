<?php
class CacheFile
{
	public $cache_path;
	
	public function __construct($cache_folder = 'cache'){
		$this->setCachePath($cache_folder);
	}
	
	public function setCachePath($folder){
		
		$cache_path = ROOT_PRO_PATH.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.$folder."/";
		if(!is_dir($cache_path) && !CacheFile::createDir($cache_path)){
			throw new Exception("Can't create cache_path '{$cache_path}'.");
		}
		
		$this->cache_path = $cache_path;
	}
	
	public function getCachePath(){
		return $this->cache_path;
	}
	
	function get($script, $prefix = 'cache_'){
		$data = array();
		$dir = $this->getCachePath();
		if(is_file("$dir$prefix$script.php")){
			include("$dir$prefix$script.php");
		}
		
		return $data;
	}
	
	function write($script, $data, $prefix = 'cache_') {
		$dir = $this->getCachePath();
		
		$cachedata = $this->getcachevars(array('data'=>$data));
		
		if($fp = @fopen("$dir$prefix$script.php", 'wb')) {
			fwrite($fp, "<?php\n\n$cachedata?>");
			fclose($fp);
		} else {
			throw new Exception('Can not write to cache files.');
		}
	}
	
	function clean()
	{
		$dir = $this->getCachePath();
		$dirhandle = dir($dir);
		while ($entry = $dirhandle->read()) {
			if (!in_array($entry, array('.', '..')) && $entry != 'index.html' && is_file($dir . '/' . $entry)) {
				@unlink($dir . '/' . $entry);
			}
		}
	}
	
	function createDir( $dirName, $mode = FILE_DEFAULT_DIRECTORY_CREATION_MODE )
	{
		if(CacheFile::exists($dirName)) return true;

		if(substr($dirName, strlen($dirName)-1) == "/" ){
			$dirName = substr($dirName, 0,strlen($dirName)-1);
		}

		$firstPart = substr($dirName,0,strrpos($dirName, "/" ));           

		if(file_exists($firstPart)){
			if(!mkdir($dirName,$mode)) return false;
			chmod( $dirName, $mode );
		} else {
			CacheFile::createDir($firstPart,$mode);
			if(!mkdir($dirName,$mode)) return false;
			chmod( $dirName, $mode );
	   }
		
		return true;
	}	
	
	function getcachevars($data, $type = 'VAR') {
		$evaluate = '';
		foreach($data as $key => $val) {
			if(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
				continue;
			}
			if(is_array($val)) {
				$evaluate .= "\$$key = ".$this->arrayeval($val).";\n";
			} else {
				$val = addcslashes($val, '\'\\');
				$evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
			}
		}
		return $evaluate;
	}
	
	function arrayeval($array, $level = 0) {
		if(!is_array($array)) {
			return "'".$array."'";
		}
		if(is_array($array) && function_exists('var_export')) {
			return var_export($array, true);
		}
	
		$space = '';
		for($i = 0; $i <= $level; $i++) {
			$space .= "\t";
		}
		$evaluate = "Array\n$space(\n";
		$comma = $space;
		if(is_array($array)) {
			foreach($array as $key => $val) {
				$key = is_string($key) ? '\''.addcslashes($key, '\'\\').'\'' : $key;
				$val = !is_array($val) && (!preg_match("/^\-?[1-9]\d*$/", $val) || strlen($val) > 12) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
				if(is_array($val)) {
					$evaluate .= "$comma$key => ".arrayeval($val, $level + 1);
				} else {
					$evaluate .= "$comma$key => $val";
				}
				$comma = ",\n$space";
			}
		}
		$evaluate .= "\n$space)";
		return $evaluate;
	}
	

}
