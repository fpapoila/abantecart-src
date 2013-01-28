<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>
  
 UPGRADE NOTE: 
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.  
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}

final class AEncryption {
	private $key;
	
	function __construct($key) {
        $this->key = $key;
	}
	
	function encrypt($value) {
		if (!$this->key) { 
			return $value;
		}
		
		$stdout = '';
		
		for ($i = 0; $i < strlen($value); $i++) {
			$char = substr($value, $i, 1);
			$keychar = substr($this->key, ($i % strlen($this->key)) - 1, 1);
			$char = chr(ord($char) + ord($keychar));
			
			$stdout .= $char;
		} 
		
        return base64_encode($stdout); 
	}
	
	function decrypt($value) {
		if (!$this->key) { 
			return $value;
		}
		
		$stdout = '';
		
		$value = base64_decode($value);
		
		for ($i = 0; $i < strlen($value); $i++) {
			$char = substr($value, $i, 1);
			$keychar = substr($this->key, ($i % strlen($this->key)) - 1, 1);
			$char = chr(ord($char) - ord($keychar));
			
			$stdout .= $char;
		}
		
		return $stdout;
	}

	/*
	* MD5 based encoding used for passwords 
	*/
	static function getHash($keyword){	
		return md5($keyword.SALT);
	}

	/*
	* Encoding of URL for marketplace access
	*/
	static function addEncoded_stid($url){
		$text = UNIQUE_ID.'***'.$_SERVER ['SERVER_ADDR'];
		$encrypt = trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, 'tracetnaba', $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));

		$url = str_replace('&amp;','&',$url);
		$url = $url.(strpos($url,'?')===false? '?' : '&amp;').'stid='.rawurlencode($encrypt);
	return $url;
	}

}

// SSL Based encryption class PHP 5.3 >
// Manual Configuration is required
/* 
Requirement: PHP => 5.3 and openSSL enabled

NOTE: Do not confuse SSL data encryption with signed SSL certificates (HTTPS) used for browser access to sites 

Configuration: 
Add key storage location path. 
Add below lines to /system/config.php file. Change path to your specific path on your server
define('ENCRYPTION_KEYS_DIR', '/path/to/keys/');

NOTES: 
1. Keep Key in secure location with restricted file permissions for root and apache (webserver)
2. There is no key expiration mamagement. 
These needs to be accounted for in key management procedures


Examples:

1. Generate Keys

$password = "123456";
$conf = array (
    'digest_alg'       => 'sha512',
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'private_key_bits' => 2048,
    'encrypt_key'      => true
);
$enc = new ASSLEncryption ();
$keys = $enc->generate_ssl_key_pair($conf, $password);
$enc->save_ssl_key_pair($keys, 'key_with_pass');
echo_array($keys);	

2. Encrypt

$enc = new ASSLEncryption ('key_with_pass');
$enc_str = $enc->encrypt('test text');
echo $enc_str;

3. Decrypt

$enc = new ASSLEncryption ('', 'key_with_pass', $password);
echo $enc->decrypt($enc_str);


Need help configuring, supporting or extending functionality, 
contact www.abantecart.com for forum or paid support
*/


final class ASSLEncryption {
	private $pubkey;
	private $prkey;
	private $key_path;
	private $failed_str = "*****";
	private $error;
	public $active = false;
	
	//To generate new keys, class can be instintiated with no data passed
	function __construct($pubkey_name = '', $prkey_name = '', $passphrase = null) {
		$this->registry = Registry::getInstance();
		$this->log = $this->registry->get('log');
		$this->message = $this->registry->get('messages');
		
		//Validate if SSL PHP support is installed
		if ( !function_exists( 'openssl_pkey_get_public' ) ){
		    $error = "Error: PHP OpenSSL is not available on your server! Check if OpenSSL installed for PHP and enabled";
		    $this->log->write($error);
		    $this->message->saveError('OpenSSL Error',$error);
		    return NULL;
		}

        //construct key storage path 
        //NOTE: ENCRYPTION_KEYS_DIR needs to be added into configuration file
        //Suggested:  Directory to be secured for read a write ONLY for users root and apache (web server).    
        if ( defined('ENCRYPTION_KEYS_DIR') ) {
        	$this->key_path = ENCRYPTION_KEYS_DIR;	
        } else {
        	$this->key_path = DIR_SYSTEM . 'keys/';
        }
		
		if ($pubkey_name) {
			$this->pubkey = $this->getPublicKey($pubkey_name.'.pub');
		}
        if ($prkey_name) {
        	$this->loadPrivateKey($prkey_name.'.prv', $passphrase);
        }
        $this->active = true; 
	}

