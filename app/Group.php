<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'members', 'avatar', 'description', 'leader', 'contact', 'address'];
	protected $visible = ['id', 'name', 'members', 'avatar', 'leader', 'contact', 'address', 'parent_id'];
	protected $casts = [
		'members'=>'integer'
	];
	
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
	
	public function getNewsAttribute()
	{
		return $this->posts()->whereIn('type', ['公告', '文章'])->orderBy('updated_at', 'desc')->take(5)->get()->map(function($post)
		{
			$post->load('author');
			$post->comments = $post->comments;
			return $post;
		});
	}
	
	public function getImagesAttribute()
	{
		return $this->posts()->where('type', '图片')->orderBy('updated_at', 'desc')->take(2)->get()->map(function($post)
		{
			$post->load('author');
			$post->addVisible('url');
			$post->comments = $post->comments;
			return $post;
		});
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
}
