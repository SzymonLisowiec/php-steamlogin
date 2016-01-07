<?php
defined('php-steamlogin') or die('null');
require_once("lib/Math/BigInteger.php");
require_once("lib/Crypt/RSA.php");

class SteamLogin {
	
	public $error = false;
	public $success = false;
	
	private $config = array(
		'username' => '',
		'password' => '',
		'datapath' => ''
	);
	private $accountdata = array();
	
	public function __construct($config){
		$this->config = $config;
		if($this->config['username'] != '' && $this->config['password'] != '' && $this->config['datapath'] != ''){
			
			if(file_exists($this->config['datapath'].'/logindata.json')){
				$account = json_decode(file_get_contents($this->config['datapath'].'/logindata.json'));
				$this->accountdata['steamid'] = ((isset($account->steamid))?$account->steamid:'');
			}
			
			$this->success = true;
		}else $this->error('Bad config!');
	}
	
	public function login($authcode='', $twofactorcode=''){
		$dologin = $this->getRSAkey();
		if($dologin->publickey_mod && $dologin->publickey_exp && $dologin->timestamp){
			
			$password = $this->config['password'];
			$rsa = new Crypt_RSA();
			$key = array('modulus' => new Math_BigInteger($dologin->publickey_mod, 16), 'publicExponent' => new Math_BigInteger($dologin->publickey_exp, 16));
			$rsa->loadKey($key, CRYPT_RSA_PUBLIC_FORMAT_RAW);
			$rsa->setPublicKey($key);
			$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
			$enc_password = base64_encode($rsa->encrypt($password));
			
			$login = $this->request('POST', 'https://steamcommunity.com/login/dologin/', array(
				'password' => $enc_password,
				'username' => $this->config['username'],
				'twofactorcode' => $twofactorcode,
				'emailauth' => $authcode,
				'loginfriendlyname' => '',
				'capatcha_text' => '',
				'emailsteamid' => ((isset($this->accountdata['steamid']))?$this->accountdata['steamid']:''),
				'rsatimestamp' => $dologin->timestamp,
				'remember_login' => 'true',
				'donotcache' => time()
			));
			$login = json_decode($login);
			if($login->success == false){
				if(isset($login->emailsteamid) && $login->emailauth_needed == true){
					if($authcode == ''){
						file_put_contents($this->config['datapath'].'/logindata.json', json_encode(array('steamid' => $login->emailsteamid)));
						$this->error('Please enter AUTHCODE available in your e-mail inbox (domain: '.$login->emaildomain.').');
					}else $this->error('You enter bad authcode!');
				}else if($login->requires_twofactor == true){
					if($twofactorcode == ''){
						$this->error('Please enter twofactorcode (mobile auth).');
					}else $this->error('You enter bad twofactorcode!');
				}
			}else{
				preg_match_all('#g_sessionID\\s\=\\s\"(.*?)\"\;#si', $this->view('http://steamcommunity.com/id'), $matches);
				return array(
					'steamid' => $login->transfer_parameters->steamid,
					'sessionId' => $matches[1][0],
					'cookies' => $this->cookiejarToString(file_get_contents('cookiejar.txt'))
				);
			}
			return $login;
		}else $this->error('Bad RSA!');
		return $dologin;
	}
	
	public function view($url){
		return $this->request('POST', $url);
	}
	
	private function request($type, $url, $data=array()){
		$c = curl_init();
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_NOBODY, false);
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
		curl_setopt($c, CURLOPT_COOKIESESSION, false);
		curl_setopt($c, CURLOPT_COOKIEJAR, $this->config['datapath'].'/cookiejar.txt');
		curl_setopt($c, CURLOPT_COOKIEFILE, $this->config['datapath'].'/cookiejar.txt');
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_CUSTOMREQUEST, strtoupper($type));
		$return = curl_exec($c);
		curl_close($c);
		return $return;
	}
	
	private function getRSAkey(){
		return json_decode($this->request('POST', 'https://steamcommunity.com/login/getrsakey/', array(
			'username' => $this->config['username'],
			'donotcache' => time()
		)));
	}
	
	private function cookiejarToString($string){
		$cookieString = '';
		$lines = explode("\n", $string);
		foreach($lines as $line){
			if(isset($line[0]) && substr_count($line, "\t") == 6){
				$tokens = explode("\t", $line);
				$tokens = array_map('trim', $tokens);
				$cookieString .= $tokens[5].'='.$tokens[6].'; ';
			}
		}
		return $cookieString;
	}
	
	private function error($error){
		if($this->error === false) $this->error = $error;
	}
	
}
?>
