<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'members', 'avatar', 'description', 'leader', 'contact', 'address'];
	
	public function parent()
	{
		return $this->belongsTo('App\Group');
	}
	
	public function children()
	{
		return $this->hasMany('App\Group', 'parent_id');
	}
	
	public function posts()
	{
		return $this->hasMany('App\Post');
	}

	public function getHasChildrenAttribute()
	{
		return (bool)count($this->children);
	}

}
