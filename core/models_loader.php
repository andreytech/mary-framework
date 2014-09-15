<?php
class CoreModelsLoader {
	protected static $models = array();
	
	public function __get($model) {
//		$model = strtolower($model);

		if(isset(self::$models[$model])) {
			return self::$models[$model];
		}

		$model_file = APPLICATION_FOLDER.'models/'
											 .$model.'.php';
		if(!file_exists($model_file)) {
			error('Model file "'.$model_file.'" does not exist');
			exit;
		}

		include $model_file;

		$model_class = ucfirst($model).'Model';
		if(!class_exists($model_class)) {
			error("Model class '{$model_class}' does not exist in {$model_file} ");
			exit;
		}
		
		self::$models[$model] = new $model_class();
		return self::$models[$model];
	}
}