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
	
	public function followedUsers()
	{
		return $this->belongsToMany('App\User', 'group_follow');
	}

	public function getHasChildrenAttribute()
	{
		return (bool)$this->children()->count();
	}
	
	public function getFollowingAttribute()
	{
		if(!app()->user)
		{
			return null;
		}
		
		return $this->followedUsers->contains(app()->user);
	}

	public function getAvatarAttribute($url)
	{
		return env('QINIU_HOST') . $url;
	}
}
