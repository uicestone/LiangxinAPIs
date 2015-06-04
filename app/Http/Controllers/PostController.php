<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Post;
use Input, Exception;

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
				$query->where($field, Input::query($field));
			}
		}
		
		if(Input::query('keyword'))
		{
			$query->where('title', 'like', '%' . Input::query('keyword') . '%');
		}
		
		if(Input::query('order_by'))
		{
			$query->orderBy(Input::query('order_by'), Input::query('order') ? Input::query('order') : 'asc');
		}
		
		if(Input::query('page'))
		{
			$per_page = Input::query('per_page') ? Input::query('per_page') : 10;
			$query->skip((Input::query('page') - 1) * $per_page)->take($per_page);
		}
		
		return $query->get()->map(function($post)
		{
			
			$post->addHidden('content', 'likedUsers');
			
			if($post->type !== '活动')
			{
				$post->addHidden(['event_date', 'event_address', 'event_type', 'due_date']);
			}
			else
			{
				$post->has_due_date = $post->has_due_date;
			}
			
			if($post->type !== '课堂')
			{
				$post->addHidden(['class_type']);
			}
			
			if($post->type !== '横幅')
			{
				$post->addHidden(['banner_position']);
			}
			
			$post->liked = $post->liked;

			return $post;
		});
		
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		if(!app()->user)
		{
			throw new Exception('Authentication is required for this action.', 401);
		}
		
		$post = new Post();
		$post->fill(Input::data());
		
		$post->author()->associate(app()->user);
		$post->group()->associate(app()->user->group);
		
		if(Input::data('parent_id'))
		{
			$parent_post = Post::find(Input::data('parent_id'));
			
			if(!$parent_post)
			{
				throw new Exception('Parent post id: ' . Input::data('parent_id') . ' not found', 400);
			}
			
			$post->parent()->associate($parent_post);
		
		}
		
		$post->save();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Post $post
	 * @return Response
	 */
	public function show(Post $post)
	{
		$post->load('likedUsers', 'author', 'poster', 'parent');
		
		$post->comments = $post->comments;
		
		$post->liked = $post->liked;
		
		if($post->type !== '活动')
		{
			$post->addHidden(['event_date', 'event_address', 'event_type', 'due_date']);
		}
		else
		{
			$post->load('attendees');
			$post->has_due_date = $post->has_due_date;
		}

		if($post->type !== '课堂')
		{
			$post->addHidden(['class_type']);
		}
		else
		{
			$post->videos = $post->videos;
			$post->articles = $post->articles;
			$post->attachments = $post->attachments;
		}

		if($post->type !== '横幅')
		{
			$post->addHidden(['banner_position']);
		}

		if(in_array($post->type, ['活动', '课堂']))
		{
			$post->images = $post->images;
		}
		
		return $post;
	}

	/**
	 * Display a post directly
	 */
	public function display(Post $post)
	{
		return $post->content;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  Post  $post
	 * @return Response
	 */
	public function update(Post $post)
	{
		$post->fill(Input::data());
		
		$post->author()->associate(app()->user);
		$post->group()->associate(app()->user->group);
		
		if(Input::data('parent_id'))
		{
			$parent_post = Post::find(Input::data('parent_id'));
			
			if(!$parent_post)
			{
				throw new Exception('Parent post id: ' . Input::data('parent_id') . ' not found', 400);
			}
			
			$post->parent()->associate($parent_post);
		
		}
		
		$post->save();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	public function like(Post $post)
	{
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法收藏该文章', 401);
		}
		
		if($post->likedUsers->contains(app()->user->id))
		{
			throw new Exception('用户已经收藏该文章，无法重复收藏', 409);
		}
		
		$post->likes = $post->likedUsers()->count();
		$post->save();
		
		return $post->likedUsers()->attach(app()->user);
	}
	
	public function unLike(Post $post)
	{
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法取消收藏该文章', 401);
		}
		
		if(!$post->likedUsers->contains(app()->user->id))
		{
			throw new Exception('用户尚未收藏该文章，无法取消收藏', 409);
		}
		
		$post->likes = $post->likedUsers()->count();
		$post->save();
		
		return $post->likedUsers()->detach(app()->user);
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
		
		return $event->attendees()->attach(app()->user);
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
		
		return $event->attendees()->detach(app()->user);
	}

}
