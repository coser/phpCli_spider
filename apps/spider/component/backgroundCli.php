<?php
class backgroundCli extends BaseCli{
	
	private $_pid = 0;
	private $_cache = null;
	private $_spider = null;
	private $_queue = null;
	
	private $_pid_filename = 'spider_cron.pid';
	
	private $_cron_pid = null;
	
	function init(){
        $this->_pid = posix_getpid();
		$this->_cron_start();
	}
	
	function queue(){
		if(!$this->_queue){
			$this->_queue = new queueDb();
		}

		return $this->_queue;
	}
	
	function isStop(){
		$filepath = $this->getPidFile();
		if(is_file($filepath)){
			return false;
		}
		
		return true;
	}
	
    function _cron_start()
    {
        @ob_end_clean();
        $this->_cron_pid = ignore_user_abort(true);
        set_time_limit(0);

		$filepath = $this->getPidFile();
		file_put_contents($filepath,0);
		
        register_shutdown_function(array($this, 'afterStop'));
    }
	
    public function _cron_close()
    {
        ignore_user_abort($this->_cron_pid);
        set_time_limit(ini_get('max_execution_time'));
	}
	
	function getPidFile(){
		return ROOT_PRO_PATH.'runtime'.DIRECTORY_SEPARATOR.$this->_pid_filename;
	}
	
	public function afterStop(){
		$this->_cron_close();
	}
    
	function showError($msg){
		$this->log($msg);
		exit(1);
	}
	
	function cache(){
		if(!$this->_cache){
			$this->_cache = new CacheFile();
		}
		return $this->_cache;
	}
	
	function log($msg){
		
	}
		
}