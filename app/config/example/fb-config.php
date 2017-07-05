<?php
$config = [
	'AppId'         => '', //Facebook App ID
	'AppSecret'     => '', //Facebook App Secret
	'RedirectionURL'   => '', //Callback URL. You may use your local sites
	'FbPermissions' => [],  //Optional permissions
	'GraphVersion' => 'v2.2',  //graph version
	'TokenName' => 'facebook_access_token',  //Name of token inside $_SESSION
];

return $config;

