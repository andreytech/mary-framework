<?php
class Input {
	static function post($field = false, $is_allow_html = false) {
		return self::getValue($_POST, $field, $is_allow_html);
	}

	static function getValue($data, $field, $is_allow_html) {
		if($field !== false) {
			if(isset($data[$field])) {
				if($is_allow_html) {
					return $data[$field];
				}else {
					return self::escapeString($data[$field]);
				}
			}else {
				return false;
			}
		}

		if($is_allow_html) {
			return $data;
		}else {
			return self::escapeString($data);
		}

	}

	static function escapeString($value) {
		if(is_array($value)) {
			foreach($value as $key => $arr_item) {
				$value[$key] = self::escapeString($arr_item);
			}
			return $value;
		}

		return htmlentities($value, ENT_QUOTES, 'UTF-8');
	}

	static function get() {
		$result = null;
		parse_str(parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY), $result);

		return self::getValue($resutl, $field, $is_allow_html);
	}
}