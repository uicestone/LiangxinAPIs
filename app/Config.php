<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Config extends Model {
	
	protected $table = 'config';
	protected $fillable = array('key', 'value');
	
	public $timestamps = false;
	
}
