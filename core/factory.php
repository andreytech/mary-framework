<?php

class CoreFactory {
	static function getDB() {
		return CoreActiveRecord::getInstance();
	}
}