<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\User;
use Input;

abstract class Controller extends BaseController {

	use DispatchesCommands, ValidatesRequests;
	
	function __construct()
	{
		
		app()->user = null;

		$token = Input::header('Authorization') ?: Input::query('authorization');

		if($token && $user = User::where('token', $token)->first())
		{
			app()->user = $user;

			if(Input::ip() !== $user->last_ip)
			{
				$user->last_ip = Input::ip();
				$user->save();
			}
		}
		
		if(Input::cookie('user_id') && $user = User::where('id', Input::cookie('user_id'))->first())
		{
			app()->user = $user;
			
			if(Input::ip() !== $user->last_ip)
			{
				$user->last_ip = Input::ip();
				$user->save();
			}
		}
		
	}

}
