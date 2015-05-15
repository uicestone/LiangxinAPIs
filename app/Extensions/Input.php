<?php namespace App\Extensions;

class Input extends \Illuminate\Support\Facades\Input {
	
	/**
	 * read from http request body
	 * parse to data in array
	 */
	public static function data($field = null)
	{
		$raw = static::$app['request']->getContent();
		
		if(static::$app['request']->isJson())
		{
			$data = json_decode($raw, JSON_OBJECT_AS_ARRAY);
		}
		
		if(str_contains(static::$app['request']->header('CONTENT_TYPE'), '/x-www-form-urlencoded'))
		{
			$data = array();
			parse_str($raw, $data);
		}
		
		if(!isset($data))
		{
			$data = $raw;
		}
		
		if(isset($field) && is_array($data) && array_key_exists($field, $data))
		{
			return $data[$field];
		}
		
		return $data;
		
	}
	
	/**
	 * Get query string arguments.
	 * 
	 * add supports of comma separated and JSON arguments
	 */
	public static function query($key = null, $default = null)
	{
		$args = static::$app['request']->query($key, $default);
		
		static::_parse($args);
		
		return $args;
	}
	
	protected static function _parse(&$arg)
	{
		if(is_array($arg))
		{
			foreach($arg as &$a)
			{
				static::_parse($a);
			}
		}
		else
		{
			$decoded = json_decode($arg, JSON_OBJECT_AS_ARRAY);

			if(!is_null($decoded))
			{
				$arg = $decoded;
			}
			elseif(str_contains($arg, ','))
			{
				$arg = explode(',', $arg);
			}
		}
		
		return $arg;
		
	}


	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'request'; }

}
