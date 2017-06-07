# php-steamlogin
Script to obtain steamid, cookies and sessionid.

## Example
```php
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
	//$SteamLogin->view('http://steamcommunity.com/id/Kysune');
	
	//Sending tradeoffers: https://github.com/halipso/php-steam-tradeoffers
	
	if($SteamLogin->error != '') echo $SteamLogin->error;
}else{
	echo $SteamLogin->error;
}
?>
```

## Public Parameters
- error
- success

## Public Methods
- ***login([$authcode, $twofactorcode])*** - login to steam
- ***view($url)*** - print source of $url

## Special thanks to
- Peter Nicholson
- http://www.hashbangcode.com/blog/netscape-http-cooke-file-parser-php

## License
MIT-License
