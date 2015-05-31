<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Input, Hash, Exception, Response;

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
		
		if(!Input::data('username'))
		{
			throw new Exception('请输入用户名', 400);
		}
		
		if(!Input::data('password'))
		{
			throw new Exception('请输入密码', 400);
		}
		
		$query_user = User::where(function($query)
		{
			$query->where('name', Input::data('username'))->orWhere('contact', Input::data('username'));
		});
		
		if(!$query_user->first())
		{
			throw new Exception('用户名或联系方式不存在', 401);
		}
		
		$user = $query_user->where('password', Input::data('password'))->first();
		
		if(!$user)
		{
			throw new Exception('密码错误', 401);
		}
		
		$token = Hash::make($user->name . $user->password . microtime(true));
		
		$user->token = $token;
		
		$user->save();
		
		$user->setHidden(array_diff($user->getHidden(), ['token']));
		
		return Response::json($user)->header('Token', $user->token);
	}

}
