<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword, SoftDeletes;

	/**
	 * Append attributes to query when building a query.
	 *
	 * @param  array|string  $attributes
	 * @return $this
	 */
	public function append($attributes)
	{
		if (is_string($attributes)) {
			$attributes = func_get_args();
		}
		
		$this->appends = array_unique(
			array_merge($this->appends, $attributes)
		);
		
		return $this;
	}
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'token', 'contact', 'avatar', 'role', 'last_ip', 'position'];
	protected $visible = ['id', 'name', 'contact', 'avatar', 'position', 'role', 'group', 'department', 'followingGroups', 'likedPosts', 'attendingEvents', 'favoritePosts'];

	protected $dates = ['deleted_at'];
	public $timestamps = false;

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'token'];
	
	public function group()
	{
		return $this->belongsTo('App\Group');
	}
	
	public function department()
	{
		return $this->belongsTo('App\Group');
	}
	
	public function followingGroups()
	{
		return $this->belongsToMany('App\Group', 'group_follow');
	}
	
	public function attendingEvents()
	{
		return $this->belongsToMany('App\Post', 'event_attend');
	}
	
	public function likedPosts()
	{
		return $this->belongsToMany('App\Post', 'post_like');
	}
	
	public function favoritePosts()
	{
		return $this->belongsToMany('App\Post', 'post_favorite');
	}
	
	public function getAvatarAttribute($url)
	{
		if(preg_match('/^http:\/\/|^https:\/\//', $url))
		{
			return $url;
		}

		if($url && \Input::header('Liangxin-Request-From') !== 'admin')
		{
			return (env('CDN_PREFIX') ? env('CDN_PREFIX') : url() . '/') . $url;
		}

		return $url;
	}
	
	public function getAttendStatusAttribute()
	{
		if(!isset($this->pivot))
		{
			return null;
		}
		
		return $this->pivot->status;
	}
	
}
