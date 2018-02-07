<?php

use PhpPlatform\Mock\Config\MockSettings;

include_once dirname(__FILE__).'/../vendor/autoload.php';

// getallheaders method will not be present when running php from shell
// so mocking this function
if(!function_exists('getallheaders')){
	function getallheaders(){
		return [];
	}
}

// following $_SERVER paramaeters are prefilled to avoid notices in CI Environment
$_SERVER['REMOTE_ADDR'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/tests';
$_SERVER['PLATFORM_APPLICATION_PATH'] = '/tests';
$_SERVER['PLATFORM_SERVICE_CONSUMES'] = 'application/json';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';

// mock database
MockSettings::setSettings('php-platform/persist', 'connection-class', 'PhpPlatform\Tests\SearchQueryParser\MockDataBase');

// set date_default_timezone_set
date_default_timezone_set('Asia/Kolkata');