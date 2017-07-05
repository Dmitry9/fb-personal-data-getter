<?php
$config = [
	'Host' => 'localhost',
	'UserName' => '',
	'Password' => '',
	'DbName' => 'test',
	'Table' => 'users', //Table for users
];
$config['Dsn'] = "mysql:host={$config['Host']};dbname={$config['DbName']}";
$config['Options'] = [
	PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
];
return $config;