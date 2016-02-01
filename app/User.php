<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'token', 'contact', 'avatar', 'role', 'last_ip', 'position'];
	protected $visible = ['id', 'name', 'contact', 'avatar', 'position', 'role', 'group', 'department', 'followingGroups', 'likedPosts', 'attendingEvents', 'favoritePosts'];

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
			return (env('QINIU_HOST') ? env('QINIU_HOST') : url() . '/') . $url;
		}

		return $url;
	}
	
}
