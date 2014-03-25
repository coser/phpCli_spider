<?php
class Uri
{
	private $_requestUri;
	private $_pathInfo;
	private $_scriptUrl;
	private $_baseUrl;
	
	public $urlSuffix='';
	
	public function getUri()
	{
		if($this->_requestUri===null)
		{
			if(isset($_SERVER['HTTP_X_REWRITE_URL'])) // IIS
				$this->_requestUri=$_SERVER['HTTP_X_REWRITE_URL'];
			else if(isset($_SERVER['REQUEST_URI']))
			{
				$this->_requestUri=$_SERVER['REQUEST_URI'];
				if(!empty($_SERVER['HTTP_HOST']))
				{
					if(strpos($this->_requestUri,$_SERVER['HTTP_HOST'])!==false)
						$this->_requestUri=preg_replace('/^\w+:\/\/[^\/]+/','',$this->_requestUri);
				}
				else
					$this->_requestUri=preg_replace('/^(http|https):\/\/[^\/]+/i','',$this->_requestUri);
			}
			else if(isset($_SERVER['ORIG_PATH_INFO']))  // IIS 5.0 CGI
			{
				$this->_requestUri=$_SERVER['ORIG_PATH_INFO'];
				if(!empty($_SERVER['QUERY_STRING']))
					$this->_requestUri.='?'.$_SERVER['QUERY_STRING'];
			}
			else
				throw new Exception('CHttpRequest is unable to determine the request URI.');
		}
		return $this->_requestUri;
	}
	
	public function getScriptUrl()
	{
		if($this->_scriptUrl===null)
		{
			$scriptName=basename($_SERVER['SCRIPT_FILENAME']);
			if(basename($_SERVER['SCRIPT_NAME'])===$scriptName)
				$this->_scriptUrl=$_SERVER['SCRIPT_NAME'];
			else if(basename($_SERVER['PHP_SELF'])===$scriptName)
				$this->_scriptUrl=$_SERVER['PHP_SELF'];
			else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME'])===$scriptName)
				$this->_scriptUrl=$_SERVER['ORIG_SCRIPT_NAME'];
			else if(($pos=strpos($_SERVER['PHP_SELF'],'/'.$scriptName))!==false)
				$this->_scriptUrl=substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$scriptName;
			else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT'])===0)
				$this->_scriptUrl=str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
			else
				throw new Exception('CHttpRequest is unable to determine the entry script URL.');
		}
		return $this->_scriptUrl;
	}	
	
	public function getBaseUrl()
	{
		if($this->_baseUrl===null)
			$this->_baseUrl=rtrim(dirname($this->getScriptUrl()),'\\/');
		return $this->_baseUrl;
	}
	
	public function getUrlReferrer()
	{
		return isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:null;
	}	
	
	public function getPathInfo()
	{
		if($this->_pathInfo===null)
		{
			$pathInfo=$this->getUri();

			if(($pos=strpos($pathInfo,'?'))!==false)
			   $pathInfo=substr($pathInfo,0,$pos);

			//$pathInfo=$this->decodePathInfo($pathInfo);

			$scriptUrl=$this->getScriptUrl();
			$baseUrl=$this->getBaseUrl();
			if(strpos($pathInfo,$scriptUrl)===0)
				$pathInfo=substr($pathInfo,strlen($scriptUrl));
			else if($baseUrl==='' || strpos($pathInfo,$baseUrl)===0)
				$pathInfo=substr($pathInfo,strlen($baseUrl));
			else if(strpos($_SERVER['PHP_SELF'],$scriptUrl)===0)
				$pathInfo=substr($_SERVER['PHP_SELF'],strlen($scriptUrl));
			else
				throw new Exception('CHttpRequest is unable to determine the path info of the request.');

			$this->_pathInfo=trim($pathInfo,'/');
		}
		return $this->_pathInfo;
	}
	
	public function removeUrlSuffix($pathInfo,$urlSuffix)
	{
		if($urlSuffix!=='' && substr($pathInfo,-strlen($urlSuffix))===$urlSuffix)
			return substr($pathInfo,0,-strlen($urlSuffix));
		else
			return $pathInfo;
	}
	
	public function parseUrl()
	{
		$rawPathInfo=$this->getPathInfo();
		return $this->removeUrlSuffix($rawPathInfo,$this->urlSuffix);
	}
	
	public function parsePathInfo($pathInfo)
	{
		if($pathInfo==='')
			return;
		$segs=explode('/',$pathInfo.'/');
		$n=count($segs);
		for($i=0;$i<$n-1;$i+=2)
		{
			$key=$segs[$i];
			if($key==='') continue;
			$_REQUEST[$key]=$_GET[$key]=$value;
		}
	}	
}