	/**
	* Generate new Key Private/Public keys pair. 
	* Incput is $config array with standard openssl_csr_new configargs 
	* $passphrase is set if want to have a passphrase to access private key
	* @return array
	*/
  	public function generate_ssl_key_pair($config = array(), $passphrase = null) {
  	  $default_length = 2048;

      if (!isset($config['private_key_bits'])) {
        $config['private_key_bits'] = $default_length;     
      } 
	  //Set key bits limits
      if ($config['private_key_bits'] < 256) {
          $config['private_key_bits'] = 256;
      } else if ($config['private_key_bits'] > 8192) {
          $config['private_key_bits'] = 8192;
      }      
      
      $res = openssl_pkey_new($config);

	  //# Do we need to use passphrase for the key?
	  $privatekey = '';
      if ((isset($config['encrypt_key'])) && ($config['encrypt_key'] == true)) {
		openssl_pkey_export($res, $privatekey, $passphrase);      
      } else {
        openssl_pkey_export($res, $privatekey);
      }

      $publickey = openssl_pkey_get_details($res);
      $publickey = $publickey["key"];

      return array('public' => $publickey, 'private' => $privatekey);
    }

	/**
	* Save Private/Public keys pair to set key_path location
	* Incput: Private/Public keys pair array
	* 		  keyname
	* 
	* @return mixed
	*/
  	public function save_ssl_key_pair($keys = array(), $key_name) {
		if(!file_exists( $this->key_path )){
			$result = mkdir($this->key_path,0700,TRUE); // create dir with nested folders
		}else{
			$result = true;
		}	
		if(!$result){
		    $error = "Error: Can't create directory ". $this->key_path . " for saving SSL keys!";
		    $this->log->write($error);
		    $this->message->saveError('Create SSL Key Error',$error);
		    return $error;
		}
		if (empty($key_name)) {
			$key_name = 'default_key';
		}
					
		foreach ( $keys as $type => $key ) {
			if ( $type == 'private') {
				$ext = '.prv';			
			} else if ( $type == 'public') {
				$ext = '.pub';			
			}
			$file = $this->key_path . '/' . $key_name . $ext;				
			if ( file_exists($file) ) {
		        $error = "Error: Can't create key ". $key_name . "! Already Exists";
		        $this->log->write($error);
		        $this->message->saveError('Create SSL Key Error',$error);
		        return $error;		
			}
			$handle = fopen( $file, 'w');
			fwrite($handle, $key . "\n");
			fclose($handle); 
		}
	}
	
	/**
	* Get public key based on key name provided. It is loaded if not yet loaded
	* Key's are stored in the path based on the configuration
	* @return string 
	*/
	public function getPublicKey( $key_name ) {
		if ( empty($this->pubkey) ) {
	    	$this->pubkey = openssl_pkey_get_public("file://".$this->key_path.$key_name);
		}
		return $this->pubkey;
	}

	/**
	* Load private key based on key name provided. 
	* Input : Key name and passphrase (if used)
	* Key's are stored in the path based on the configuration
	* NOTE: Private key value never returned back 
	* @return bool 
	*/
	public function loadPrivateKey($key_name, $passphrase = '') {	
		$this->prkey = openssl_pkey_get_private("file://".$this->key_path.$key_name, $passphrase);
		if ($this->prkey){
		  return true;
		} else {
		  return false;
		} 
	}
	
	/**
	* Dectript value based on private key ONLY
	* @return string
	*/
  	public function decrypt( $crypttext ) {
		if (empty($crypttext)) {
		 	return '';
		}
		//check if encryption is off or this is not encrypted string 
		if ( !$this->active || !base64_decode($crypttext, true)) {
			return $crypttext;
		}
		
		$cleartext = '';
		if ( empty($this->prkey) ) {
		 	$error = "Error: SSL Decryption failed! Missing private key";
		 	$this->log->write($error);
		 	return $this->failed_str;	   
		}
		
		if ((openssl_private_decrypt(base64_decode($crypttext), $cleartext, $this->prkey)) === true) {
		 	return $cleartext;  
		} else {
		 	$error = "Error: SSL Decryption based on private key has failed! Possibly wrong key!";
		 	$this->log->write($error);
		 	return $this->failed_str;	          
		}
    }

