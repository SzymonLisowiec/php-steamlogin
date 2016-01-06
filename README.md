# php-steamlogin
Script to obtain steamid, cookies and sessionid.

<h1>Example</h1>
```php
<?php
define('php-steamlogin', true);
require('main.php');
$SteamLogin = new SteamLogin([
	'username' => '',
	'password' => '',
	'datapath' => dirname(__FILE__) //path to saving cache files
]);
if($SteamLogin->success){
	$logindata = $SteamLogin->login();
	/*
	$logindata = [
		'steamid' => 'xxx', //64-bit
		'sessionId' => 'xxx',
		'cookies' => 'xxx=xxx; yyy=yyy; '
	];
	*/
	
	//You can view steamcommunity.com from created session
	//$SteamLogin->view('http://steamcommunity.com/id/pandeu');
	
	if($SteamLogin->error != '') echo $SteamLogin->error;
}else{
	echo $SteamLogin->error;
}
?>
```

<h1>Public Parameters</h1>
<b>error</b> and <b>success</b>

<h1>Public Methods</h1>
- login([$authcode, $twofactorcode]) - login to steam
- view($url) - print page

<h1>Special thanks for</h1>
- Peter Nicholson
- http://www.hashbangcode.com/blog/netscape-http-cooke-file-parser-php

<h1>License</h1>
MIT-License
