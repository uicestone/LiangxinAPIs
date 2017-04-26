<?php namespace App\Http\Controllers;

use App\Config;

class WelcomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('welcome');
	}
	
	public function quiz()
	{
		$user = app()->user;
		$token = null;

		if(!$user || !$user->exists)
		{
			abort(401, '请返回，前往“我的账号”，登陆后再进入竞赛');
		}

		if($user && $user->exists)
		{
			$token = $user->token;
		}
		
		$round = 1;
		
		foreach(Config::get('quiz_round_date') as $index => $date)
		{
			if(time() < strtotime($date))
			{
				break;
			}
			
			$round = $index + 1;
		}
		
		return view('quiz', compact('user', 'token', 'round'));
	}
	
	public function poi()
	{
		$user = app()->user;
		$token = null;

		if($user && $user->exists)
		{
			$token = $user->token;
		}

		return view('poi', compact('user', 'token'));
	}

	public function creditPolicy()
	{
		return view('credit_policy', ['site_name'=>Config::where('key', 'site_name')->first()->value, 'credit_policy'=>Config::where('key', 'credit_policy')->first()->value]);
	}
	
	public function admin()
	{
		if(!app()->user)
		{
			return redirect('login');
		}
		
		return view('admin');
	}

	public function login()
	{
		return view('login');
	}
	
}