	/**
	* Encrypt value based on public key ONLY
	* @return string
	*/
  	public function encrypt( $cleartext) {
		if (empty($cleartext)) {
			return '';
		}
 		//check if encryption is off or this is not encrypted string 
		if ( !$this->active ) {
			return $cleartext;
		}
 	   
		$crypttext = '';
		if ( empty($this->pubkey) ) {
		 	$error = "Error: SSL Encryption failed! Missing public key";
		 	$this->log->write($error);
		 	return '';	   
		}
		
		if ((openssl_public_encrypt($cleartext, $crypttext, $this->pubkey)) === true) {
		 	return base64_encode($crypttext);  
		} else {
		 	$error = "Error: SSL Encryption based on public key has failed! Possibly wrong key!";
		 	$this->log->write($error);
		 	return '';	          
		}
    }
		
	public function getKeyPath() {
		return $this->key_path;
	}	
	
}

// SSL Based data encryption class based on ASSLEncryption class
// Manual Configuration is required
/* 
This class is managin encryption/decription of data in AbanteCart database tables 
configured in $this->enc_data array
These tables need to have specific postfix in the name like '_enc'

Configuration: 
Add below configs to /system/config.php file.
define('DATA_ENCRYPTION_ENABLED', true);
define('ENCRYPTED_POSTFIX', '_enc');
define('DATA_ENCRYPTION_KEYPAIR', 'data_enc_key');

NOTE: DATA_ENCRYPTION_KEYPAIR needs to be a files name portion for public and private keys stored in ENCRYPTION_KEYS_DIR 
Keys can be generated by ASSLEncryption class (see ASSLEncryption class) or by any other openSSL script
Example of keys: data_enc_key for data_enc_key.pub and data_enc_key.prv
This is also genrated in encryption_data_manager extension

Tables SQL:
New tables needs to be created with provided SQL. 
Encryption Data Manager extension runs SQL on install

Limitation: passphrase is not supported to data encryption.

*/

final class ADataEncryption {
	private $key_name;
	private $passphrase;
	private $enc_data;
	private $posfix = '';
	public $active = false;

	function __construct($key_name = null, $passphrase = null) {
		//if not enabled exit
		if ( !defined('DATA_ENCRYPTION_ENABLED') || DATA_ENCRYPTION_ENABLED != true) {
			return NULL;
		}		
		
		if ($key_name ){
			$this->key_name = $key_name;
		} else {
			$this->key_name = DATA_ENCRYPTION_KEYPAIR;
		} 
		
		if ($passphrase) {
			$this->passphrase = $passphrase;
		}
		
		//set tables/fields encrypted
		$this->enc_data['orders'] = array(
			'id' => 'order_id',
			'fields' => array ( 
				  'telephone',
				  'fax',
				  'email',
				  'shipping_company',
				  'shipping_address_1',
				  'shipping_address_2',
				  'shipping_city',
				  'shipping_postcode',
				  'shipping_country',
				  'payment_company',
				  'payment_address_1',
				  'payment_address_2',
				  'payment_city',
				  'payment_postcode',
				  'payment_country'
			 ),
		);
		$this->enc_data['customers'] = array(
			'id' => 'customer_id',
			'fields' => array ( 
				  'telephone',
				  'fax',
				  'email'	
			 ),
		);
		$this->enc_data['addresses'] = array(
			'id' => 'address_id',
			'fields' => array ( 
				  'company',
				  'address_1',
				  'address_2',
				  'postcode',
				  'city'	
			 ),
		);		
		
		if ( defined('ENCRYPTED_POSTFIX') ) {
			$this->posfix = ENCRYPTED_POSTFIX;
		} else {
			$this->posfix = '_enc';
		}
		
		$this->active = true;
	}

