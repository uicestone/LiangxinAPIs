<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Post, App\User, App\Group;
use Input, Exception, Log;
use Illuminate\Database\Eloquent\Collection;

class PostController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		
		$query = Post::with('group', 'author', 'poster');
		
		foreach(['type', 'author_id', 'parent_id', 'group_id', 'event_type', 'class_type'] as $field)
		{
			if(Input::query($field))
			{
				if(is_array(Input::query($field)))
				{
					$query->whereIn($field, Input::query($field));
				}
				else
				{
					$query->where($field, Input::query($field));
				}
			}
		}
		
		if(Input::query('keyword'))
		{
			$query->where('title', 'like', '%' . Input::query('keyword') . '%');
		}
		
		if(Input::query('banner_position') && Input::query('type') === '横幅')
		{
			$query->where('banner_position', Input::query('banner_position'));
		}
		
		if(Input::query('liked_user_id'))
		{
			$query->whereHas('likedUsers', function($query)
			{
				return $query->where('user_id', Input::query('liked_user_id'));
			});
		}
		
		if(Input::query('favored_user_id'))
		{
			$query->whereHas('favoredUsers', function($query)
			{
				return $query->where('user_id', Input::query('favored_user_id'));
			});
		}
		
		if(Input::query('attended_user_id'))
		{
			$query->whereHas('attendees', function($query)
			{
				return $query->where('user_id', Input::query('attended_user_id'));
			});
		}
		
		$order_by = Input::query('order_by') ? Input::query('order_by') : 'created_at';

		if(Input::query('order'))
		{
			$order = Input::query('order');
		}
		elseif(in_array($order_by, ['likes', 'created_at', 'updated_at']))
		{
			$order = 'desc';
		}
		
		if($order_by)
		{
			$query->orderBy($order_by, isset($order) ? $order : 'asc');
		}
		
		$page = Input::query('page') ? Input::query('page') : 1;
		
		$per_page = Input::query('per_page') ? Input::query('per_page') : (Input::query('type') === '服务' ? false : 10);
		
		$list_total = $query->count();
		
		if($per_page)
		{
			$query->skip(($page - 1) * $per_page)->take($per_page);
			$list_start = ($page - 1) * $per_page + 1;
			$list_end = ($page - 1) * $per_page + $per_page;
			if($list_end > $list_total)
			{
				$list_end = $list_total;
			}
		}
		else
		{
			$list_start = 1; $list_end = $list_total;
		}
		
		$results = $query->get()->map(function($post)
		{
			
			$post->addVisible('group', 'author');
			
			if($post->type === '活动')
			{
				$post->addVisible(['event_address', 'event_type', 'has_due_date', 'attended', 'attend_status', 'attendee_count']);
				
				if($post->due_date > 0)
				{
					$post->addVisible('due_date');
				}
				
				if($post->event_date > 0)
				{
					$post->addVisible('event_date');
				}
				
				$post->has_due_date = $post->has_due_date;
				$post->attended = $post->attended;
				$post->attendee_count = $post->attendees()->count();
			}
			
			if($post->type === '课堂')
			{
				$post->addVisible(['class_type']);
			}
			
			if($post->type === '横幅')
			{
				$post->addVisible('banner_position');
			}
			
			if(in_array($post->type, ['课堂', '活动', '文章', '服务', '视频']))
			{
				$post->addVisible('excerpt');
			}
			
			if(in_array($post->type, ['课堂', '活动', '文章', '图片']))
			{
				$post->addVisible('likes');
				$post->liked = $post->liked;
				$post->is_favorite = $post->is_favorite;
				$post->comments_count = $post->comments_count;
			}

			if(in_array($post->type, ['横幅', '图片', '视频', '附件']))
			{
				$post->addVisible(['url']);	
			}
			
			if(in_array($post->type, ['横幅', '课堂', '视频', '活动', '服务']))
			{
				$post->addVisible('poster');
				
				if($post->poster)
				{
					$post->poster->addVisible('url');
				}
			}
			
			return $post;
		});
		
		return response($results)->header('Items-Total', $list_total)->header('Items-Start', $list_start)->header('Items-End', $list_end);
		
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		// 对于图片类型，一个请求可以创建多个图片
		if(Input::data('type') === '图片')
		{
			if(Input::data('images') && is_array(Input::data('images')) && Input::data('images')[0] instanceof \Symfony\Component\HttpFoundation\File\UploadedFile && Input::data('images')[0]->isValid())
			{
				$posts = new Collection;
				
				foreach(Input::data('images') as $file)
				{
					$extension = $file->getClientOriginalExtension();
					
					if(!$extension){
						throw new Exception('file extended name not resolved', 400);
					}
					
					\Log::info('上传了文件 ' . $file->getClientOriginalName());
					
					$file_store_name = md5($file->getClientOriginalName() . time() . rand(10000, 99999) . env('APP_KEY')) . '.' . $extension;
					$file->move(public_path('images'), $file_store_name);

					\Log::info('上传的文件被移至 ' . public_path('images') . '/' . $file_store_name);
					
					$file_post = new Post();

					$file_post->fill([
						'title'=>Input::data('title') ?  Input::data('title'): $file->getClientOriginalName(),
						'type'=>'图片',
						'url'=>'images' . '/' . $file_store_name,
					]);

					$file_post->author()->associate(app()->user);

					if(app()->user->group)
					{
						$file_post->group()->associate(app()->user->group);
					}

					if(Input::data('parent'))
					{
						$parent_id = Input::data('parent')['id'];
					}

					if(Input::data('parent_id'))
					{
						$parent_id = Input::data('parent_id');
					}

					if(isset($parent_id))
					{
						$parent_post = Post::find($parent_id);
						
						if(!$parent_post)
						{
							throw new Exception('Parent post id: ' . Input::data('parent_id') . ' not found', 400);
						}

						$file_post->parent()->associate($parent_post);

					}
					
					$file_post->save();
					
					$file_post->addVisible('url');
					
					$posts->push($file_post);
				}
				
				return $posts;
			}
			else
			{
				throw new Exception('Invalid image file', 400);
			}
		}
		
		$post = new Post();
		
		if(!Input::data('type'))
		{
			throw new Exception('请指定文章类型', 400);
		}
		
		if(!Input::data('title'))
		{
			throw new Exception('请指定文章标题', 400);
		}
		
		return $this->update($post);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Post $post
	 * @return Response
	 */
	public function show(Post $post)
	{
		$post->load('author', 'group', 'parent');
		
		if(in_array($post->type, ['文章', '服务']))
		{
			$post->addVisible('content');
		}
		
		if($post->type === '活动')
		{
			$post->addVisible(['event_date', 'event_address', 'event_type', 'due_date', 'has_due_date', 'content', 'excerpt', 'attendees', 'attended', 'attend_status', 'articles', 'images']);
			$post->load('attendees');
			$post->attendees->map(function($item)
			{
				$item->load('group');
				$item->addVisible('attend_status');
				$item->attend_status = $item->pivot->status;
				return $item;
			});
			$post->articles = $post->articles;
			$post->has_due_date = $post->has_due_date;
			$post->attended = $post->attended;
		}

		if($post->type === '课堂')
		{
			$post->addVisible(['class_type', 'videos', 'articles', 'attachments']);
			$post->videos = $post->videos;
			$post->articles = $post->articles;
			$post->attachments = $post->attachments;
		}

		if($post->type === '横幅')
		{
			$post->addVisible('banner_position');
		}
		
		if(in_array($post->type, ['课堂', '活动', '文章', '服务', '视频']))
		{
			$post->addVisible('excerpt');
		}

		if(in_array($post->type, ['课堂', '活动', '文章', '图片']))
		{
			$post->addVisible('likes');
			$post->liked = $post->liked;
			$post->is_favorite = $post->is_favorite;
			$post->comments = $post->comments;
		}

		if(in_array($post->type, ['文章', '课堂', '活动', '视频', '横幅', '服务']))
		{
			$post->load('poster');
			$post->addVisible('poster');
			
			if($post->poster)
			{
				$post->poster->addVisible('url');
			}
		}

		if(in_array($post->type, ['图片', '附件', '视频', '横幅', '活动']))
		{
			$post->addVisible(['url']);
		}
		
		if(in_array($post->type, ['活动', '课堂', '文章', '服务']))
		{
			$post->addVisible('images');
			$post->images = $post->images;
		}
		
		if($post->type === '服务')
		{
			$post->addVisible('class_type');
		}
		
		return $post;
	}

	/**
	 * Display a post directly
	 */
	public function display(Post $post)
	{
		return view('post', compact('post'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  Post  $post
	 * @return Response
	 */
	public function update(Post $post)
	{
		if(!app()->user)
		{
			throw new Exception('Authentication is required for this action.', 401);
		}
		
		Log::info('Updating post, input data: ' . json_encode(Input::data()));
		
		$post->fill(Input::data());
		
		if(app()->user && app()->user->role === 'app_admin')
		{
			if(Input::data('author') && $user = User::find(Input::data('author')['id']))
			{
				$post->author()->associate($user);
			}
			
			if(Input::data('group') && $group = Group::find(Input::data('group')['id']))
			{
				$post->group()->associate($group);
			}
		}
		
		if(!$post->author)
		{
			$post->author()->associate(app()->user);
			if(app()->user->group)
			{
				$post->group()->associate(app()->user->group);
			}
		}
		
		if(Input::data('parent'))
		{
			$parent_id = Input::data('parent')['id'];
		}
		
		if(Input::data('parent_id'))
		{
			$parent_id = Input::data('parent_id');
		}
		
		if(isset($parent_id))
		{
			$parent_post = Post::find($parent_id);
			
			if(!$parent_post)
			{
				throw new Exception('Parent post id: ' . $parent_id . ' not found', 400);
			}
			
			$post->parent()->associate($parent_post);
		}
		
		// upload files and create child posts
		foreach(['images', 'attachments'] as $file_type)
		{
			if(!Input::data($file_type) || !is_array(Input::data($file_type)) || !Input::data($file_type)[0]->isValid())
			{
				break;
			}
			
			foreach(Input::data($file_type) as $file)
			{
				$file_store_name = md5($file->getClientOriginalName() . time() . env('APP_KEY')) . '.' . $file->getClientOriginalExtension();
				$file->move(public_path($file_type), $file_store_name);
				
				$file_post = new Post();
				
				$file_post->fill([
					'title'=>$file->getClientOriginalName(),
					'type'=>$file_type === 'images' ? '图片' : '附件',
					'url'=>$file_type . '/' . $file_store_name,
				]);
				
				$file_post->parent()->associate($post);
				$file_post->author()->associate(app()->user);
				
				if(app()->user->group)
				{
					$file_post->group()->associate(app()->user->group);
				}
				
				$file_post->save();
			}
		}
		
		if(Input::data('file') && Input::data('file') instanceof \Symfony\Component\HttpFoundation\File\UploadedFile && Input::data('file')->isValid())
		{
			$file = Input::data('file');
			$path = preg_match('/^image\//', $file->getMimeType()) ? 'images' : 'attachments';
			
			$extension = $file->getClientOriginalExtension();

			if(!$extension){
				throw new Exception('file extended name not resolved', 400);
			}
			
			$file_store_name = md5($file->getClientOriginalName() . time() . env('APP_KEY')) . '.' . $extension;
			$file->move(public_path($path), $file_store_name);

			$post->url = $path . '/' . $file_store_name;
		}
		
		if(Input::data('poster') instanceof \Symfony\Component\HttpFoundation\File\UploadedFile && Input::data('poster')->isValid())
		{
			
			$file = Input::data('poster');
			
			$file_store_name = md5($file->getClientOriginalName() . time() . env('APP_KEY')) . '.' . $file->getClientOriginalExtension();
			$file->move(public_path('images'), $file_store_name);

			if(!$post->poster)
			{
				$file_post = new Post();

				$file_post->fill([
					'title'=>$file->getClientOriginalName(),
					'type'=>'封面',
					'url'=>'images' . '/' . $file_store_name,
				]);

				$file_post->author()->associate(app()->user);

				if(app()->user->group)
				{
					$file_post->group()->associate(app()->user->group);
				}

				$file_post->save();
				
				$post->poster_id = $file_post->id;
			}
			else
			{
				$post->poster->fill([
					'title'=>$file->getClientOriginalName(),
					'url'=>'images' . '/' . $file_store_name,
				]);
				
				$post->poster->save();
			}
			
		}

		$post->save();
		
		return $this->show($post);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Post  $post
	 * @return Response
	 */
	public function destroy($post = null)
	{
		if(is_null($post) && Input::query('id'))
		{
			$ids = Input::query('id');
			
			if(!is_array($ids))
			{
				$ids = [$ids];
			}
			
			$posts = Post::whereIn('id', $ids)->get();
			
			$posts->each(function($post)
			{
				try{
					$this->destroy($post);
				}
				catch(Exception $e)
				{
					
				}
			});
			
			return;
		}
		
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法删除该文章', 401);
		}

		if(!app()->user->role == 'app_admin' && !($post->author && app()->user->id === $post->author->id))
		{
			throw new Exception('用户不是文章的作者，无权删除该文章', 403);
		}
		
		try{
			$post->delete();
		}
		catch(\Illuminate\Database\QueryException $e)
		{
			if($e->getCode() === '23000')
			{
				throw new Exception('该文章是其他文章的上级文章，无法删除', 400);
			}
		}
	}
	
	public function like(Post $post)
	{
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法点赞该文章', 401);
		}
		
		if($post->likedUsers->contains(app()->user->id))
		{
			throw new Exception('用户已经点赞该文章，无法重复点赞', 409);
		}

		$post->likedUsers()->attach(app()->user);
		
		$post->likes = $post->likedUsers()->count();
		$post->save();
		
		return ['success' => true];
	}
	
	public function unLike(Post $post)
	{
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法取消点赞该文章', 401);
		}
		
		if(!$post->likedUsers->contains(app()->user->id))
		{
			throw new Exception('用户尚未点赞该文章，无法取消点赞', 409);
		}
		
		$post->likedUsers()->detach(app()->user);
		
		$post->likes = $post->likedUsers()->count();
		$post->save();
		
		return ['success' => true];
	}

	public function favorite(Post $post)
	{
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法收藏该文章', 401);
		}
		
		if($post->favoredUsers->contains(app()->user->id))
		{
			throw new Exception('用户已经收藏该文章，无法重复收藏', 409);
		}
		
		$post->favoredUsers()->attach(app()->user);
		
		return ['success' => true];
	}
	
	public function unFavorite(Post $post)
	{
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法取消收藏该文章', 401);
		}
		
		if(!$post->likedUsers->contains(app()->user->id))
		{
			throw new Exception('用户尚未收藏该文章，无法取消收藏', 409);
		}
		
		$post->likedUsers()->detach(app()->user);
		
		return ['success' => true];
	}

	public function attend(Post $event)
	{
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法参与该活动', 401);
		}
		
		if($event->attendees->contains(app()->user->id))
		{
			throw new Exception('用户已经参与该活动，无法重复参与', 409);
		}
		
		$event->attendees()->attach(app()->user, ['status'=>'pending']);
		
		return ['success' => true];
	}
	
	public function unAttend(Post $event)
	{
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法取消参与该活动', 401);
		}
		
		if(!$event->attendees->contains(app()->user->id))
		{
			throw new Exception('用户尚未参与该活动，无法取消参与', 409);
		}
		
		$event->attendees()->detach(app()->user);
		
		return ['success' => true];
	}
	
	public function attendeeApproval(Post $post, User $user)
	{
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法批准活动参与者', 401);
		}
		
		if(app()->user->id !== $post->author->id)
		{
			throw new Exception('不是活动发起人，无权批准活动参与者', 403);
		}
		
		if(Input::data('status'))
		{
			$status = Input::data('status');
		}
		else
		{
			$status = Input::data('approved') ? 'approved' : 'rejected';
		}
		
		$post->attendees()->updateExistingPivot($user->id, ['status'=>$status]);
		return ['success' => true];
	}

}
