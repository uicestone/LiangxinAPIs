<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Group;
use Input;

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
			$item->has_children = $item->has_children;
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
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$group = Group::with('parent', 'posts')->find($id);
		$group->has_children = $group->has_children;
		return $group;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
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

}
