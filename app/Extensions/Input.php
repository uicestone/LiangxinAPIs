<?php namespace App\Extensions;

class Input extends \Illuminate\Support\Facades\Input {
	
	/**
	 * read from http request body
	 * parse to data in array
	 */
	public static function data($field = null)
	{
		if(is_null($field))
		{
			return array_replace_recursive(static::$app['request']->request->all(), static::$app['request']->files->all());
		}
		elseif(static::$app['request']->files->get($field))
		{
			return static::$app['request']->files->get($field);
		}
		else
		{
			return static::$app['request']->request->get($field);
		}
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
