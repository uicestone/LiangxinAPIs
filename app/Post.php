<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use QrCode, Hash, File;

class Post extends Model {

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
	protected $fillable = ['type', 'title', 'excerpt', 'meta', 'content', 'url', 'likes', 'event_date', 'event_address', 'event_type', 'class_type', 'banner_position', 'due_date', 'describe'];
	protected $visible = ['id', 'type', 'title', 'updated_at', 'created_at', 'author', 'group', 'comments', 'parent', 'liked', 'is_favorite', 'comments_count'];
	protected $casts = [
		'likes'=>'integer',
		'meta'=>'object'
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
		return $this->belongsTo('App\Post');
	}
	
	public function attendees()
	{
		return $this->belongsToMany('App\User', 'event_attend')->withPivot('status');
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
		return $this->children()->where('type', '评论')->get()->map(function($item)
		{
			$item->load('author');
			return $item;
		});
	}
	
	public function getImagesAttribute()
	{
		return $this->children()->where('type', '图片')->get()->map(function($item)
		{
			$item->comments = $item->comments;
			$item->addVisible('url');
			return $item;
		});
	}
	
	public function getArticlesAttribute()
	{
		return $this->children()->where('type', '文章')->get()->map(function($item)
		{
			$item->load('author');
			return $item;
		});
	}
	
	public function getVideosAttribute()
	{
		return $this->children()->where('type', '视频')->get()->map(function($item)
		{
			$item->load('author');
			$item->addVisible('url', 'excerpt');
			return $item;
		});
	}
	
	public function getAttachmentsAttribute()
	{
		return $this->children()->where('type', '附件')->get()->map(function($item)
		{
			$item->load('author');
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
	
	public function getAttendedAttribute()
	{
		if(!app()->user)
		{
			return null;
		}
		
		$attend_status = null;
		$attended = false;
		
		$this->attendees->each(function($attendee) use (&$attended, &$attend_status)
		{
			if($attendee->id === app()->user->id)
			{
				$attended = true;
				$attend_status = $attendee->pivot->status;
			}
		});
		
		$this->append('attend_status');;
		
		return $attended;
	}
	
	public function getHasDueDateAttribute()
	{
		return (bool) $this->due_date;
	}
	
	public function getUrlAttribute($url)
	{
		
		if($this->type === '活动' && isset($this->poster))
		{
			return $this->poster->url;
		}
		
		if(in_array($this->type, ['图片', '附件', '封面']) && $url && !(\Request::header('Liangxin-Request-From') === 'admin' && app()->user && app()->user->role === 'app_admin'))
		{
			return wholeurlencode(env('CDN_PREFIX') . $url);
		}
		
		return str_contains($url, '%') ? $url : wholeurlencode($url);
	}
	
	public function getExcerptAttribute($excerpt)
	{
		if($this->type === '视频')
		{
			if(json_decode($excerpt))
			{
				return json_decode($excerpt);
			}
			else
			{
				return (object)['high'=>[$this->url], 'normal'=>[$this->url]];
			}
		}
		
		if(!$excerpt)
		{
			return str_limit(strip_tags($this->content), 64);
		}
		
		return str_limit($excerpt, 64);
	}

	public function setExcerptAttribute($excerpt)
	{
		if(!is_string($excerpt))
		{
			$excerpt = json_encode($excerpt);
		}
		$this->attributes['excerpt'] = $excerpt;
	}
	
	public function getCommentsCountAttribute()
	{
		return $this->comments->count();
	}
	
	public function setDescribeAttribute($value)
	{
		$this->attributes['content'] = $value;
	}
	
	public function getQrcodeAttribute()
	{
		if(!isset($this->meta->token))
		{
			$meta = $this->meta ? (array) $this->meta : [];
			$meta['token'] = Hash::make($this->id . ' event_attend');
			$this->meta = (object) $meta;
			$this->save();
		}

		if(!File::exists(public_path('images/event_attend_qr_' . $this->id . '.png')))
		{
			Qrcode::format('png')->size(300)->margin(2)->generate('liangxin://attend/' . $this->id . '/' . $this->meta->token, public_path('images/event_attend_qr_' . $this->id . '.png'));
		}
		
		return url('images/event_attend_qr_' . $this->id . '.png');
		
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
