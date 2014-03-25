<?php
class FileManage
{
	public $save_path;
	
	public function __construct($folder = 'tmp'){
		$this->setFilePath($folder);
	}
	
	public function setFilePath($folder){
		
		$file_path = ROOT_PRO_PATH.'runtime'.DIRECTORY_SEPARATOR.$folder."/";
		if(!is_dir($file_path) && !FileManage::makeDir($file_path)){
			throw new Exception("Can't create path '{$file_path}'.");
		}
		
		$this->save_path = $file_path;
	}
	
	public function getFilePath(){
		return $this->save_path;
	}
	
    public function saveFile($type = 'temp', $forcename = '', $content = '' ,$savetype = 0)
    {                                       

        $attachdir = $this->getTargetDir($type, $savetype);
		$filename = $forcename ? $forcename :  $this->getFilename($type, $forcename);
        $attachment = $attachdir . $filename;
        $target = $this->getFilePath() . $attachment;

        if (!file_put_contents($target, $content)) {
            return false;
        }
		
        return $attachment;
    }

    function getFilename($type, $forcename = '')
    {
		
        $filename = date('His') . strtolower($this->random(16));

        if ($forcename != '') {
            $filename .= "_$forcename";
        }
        return $filename;
    }

    function getTargetDir($type, $savetype = 0, $check = true)
    {

        $subdir = $subdir1 = $subdir2 = '';

        switch ($savetype) {
            case 1:
                break;
            case 2:
                break;
            default:
                $subdir1 = date('Ym');
                $subdir2 = date('d');

                $subdir = $subdir1 . '/' . $subdir2 . '/';
                break;
        }

        $check && $this->checkDir($type, $subdir1, $subdir2);

        return $type . '/' . $subdir;
    }

    function checkDir($type, $sub1 = '', $sub2 = '')
    {

        $basedir = $this->getFilePath();

        $typedir = $type ? ($basedir . '/' . $type) : '';
        $subdir1 = $type && $sub1 !== '' ? ($typedir . '/' . $sub1) : '';
        $subdir2 = $sub1 && $sub2 !== '' ? ($subdir1 . '/' . $sub2) : '';

        $res = $subdir2 ? is_dir($subdir2) : ($subdir1 ? is_dir($subdir1) : is_dir($typedir));
        if (!$res) {
            $res = $typedir && $this->makeDir($typedir);
            $res && $subdir1 && ($res = $this->makeDir($subdir1));
            $res && $subdir1 && $subdir2 && ($res = $this->makeDir($subdir2));
        }

        return $res;
    }

    function makeDir($dir, $index = true)
    {
        $res = true;
        if (!is_dir($dir)) {
            $res = @mkdir($dir, 0777);
            $index && @touch($dir . '/index.html');
        }
        return $res;
    }
	

	function clean()
	{
		$dir = $this->getFilePath();
		$dirhandle = dir($dir);
		while ($entry = $dirhandle->read()) {
			if (!in_array($entry, array('.', '..')) && $entry != 'index.html' && is_file($dir . '/' . $entry)) {
				@unlink($dir . '/' . $entry);
			}
		}
	}
}
