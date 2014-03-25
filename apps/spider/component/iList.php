<?php
interface iList {
	
	function init();
	
	function put($data);
	
	function flush();
	
	function count();
	
	function remove($pos);
	
}