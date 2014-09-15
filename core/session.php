<?php

class Session {
	protected static $_instance = null;
	private $params = array();
	private $data = array();
	private $sess_info = array();
	private $ip_address = false;
	
	static function getInstance($expiration = false) {
		if (null === self::$_instance) {
			self::$_instance = new self($expiration);
		}else {
			self::$_instance->update($expiration);
		}

		return self::$_instance;
	}

	static function isCreated() {
		return (null !== self::$_instance);
	}
	
	function __construct($expiration = false) {
		$config = CoreConfig::getInstance();

		foreach (array('sess_expiration', 'sess_match_ip', 'sess_match_useragent'
						 , 'sess_cookie_name', 'sess_time_to_update', 'cookie_path'
						 , 'cookie_domain', 'cookie_prefix', 'encryption_key', 'sess_db_table') as $key
		) {
			$this->params[$key] = $config->get($key);
		}

		if($expiration) {
			$this->params['sess_expiration'] = $expiration;
		}
		if (!isset($this->params['sess_expiration'])) {
			$this->params['sess_expiration'] = (60*60*24*7*2);
		}

		if ( !$this->load()) {
			$this->create();
		} else {
			$this->update($expiration);
		}

		$this->purge();
	}

	function create() {
		$config = CoreConfig::getInstance();

		$sessid = '';
		while (strlen($sessid) < 32) {
			$sessid .= mt_rand(0, mt_getrandmax());
		}
		$sessid .= $this->getIP();
		$sessid = md5(uniqid($sessid, true));

//		$session_table = Sessions::getInstance();
//		$session_table->id = $sessid;
//		$session_table->ip_address = $this->getIP();
//		$session_table->user_agent = substr($this->getUserAgent(), 0, 50);
//		$session_table->last_activity = time();
//		$session_table->insert();
		$this->sess_info['id'] = $sessid;
		$this->sess_info['ip_address'] = $this->getIP();
		$this->sess_info['user_agent'] = substr($this->getUserAgent(), 0, 50);
		$this->sess_info['last_activity'] = time();
		$this->sess_info['expiration_time'] = $this->params['sess_expiration'];

		$db = CoreFactory::getDB();

		$query = "
			INSERT INTO `".$config->get('sess_db_table')."` SET
			`id` = '{$this->sess_info['id']}'
			, `ip_address` = '".$this->sess_info['ip_address']."'
			, `user_agent` = '".$this->sess_info['user_agent']."'
			, `last_activity` = '".$this->sess_info['last_activity']."'
			, `expiration_time` = '".$this->sess_info['expiration_time']."'
		";
		$db->setQuery($query)->execute();
		 
		$expire = $this->sess_info['expiration_time'] + time();
		if(!$this->sess_info['expiration_time']) {
			$expire = 0;
		}

		$this->_setCookie(
			$sessid.md5($sessid.$config->get('encryption_key'))
			, $expire
		);
	}

	static function delete($key) {
		$session_obj = Session::getInstance();
		unset($session_obj->data[$key]);

		$session_obj->write();
	}
	
	static function destroy() {
		$session_obj = Session::getInstance();
		$session_obj->_destroy();
	}

	function _destroy() {
		$config = CoreConfig::getInstance();
		$db = CoreFactory::getDB();

		if (isset($this->sess_info['id'])) {
			$query = "
				DELETE FROM `".$config->get('sess_db_table')."`
				WHERE `id` = '{$this->sess_info['id']}'
			";
			$db->setQuery($query)->execute();

//			Sessions::getInstance()
//				->where('id', $this->sess_info['id'])
//				->delete();
		}

		$this->_setCookie( addslashes(serialize(array())), (time() - 31500000));
	}

	static function get($item) {
		$session_obj = Session::getInstance();
		return ( ! isset($session_obj->data[$item])) ? false : $session_obj->data[$item];
	}
	
	function load() {
		$config = CoreConfig::getInstance();
		$db = CoreFactory::getDB();

		$session = false;
		if(!empty($_COOKIE[$this->params['sess_cookie_name']])) {
			$session = $_COOKIE[$this->params['sess_cookie_name']];
		}

		if (!$session) {
//			Log::add("Session cookie was not found");
			return false;
		}

		$hash	 = substr($session, strlen($session)-32); // get last 32 chars
		$session_id = substr($session, 0, strlen($session)-32);

		if ($hash !==  md5($session_id.$config->get('encryption_key'))) {
//			Log::add("The session cookie data did not match what was expected. This could be a possible hacking attempt.");
			$this->_destroy();
			return false;
		}

//		$this->sess_info = Sessions::getInstance()
//			->where('id', $session_id)
//			->getArray();
		$query = "
			SELECT *
			FROM `".$config->get('sess_db_table')."`
			WHERE id = '{$session_id}'
		";
		$this->sess_info = $db->setQuery($query)->getArray();

		if (!$this->sess_info) {
			$this->_destroy();
			return false;
		}

		if(!empty($this->params['sess_check_expiration'])) {
			if(empty($this->sess_info['expiration_time'])) {
				$this->sess_info['expiration_time'] = $this->params['sess_expiration'];
			}

			if ( $this->sess_info['expiration_time']
				&& ($this->sess_info['last_activity'] + $this->sess_info['expiration_time']) < time()
			) {
				$this->_destroy();
				return false;
			}
		}

		if (!empty($this->params['sess_match_ip'])
			&& $this->sess_info['ip_address'] != $this->getIP()
		) {
			$this->destroy();
			return false;
		}

		if (!empty($this->params['sess_match_useragent'])
			&& trim($this->sess_info['user_agent']) != trim(substr($this->getUserAgent(), 0, 50))
		) {
			$this->_destroy();
			return false;
		}

		if ($this->sess_info['user_data'] != '') {
			$this->data = unserialize(stripslashes($this->sess_info['user_data']));
		}

		return true;
	}
	
