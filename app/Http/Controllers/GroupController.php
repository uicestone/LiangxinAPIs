<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Group;
use Input, Exception;

class GroupController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$query = Group::query();
		
		if(Input::query('parent'))
		{
			$query->where('parent_id', Input::query('parent'));
		}
		
		if(Input::query('keyword'))
		{
			$query->where('name', 'like', '%' . Input::query('keyword') . '%');
		}
		
		return $query->get(['id', 'name', 'members', 'avatar', 'leader', 'contact', 'address', 'parent_id'])->map(function($item)
		{
			$item->addVisible('has_children', 'following');
			$item->has_children = $item->has_children;
			$item->following = $item->following;
			return $item;
		});
		
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Group $group
	 * @return Response
	 */
	public function show(Group $group)
	{
		$group->load('parent', 'posts');
		$group->addVisible('parent', 'has_children', 'images', 'news');
		$group->has_children = $group->has_children;
		$group->news = $group->news;
		$group->images = $group->images;
		return $group;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  Group $group
	 * @return Response
	 */
	public function update(Group $group)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Group $group
	 * @return Response
	 */
	public function destroy(Group $group)
	{
		//
	}
	
	public function follow(Group $group)
	{
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法关注该群组', 401);
		}
		
		if($group->followedUsers->contains(app()->user->id))
		{
			throw new Exception('用户已经关注该群组，无法重复关注', 409);
		}
		
		return $group->followedUsers()->attach(app()->user);
	}
	
	public function unFollow(Group $group)
	{
		if(!app()->user)
		{
			throw new Exception('用户没有登录，无法取消关注该群组', 401);
		}
		
		if(!$group->followedUsers->contains(app()->user->id))
		{
			throw new Exception('用户尚未关注该群组，无法取消关注', 409);
		}
		
		return $group->followedUsers()->detach(app()->user);
	}

}
