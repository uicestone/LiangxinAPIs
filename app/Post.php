<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['type', 'title', 'excerpt', 'content', 'event_date', 'event_address', 'event_type', 'class_type', 'banner_position', 'due_date', 'url', 'likes'];
	protected $visible = ['id', 'type', 'title', 'excerpt', 'content', 'url', 'likes', 'likedUsers', 'attendedUsers', 'children', 'comments', 'images', 'videos', 'attachments', 'articles', 'updated_at', 'created_at'];
	
	public function parent()
	{
		return $this->belongsTo('App\Post');
	}
	
	public function children()
	{
		return $this->hasMany('App\Post', 'parent_id');
	}

	public function author()
	{
		return $this->belongsTo('App\User');
	}
	
	public function group()
	{
		return $this->belongsTo('App\Group');
	}
	
	public function poster()
	{
		return $this->hasOne('App\Post', 'id', 'poster_id');
	}
	
	public function attendedUsers()
	{
		return $this->belongsToMany('App\User', 'event_attend');
	}
	
	public function likedUsers()
	{
		return $this->belongsToMany('App\User', 'post_like');
	}
	
	public function getCommentsAttribute()
	{
		return $this->children()->where('type', '评论')->get();
	}
	
	public function getImagesAttribute()
	{
		return $this->children()->where('type', '图片')->get();
	}
	
	public function getArticlesAttribute()
	{
		return $this->children()->where('type', '文章')->get();
	}
	
	public function getVideosAttribute()
	{
		return $this->children()->where('type', '视频')->get();
	}
	
	public function getAttachmentsAttribute()
	{
		return $this->children()->where('type', '附件')->get();
	}
	
	public function getLikedAttribute()
	{
		if(!app()->user)
		{
			return false;
		}
		
		return $this->likedUsers->contains(app()->user);
	}
	
}
