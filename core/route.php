<?php
class Route {
	static function buildAlias($str) {
		$str = trim($str, " \t./\\");
		$str = html_entity_decode($str, ENT_QUOTES);
		$str = str_replace('&', ' and ', $str);
		$str = str_replace('   ', ' ', $str);
		$str = str_replace('  ', ' ', $str);
		$str = str_replace(' ', '-', $str);
		$str = preg_replace('/[^a-zA-Z0-9_-]/','',$str);
		$str = str_replace('---', '-', $str);
		$str = str_replace('--', '-', $str);
		$str = strtolower($str);
		return $str;
	}

	static function build($non_sef, $alias, $is_add_base_url = 0) {
		$alias = Route::buildAlias($alias);
		//trim($path, '/\\')
//		return '/'.$alias.'/';
		$non_sef = trim($non_sef, " \t./\\");
		$sef_url = trim($alias, " \t./\\").'.html';

		$config = CoreConfig::getInstance();
		$is_use_sef_urls = $config->get('is_use_sef_urls');

		if(!$is_use_sef_urls) {
			if($is_add_base_url) {
				return base_url() . $non_sef.'/';
			}
			return $non_sef.'/';
		}

		$db = CoreFactory::getDB();

		$urls_db_table = $config->get('urls_db_table');
		$query = "
			SELECT non_sef FROM `{$urls_db_table}` WHERE sef = '{$sef_url}'
		";
		$current_non_sef = $db->setQuery($query)->getResult();
		if($current_non_sef && $current_non_sef == $non_sef) {
			if($is_add_base_url) {
				return base_url() . $sef_url;
			}
			return $sef_url;
		}

		$query = "
			SELECT sef FROM `{$urls_db_table}` WHERE non_sef = '{$non_sef}'
		";
		$cur_sef_url = $db->setQuery($query)->getResult();

		if($cur_sef_url) {
			$sef_url = $cur_sef_url;
		}else {
			$query = "
				INSERT INTO `{$urls_db_table}` SET sef = '{$sef_url}', non_sef = '{$non_sef}'
			";
			$db->setQuery($query)->execute();
		}
		
		if($is_add_base_url) {
			return base_url() . $sef_url;
		}

		return $sef_url;
	}


	static function removeNonSEF($non_sef) {
		$non_sef = trim($non_sef, " \t./\\");

		$db = CoreDBMysql::getInstance();
		$config = CoreConfig::getInstance();
		$urls_db_table = $config->get('urls_db_table');

		$query = "
			DELETE FROM `{$urls_db_table}` WHERE non_sef = '{$non_sef}'
		";
		$db->setQuery($query)->execute();
	}
}