<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Input;

class Config extends Model {
	
	protected $table = 'config';
	protected $fillable = array('key', 'value');
	
	public $timestamps = false;
	
	public function getValueAttribute($value)
	{
		if(Input::query('decode') === false)
		{
			$decoded = json_decode($value);
			return is_null($decoded) ? $value : json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		}
		
		$decoded = json_decode($value);
		return is_null($decoded) ? $value : $decoded;
	}
	
	public function setValueAttribute($value)
	{
		if(is_array($value) || is_object($value))
		{
			$value = json_encode($value, JSON_UNESCAPED_UNICODE);
		}
		
		if(!is_null(json_decode($value)))
		{
			$value = json_encode(json_decode($value), JSON_UNESCAPED_UNICODE);
		}
		
		$this->attributes['value'] = $value;
	}
	
	public static function get($key)
	{
		$item = static::where('key', $key)->first();

		if($item)
		{
			return $item->value;
		}
	}

	public static function set($key, $value)
	{
		$item = static::firstOrNew(['key'=>$key]);
		$item->value = $value;

		$item->save();
		return $item;
	}
	
}
