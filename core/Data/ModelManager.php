<?php

namespace Light\Data;

class ModelManager
{
	private static $loadedModels = array();
	
	/**
	 * Makes sure that the specified model is loaded.
	 * @param Model $model
	 * @return Model
	 */
	public static function prepare(Model $model)
	{
		if (!in_array($model,self::$loadedModels,true))
		{
			$model->load();
			self::$loadedModels[] = $model;
		}
		return $model;
	}
}