<?php

include(dirname(__FILE__).DIRECTORY_SEPARATOR.'simple_html_dom.php');

class simpleHtmlExt
{
    public function file_get_html($url){
        return file_get_html($url);
    }
	
    public function str_get_html($string){
        return str_get_html($string);
    }	
}