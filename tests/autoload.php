<?php

include_once dirname(__FILE__).'/../vendor/autoload.php';

// getallheaders method will not be present when running php from shell
// so mocking this function
if(!function_exists('getallheaders')){
	function getallheaders(){
		return [];
	}
}