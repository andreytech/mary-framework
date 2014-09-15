<?php
	class URL {
		static private $non_sef_path = false;
		static private $sef_path = false;
		static private $mode = 'non_sef';

		static function segment($n, $default = false) {
			$path = self::getNonSEFPath();
			$segments = explode('/', $path);

			if(!isset($segments[$n-1])) {
				return $default;
			}
			return $segments[$n-1];
		}

		static function getNonSEFPath() {
			if(self::$non_sef_path !== false) {
				return self::$non_sef_path;
			}

			if(!empty($_SERVER["PATH_INFO"])) {
				$request_uri = trim(parse_url($_SERVER["PATH_INFO"], PHP_URL_PATH), '/');
			}else {
			$request_uri = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), '/');
			}

			$request_base_url = 'http://'.$_SERVER["HTTP_HOST"].'/'.$request_uri.'/';
			$base_url = base_url();
			if(strstr($request_base_url, $base_url) !== false) {
				$request_uri = substr($request_base_url, strlen($base_url));
				$request_uri = trim($request_uri, '/');
			}

			$config = CoreConfig::getInstance();
			$is_use_sef_urls = $config->get('is_use_sef_urls');

			if(!$is_use_sef_urls) {
				self::$non_sef_path = $request_uri;
				return self::$non_sef_path;
			}

			$db = CoreFactory::getDB();

			$urls_db_table = $config->get('urls_db_table');
			$query = "
				SELECT non_sef FROM `{$urls_db_table}` WHERE sef = '{$request_uri}'
			";
			self::$non_sef_path = $db->setQuery($query)->getResult();
			if(self::$non_sef_path) {
				self::$mode = 'sef';
			}else {
				self::$non_sef_path = $request_uri;
			}

			return self::$non_sef_path;
		}

		static function getPageAlias() {
			$request_uri = trim($_SERVER["REQUEST_URI"], '/');
			$parts = explode('.', $request_uri);
			return $parts[0];
		}

		static function getMode() {
			return self::$mode;
		}

		static function getSEFPath($non_sef) {
			if(self::$sef_path !== false) {
				return self::$sef_path;
			}

			$db = CoreFactory::getDB();

			$config = CoreConfig::getInstance();
			$urls_db_table = $config->get('urls_db_table');
			$query = "
				SELECT sef FROM `{$urls_db_table}` WHERE non_sef = '{$non_sef}'
			";
			self::$sef_path = $db->setQuery($query)->getResult();
			return self::$sef_path;
		}
	}

	function base_url() {
		$config = CoreConfig::getInstance();
		$base_url = $config->get('base_url');
		if(!$base_url) {
			$base_url = 'http://'.$_SERVER["HTTP_HOST"].'/';
		}
		return $base_url;
	}

	function redirect($url) {
		if(strstr($url, 'http://') === false) {
			$url = trim($url, '/');
			$url = base_url().$url;
		}
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.$url);
		exit;
	}

	function anchor($uri = '', $title = '', $attributes = '')
	{
		$title = (string) $title;

		if(preg_match('!^\w+://! i', $uri)) {
			$site_url = $uri;
		}else {
			$site_url = base_url().$uri;
		}

		if ($title == '')
		{
			$title = $site_url;
		}

		return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
	}