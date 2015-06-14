<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User, App\Config, App\Sms;
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
		$query = User::query();
		
		if(Input::query('group_id'))
		{
			$query->where('group_id', Input::query('group_id'));
		}
		
		return $query->get()->map(function($user)
		{
			if($user->position)
			{
				$user->addVisible('position');
			}
			return $user;
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
	 * @param  User $user
	 * @return Response
	 */
	public function show(User $user)
	{
		$user->load('group', 'followingGroups', 'likedPosts', 'attendingEvents', 'favoritePosts');
		return $user;
	}
	
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  User $user
	 * @return Response
	 */
	public function update(User $user)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  User $user
	 * @return Response
	 */
	public function destroy(User $user)
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
		
		$user->addVisible('token');
		
		return Response::json($user)->header('Token', $user->token);
	}
	
	/**
	 * update contact, password, reset password of a user
	 */
	public function updateProfile()
	{
		// update profile
		if(app()->user && Input::data('contact'))
		{
			$mobile = Input::data('contact');
			
			if(!Input::query('verification_code'))
			{
				// write sms code to db, and send
				$code = floor(rand(1E5, 1E6-1));
				Config::create([
					'key'=>'mobile_code_' . $mobile . '_' . $code,
					'value'=>json_encode(['expires_at'=>time() + 600])
				]);
				
				Sms::send($mobile, '【新城党群】您的验证码是' . $code . '。如非本人操作，请忽略本短信');
			}
			else
			{
				$code = Input::query('verification_code');
				$config_item = Config::where('key', 'mobile_code_' . $mobile . '_' . $code)->first();
				
				if(!$config_item)
				{
					throw new Exception('短信验证码错误', 401);
				}
				
				if(json_decode($config_item->value)->expires_at < time())
				{
					throw new Exception('短信验证码已过期', 401);
				}
				
				app()->user->contact = $mobile;
				app()->user->save();
				
				$config_item->delete();
			}
			
			return app()->user;
		}
		
		// reset password
		if(!app()->user && Input::query('contact'))
		{
			
			$mobile = (string) Input::query('contact');
			
			$user = User::where('contact', $mobile)->first();
			
			if(!$user)
			{
				throw new Exception('用户名不存在', 401);
			}
			
			if($user->contact !== $mobile)
			{
				throw new Exception('用户名和联系方式不匹配', 401);
			}
			
			if(!Input::query('verification_code'))
			{
				// send sms code to contact
				$code = floor(rand(1E5, 1E6-1));
				Config::create([
					'key'=>'mobile_code_' . $mobile . '_' . $code,
					'value'=>json_encode(['expires_at'=>time() + 600])
				]);
				
				Sms::send($mobile, '【新城党群】您的验证码是' . $code . '。如非本人操作，请忽略本短信');
			}
			elseif(Input::get('password'))
			{
				// update password
				$code = Input::query('verification_code');
				$config_item = Config::where('key', 'mobile_code_' . $mobile . '_' . $code)->first();
				
				if(!$config_item)
				{
					throw new Exception('短信验证码错误', 401);
				}
				
				if(json_decode($config_item->value)->expires_at < time())
				{
					throw new Exception('短信验证码已过期', 401);
				}
				
				$user = User::where('contact', $mobile)->first();
				
				$user->password = Input::get('password');
				$user->save();
				
				$config_item->delete();
				return $user;
			}
		}
	}
}
