<?php
// Include FB config file
$loader = require __DIR__ . '/vendor/autoload.php';

use App\Config;
use App\FbConnector;

$br = "\n</br>";
$title = 'Error';
try {
	if ( !session_id() ) {
		session_start();
	}
	$oConfig = new Config();
	$oFbConnector = new FbConnector($oConfig);
	$oFbConnector->logOut();
	$output = '<h3 style="color:green">Success.</h3>';
	$title = $oConfig->get('app')['AppName'];
} catch (\Exception $e) {

	$output = '<h3 style="color:red">Error happened.</h3>';
	$output .= $br . "Code: " . $e->getCode();
	$output .= $br . "Message: " . $e->getMessage();
}
$output .= $br . '<a href="/" target="_blank">Back to index</a>';
require_once ('template.php');
