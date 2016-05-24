<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model {

	protected $fillable = ['roumd', 'title', 'choices', 'answer'];

	public function getChoicesAttribute($value)
	{
		return json_decode($value);
	}

	public function setChoicesAttribute($value)
	{
		$this->attributes['choices'] = json_encode($value, JSON_UNESCAPED_UNICODE);
	}

}
