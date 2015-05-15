<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use App\User;
use Input;

abstract class Controller extends BaseController {

	use DispatchesCommands, ValidatesRequests;
	
	function __construct()
	{
		
		app()->user = null;
		
		if(Input::query('token'))
		{
			$user = User::where('token', Input::query('token'))->first();
			
			if(!$user)
			{
				throw new TokenNotFoundException;
			}
			
			app()->user = $user;
		}
		
	}

}