	function purge() {
		$config = CoreConfig::getInstance();
		$db = CoreFactory::getDB();
		
		if ((rand() % 100) < 5) {
			$expire = time() - $this->params['sess_expiration'];

//			Sessions::getInstance()
//				->where('last_activity', $expire, '<')
//				->delete();

//			Log::add("Old session data deleted");
			$query = "
				DELETE FROM `".$config->get('sess_db_table')."`
				WHERE `last_activity` < '{$expire}'
			";
			$db->setQuery($query)->execute();
		}
	}
	
	function update($expiration = false) {
		if (!$expiration
			&& ($this->sess_info['last_activity'] + $this->params['sess_time_to_update']) >= time()
		) {
			return;
		}

		if($expiration) {
			$this->sess_info['expiration_time'] = $expiration;
		}

		$config = CoreConfig::getInstance();
		$db = CoreFactory::getDB();

		$old_sessid = $this->sess_info['id'];
		$new_sessid = '';
		while (strlen($new_sessid) < 32) {
			$new_sessid .= mt_rand(0, mt_getrandmax());
		}

		$new_sessid .= $this->getIP();
		$new_sessid = md5(uniqid($new_sessid, true));

		$this->sess_info['id'] = $new_sessid;

		$query = "
			UPDATE `".$config->get('sess_db_table')."`
			SET
				`id` = '{$new_sessid}'
				, `last_activity` = '".time()."'
				, `expiration_time` = '{$this->sess_info['expiration_time']}'
			WHERE `id` = '{$old_sessid}'
		";
		$db->setQuery($query)->execute();

		$expire = $this->sess_info['expiration_time'] + time();
		if(!$this->sess_info['expiration_time']) {
			$expire = 0;
		}

		$this->_setCookie(
			$new_sessid.md5($new_sessid.$config->get('encryption_key'))
			, $expire
		);
	}

	static function set($key, $value) {
		$session_obj = Session::getInstance();
		$session_obj->data[$key] = $value;

		$session_obj->write();
	}
	
	function _setCookie($value, $expire) {
		setcookie(
			$this->params['sess_cookie_name'],
			$value,
			$expire,
			$this->params['cookie_path'],
			$this->params['cookie_domain'],
			0
		);
		
	}
	
	function write() {
//		$session_table = Sessions::getInstance();
//		$session_table->where('id', $this->sess_info['id']);
//		$session_table->user_data = addslashes(serialize($this->data));
//		$session_table->update();

		$config = CoreConfig::getInstance();
		$db = CoreFactory::getDB();

		$query = "
			UPDATE `".$config->get('sess_db_table')."`
			SET
				`user_data` = '".addslashes(serialize($this->data))."'
			WHERE `id` = '{$this->sess_info['id']}'
		";
		$db->setQuery($query)->execute();
	}


	function getIP() {
		if ($this->ip_address !== false) {
			return $this->ip_address;
		}

		$config = CoreConfig::getInstance();

		if ($config->get('proxy_ips') != ''
			&& $this->server('HTTP_X_FORWARDED_FOR') && $this->server('REMOTE_ADDR')
		) {
			$proxies = preg_split('/[\s,]/', $config->get('proxy_ips'), -1, PREG_SPLIT_NO_EMPTY);
			$proxies = is_array($proxies) ? $proxies : array($proxies);

			$this->ip_address = in_array($_SERVER['REMOTE_ADDR'], $proxies)
				? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('REMOTE_ADDR') AND $this->server('HTTP_CLIENT_IP'))
		{
			$this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('REMOTE_ADDR'))
		{
			$this->ip_address = $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('HTTP_CLIENT_IP'))
		{
			$this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('HTTP_X_FORWARDED_FOR'))
		{
			$this->ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ($this->ip_address === false)
		{
			$this->ip_address = '0.0.0.0';
			return $this->ip_address;
		}

		if (strstr($this->ip_address, ','))
		{
			$x = explode(',', $this->ip_address);
			$this->ip_address = trim(end($x));
		}

		if ( ! $this->valid_ip($this->ip_address))
		{
			$this->ip_address = '0.0.0.0';
		}

		return $this->ip_address;
	}

	function server($key = '') {
		return (isset($_SERVER[$key])?$_SERVER[$key]:false);
	}

	function valid_ip($ip) {
		$ip_segments = explode('.', $ip);

		if (count($ip_segments) != 4) {
			return false;
		}
		if ($ip_segments[0][0] == '0') {
			return false;
		}
		foreach ($ip_segments as $segment) {
			if ($segment == '' OR preg_match("/[^0-9]/", $segment) OR $segment > 255 OR strlen($segment) > 3) {
				return false;
			}
		}

		return true;
	}

	static function getUserAgent() {
		return ( !isset($_SERVER['HTTP_USER_AGENT']) ? false : $_SERVER['HTTP_USER_AGENT'] );
	}


}