	/**
	* Get postfix used to extend tables storing encrypted data 
	* This is only for tables that require encryption
	* This is set in ENCRYPTED_POSTFIX configuration 
	*@param none
	*@return string
	*/	
	public function posfix( $table ) {
		//check if table requires encryption and there is a postfix
		if ( $this->getEcryptedTableID($table) ) {
			return $this->posfix;
		} else {
			return '';
		}
	}

	/**
	* Get list of tables containing encrypted data 
	*@param none
	*@return array
	*/	
	public function getEcryptedTables(){		
		return array_keys($this->enc_data);
	}	

	/**
	* Get ID field name for table containing encrypted data 
	*@param string
	*@return string
	*/	
	public function getEcryptedTableID( $table ){		
		return $this->enc_data[ $table ]['id'];
	}	
	
	/**
	* Get list of encrypted fields in table containing encrypted data 
	*@param string
	*@return array
	*/	
	public function getEcryptedFields( $table ){		
		return $this->enc_data[ $table ]['fields'];
	}	

	/**
	* Add to the list of encrypted tables/fields containing encrypted data 
	*@param array
	*@return none
	*/	
	public function addEcryptedTables( $table_data ) {
		foreach ( $table_data as $table => $data ) {
			if ( in_array($table, $this->getEcryptedTables() ) ) {
		        $error = "ADataEncryption Error: Can't add existing table ". $table . "! Table already Exists";
		        $this->log->write($error);			
			} else {
				$this->enc_data[$table] = $data;			
			}
		}
	}	

	/**
	* Add to the list of fields to existing tables containing encrypted data 
	*@param string, array
	*@return none
	*/	
	public function addEcryptedFields( $table, $fields ){
		if ( empty($table) ) {
			return;
		}
	
		foreach ( $fields as $field ) {
			if ( !in_array($field, $this->getEcryptedFields($table) ) ) {
				$this->enc_data[$table][] = $field;			
			}
		}
	}	

	/**
	* Decrypt 1 row of data in table for fields that are encrypted
	*@param array, string, string
	*@return array
	*/	
	public function decrypt_data ( $crypt_data_arr, $table, $pass = null) {
		if ( empty($pass) ) {
			$pass = $this->passphrase;
		}
		if ( empty($table) ) {
			return array();
		}
		//if encryption off return pure data
		if ( !$this->active ) {
			return $crypt_data_arr;
		}

		$open_data_arr = $crypt_data_arr;				
		$enc = new ASSLEncryption('', $this->key_name, $pass);
		$fields = $this->getEcryptedFields($table);
		foreach ($crypt_data_arr as $key => $data) {
			if ( in_array($key, $fields) ) {
				$open_data_arr[$key] = 	$enc->decrypt($data);			
			}
		}
		return $open_data_arr;	
	}

	/**
	* Encrypt 1 row of data in table for fields that are encrypted
	*@param array, string
	*@return array
	*/	
	public function encrypt_data ( $open_data_arr, $table ) {
		if ( empty($table) ) {
			return array();
		}
		//if encryption off return pure data
		if ( !$this->active ) {
			return $open_data_arr;
		}

		$crypt_data_arr = $open_data_arr;
		$enc = new ASSLEncryption($this->key_name);
		$fields = $this->getEcryptedFields($table);
		foreach ($open_data_arr as $key => $data) {
			if ( in_array($key, $fields) ) {
				$crypt_data_arr[$key] = $enc->encrypt($data);			
			}
		}
		return $crypt_data_arr;
	}

	/**
	* Encrypt 1 field of data
	*@param string
	*@return string
	*/	
	public function encrypt_record ( $open_data ) {
		//if encryption off return pure data
		if ( !$this->active ) {
			return $open_data;
		}

		$enc = new ASSLEncryption($this->key_name);
		return $enc->encrypt($open_data);
	}

	/**
	* Decrypt 1 field of data
	*@param string, string
	*@return string
	*/	
	public function decrypt_record ( $crypt_data, $pass = null) {
		if ( empty($pass) ) {
			$pass = $this->passphrase;
		}
		//if encryption off return pure data
		if ( !$this->active ) {
			return $crypt_data;
		}

		$enc = new ASSLEncryption('', $this->key_name, $pass);
		return $enc->decrypt($crypt_data);
	}
}

?>