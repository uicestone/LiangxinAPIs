<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['type', 'title', 'excerpt', 'content', 'event_date', 'event_address', 'event_type', 'class_type', 'banner_position', 'due_date', 'url', 'likes'];
	protected $visible = ['id', 'type', 'title', 'author_id', 'likes', 'updated_at', 'created_at'];
	protected $casts = [
		'likes'=>'integer'
	];

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
	
	public function attendees()
	{
		return $this->belongsToMany('App\User', 'event_attend');
	}
	
	public function likedUsers()
	{
		return $this->belongsToMany('App\User', 'post_like');
	}
	
	public function favoredUsers()
	{
		return $this->belongsToMany('App\User', 'post_favorite');
	}
	
	public function getCommentsAttribute()
	{
		return $this->children()->where('type', '评论')->get();
	}
	
	public function getImagesAttribute()
	{
		return $this->children()->where('type', '图片')->get()->map(function($item)
		{
			$item->addVisible('url');
			return $item;
		});
	}
	
	public function getArticlesAttribute()
	{
		return $this->children()->where('type', '文章')->get()->map(function($item)
		{
			$item->addVisible('content');
			return $item;
		});
	}
	
	public function getVideosAttribute()
	{
		return $this->children()->where('type', '视频')->get()->map(function($item)
		{
			$item->addVisible('url');
			return $item;
		});
	}
	
	public function getAttachmentsAttribute()
	{
		return $this->children()->where('type', '附件')->get()->map(function($item)
		{
			$item->addVisible('url');
			return $item;
		});
	}
	
	public function getLikedAttribute()
	{
		if(!app()->user)
		{
			return null;
		}
		
		return $this->likedUsers->contains(app()->user);
	}
	
	public function getIsFavoriteAttribute()
	{
		if(!app()->user)
		{
			return null;
		}
		
		return $this->favoredUsers->contains(app()->user);
	}
	
	public function getHasDueDateAttribute()
	{
		return (bool) $this->due_date;
	}
	
	public function getUrlAttribute($url)
	{
		if(in_array($this->type, ['图片', '附件']))
		{
			return env('QINIU_HOST') . $url;
		}
		
		return $url;
	}
	
}
