<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Input, Hash;

use Illuminate\Http\Request;

class UserController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$query = User::with('group');
		
		if(Input::query('group_id'))
		{
			$query->where('group_id', Input::query('group_id'));
		}
		
		return $query->get();
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
		return User::find($id)->with('group')->get();
	}
	
	/**
	 * Display a post directly
	 */
	public function display($post)
	{
		return $post->content;
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
	
	/**
	 * Authenticate a user and generate a token
	 */
	public function authenticate()
	{
		$user = User::where('name', Input::data('username'))->where('password', Input::data('password'))->first();
		
		if(!$user)
		{
			return;
		}
		
		$token = Hash::make($user->name . $user->password . time());
		$user->token = $token;
		
		$user->save();
		
		return $user;
	}

}
