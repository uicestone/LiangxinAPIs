<?php namespace App\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Commands\SendSms;
use App\User, App\Group, App\Config;
use Input, Hash, Exception, Log, Response, Queue;

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
		
		if(Input::query('keyword'))
		{
			$query->where('name', 'like', '%' . Input::query('keyword') . '%');
		}
		
		if(Input::query('with_group'))
		{
			$query->with('group');
		}
		
		$page = Input::query('page') ? Input::query('page') : 1;
		
		$per_page = Input::query('per_page') ? Input::query('per_page') : false;
		
		$list_total = $query->count();
		
		if($per_page)
		{
			$query->skip(($page - 1) * $per_page)->take($per_page);
			$list_start = ($page - 1) * $per_page + 1;
			$list_end = ($page - 1) * $per_page + $per_page;
			if($list_end > $list_total)
			{
				$list_end = $list_total;
			}
		}
		else
		{
			$list_start = 1; $list_end = $list_total;
		}
		
		$results = $query->get()->map(function($user)
		{
			if($user->position)
			{
				$user->addVisible('position');
			}
			return $user;
		});
		
		return response($results)->header('Items-Total', $list_total)->header('Items-Start', $list_start)->header('Items-End', $list_end);

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$user = new User();
		return $this->update($user);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  User $user
	 * @return Response
	 */
	public function show(User $user)
	{
		$user->load('group', 'department', 'followingGroups', 'likedPosts', 'attendingEvents', 'favoritePosts');
		$user->likedPosts->map(function($post)
		{
			$post->addVisible('excerpt');
			return $post;
		});
		return $user;
	}
	
	/**
	 * Update the specified resource in storage.
	 *
	 * @param  User $user
	 * @return Response
	 * @todo Need to check user permission
	 */
	public function update(User $user)
	{
		$input_group = Input::data('group');
		
		if($input_group && is_array($input_group) && isset($input_group['id']))
		{
			$previous_group = $user->group;
			$new_group = Group::find($input_group['id']);
		}
		
		$user->fill(Input::data());
		
		if(app()->user && app()->user->role === 'app_admin')
		{
			if(Input::data('group') && $group = Group::find(Input::data('group')['id']))
			{
				$user->group()->associate($group);
			}
			if(Input::data('department') && $department = Group::find(Input::data('department')['id']))
			{
				$user->department()->associate($department);
			}
			if(Input::data('password'))
			{
				$user->password = Input::data('password');
			}
			if(!$user->contact)
			{
				$user->contact = null;
			}
		}
		
		if(Input::data('avatar') instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)
		{
			if(Input::data('avatar')->isValid())
			{
				$file = Input::data('avatar');

				$extension = $file->getClientOriginalExtension();

				if(!$extension){
					throw new Exception('file extended name not resolved', 400);
				}

				$file_store_name = md5($file->getClientOriginalName() . time() . env('APP_KEY')) . '.' . $extension;

				$file->move(public_path('images'), $file_store_name);

				$user->avatar = 'images' . '/' . $file_store_name;
			}
			else
			{
				throw new Exception('Invalid image file', 400);
			}
		}
		
		$user->save();
		
		if(isset($input_group))
		{
			if($previous_group)
			{
				$previous_group->updateMembersCount();
			}
			
			$new_group->updateMembersCount();
		}
		
		return $this->show($user);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  User $user
	 * @return Response
	 */
	public function destroy(User $user)
	{
		$user->delete();
	}
	
	/**
	 * Authenticate a user and generate a token
	 */
	public function authenticate()
	{
		
		Log::info('Logging in: ' . Input::data('username') . ' : ' . Input::data('password'));
		
		if(!Input::data('username'))
		{
			throw new HttpException(400, '请输入用户名');
		}
		
		if(!Input::data('password'))
		{
			throw new HttpException(400, '请输入密码');
		}
		
		$query_user = User::where(function($query)
		{
			$query->where('name', Input::data('username'))->orWhere('contact', Input::data('username'));
		});
		
		if(!$query_user->first())
		{
			throw new HttpException(401, '用户名或联系方式不存在');
		}
		
		$user = $query_user->where('password', Input::data('password'))->first();
		
		if(!$user)
		{
			throw new HttpException(403, '密码错误');
		}

		if(\Route::current()->uri() === 'login')
		{	
			return redirect('admin')->withCookie(cookie('user_id', $user->id));
		}
		else
		{
			if(!$user->token)
			{
				$token = Hash::make($user->name . $user->password . microtime(true));
				$user->token = $token;
				$user->save();
			}
			
			$user->addVisible('token');
			$user->load('group');

			return Response::json($user)->header('Token', $user->token);
		}
	}
	
	public function logout()
	{
		return redirect('login')->withCookie(cookie('user_id', null));
	}
	
	/**
	 * update contact, password, reset password of a user
	 */
	public function updateProfile()
	{
		// update profile
		if(app()->user)
		{
			if(Input::data('contact'))
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

					Queue::push(new SendSms($mobile, '【新城党群】您的验证码是' . $code . '。如非本人操作，请忽略本短信'));
				}
				else
				{
					$code = Input::query('verification_code');
					$config_item = Config::where('key', 'mobile_code_' . $mobile . '_' . $code)->first();

					if(!$config_item)
					{
						throw new Exception('短信验证码错误', 401);
					}

					if($config_item->value->expires_at < time())
					{
						throw new Exception('短信验证码已过期', 401);
					}

					app()->user->contact = $mobile;

					$config_item->delete();
				}				
			}
			
			if(Input::data('avatar') instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)
			{
				if(Input::data('avatar')->isValid())
				{
					$file = Input::data('avatar');

					$extension = $file->getClientOriginalExtension();

					if(!$extension){
						throw new Exception('file extended name not resolved', 400);
					}

					$file_store_name = md5($file->getClientOriginalName() . time() . env('APP_KEY')) . '.' . $extension;

					$file->move(public_path('images'), $file_store_name);

					app()->user->avatar = 'images' . '/' . $file_store_name;
				}
				else
				{
					throw new Exception('Invalid image file', 400);
				}
			}
			
			app()->user->save();
			
			return app()->user;
		}
		
		// reset password
		if(!app()->user && Input::query('contact'))
		{
		
			Log::info('Reseting password for contact: ' . Input::query('contact'));
			
			$mobile = (string) Input::query('contact');
			
			$user = User::where('contact', $mobile)->first();
			
			if(!$user)
			{
				throw new Exception('用户名 ' . $mobile . ' 不存在', 401);
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
				
				Queue::push(new SendSms($mobile, '【新城党群】您的验证码是' . $code . '。如非本人操作，请忽略本短信'));
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
				
				if($config_item->value->expires_at < time())
				{
					throw new Exception('短信验证码已过期', 401);
				}
				
				$user = User::where('contact', $mobile)->first();
				
				$user->password = Input::get('password');
				$user->save();
				
				Log::info('Password resetted to ' . Input::get('password') . ' for user: ' . $user->name);
				
				$config_item->delete();
				return $user;
			}
		}
	}
}
