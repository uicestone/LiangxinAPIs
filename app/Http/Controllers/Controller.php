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
		
		if(Input::header('Authorization') && $user = User::where('token', Input::header('Authorization'))->first())
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
