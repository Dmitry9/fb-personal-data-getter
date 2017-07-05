<?php

$loader = require __DIR__ . '/vendor/autoload.php';

use App\Config;
use App\User;
use App\FbConnector;

$br = "\n</br>";
$title = 'Error';
try {
	if ( !session_id() ) {
		session_start();
	}

	$oConfig = new Config();
	$title = $oConfig->get('app')['AppName'];

	$oFbConnector = new FbConnector($oConfig);

	if ( $oFbConnector->getAccessToken() ){

		$oFbConnector->checkAccessToken();
		$fbUserProfile = $oFbConnector->getFbUserData();

		$oUser = new User($oConfig);

		// Insert or update user data to the database
		$fbUserData = array(
			'oauth_provider'=> 'facebook',
			'oauth_uid'     => $fbUserProfile['id'],
			'first_name'    => $fbUserProfile['first_name'],
			'last_name'     => $fbUserProfile['last_name'],
			'email'         => $fbUserProfile['email'],
		);
		$userData = $oUser->checkUser($fbUserData);

		$logoutURL = $oFbConnector->getLogoutUrl();

		// Render facebook profile data
		if(!empty($userData)){
			$output  = '<h1>User Profile Details </h1>';
			$output .= $br . 'First name : ' . $userData['first_name'];
			$output .= $br . 'Last name : ' . $userData['last_name'];
			$output .= $br . 'Email : ' . $userData['email'];
			$output .= $br . $br . '<a href="'.$logoutURL.'">Logout from Facebook</a>';
		}else{
			throw new \Exception("Can't retrieve user data.");
		}

	}else{
		$loginURL = $oFbConnector->getLoginUrl();
		// Render facebook login button
		$output = '<a href="'.htmlspecialchars($loginURL).'"><img src="images/fblogin-btn.png"></a>';
	}

} catch (Exception $e) {
	$output = '<h3 style="color:red">Error happened.</h3>';
	$output .= $br . "Code: " . $e->getCode();
	$output .= $br . "Message: " . $e->getMessage();
	$output .= $br . '<a href="/" target="_blank">Back to index</a>';
}

require_once ('template.php');