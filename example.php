<?php
define('php-steamlogin', true);
require('main.php');

$SteamLogin = new SteamLogin(array(
	'username' => '',
	'password' => '',
	'datapath' => dirname(__FILE__) //path to saving cache files
));

if($SteamLogin->success){
	$logindata = $SteamLogin->login();
	/*
	$logindata = array(
		'steamid' => 'xxx', //64-bit
		'sessionId' => 'xxx',
		'cookies' => 'xxx=xxx; yyy=yyy; '
	);
	*/
	
	//You can view steamcommunity.com from created session
	//$SteamLogin->view('http://steamcommunity.com/id/pandeu');
	
	//Sending tradeoffers: https://github.com/halipso/php-steam-tradeoffers
	
	if($SteamLogin->error != '') echo $SteamLogin->error;
}else{
	echo $SteamLogin->error;
}
?>
