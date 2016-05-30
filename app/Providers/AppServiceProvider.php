<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Input, Log;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('from_admin', function()
		{
			return Input::header('Liangxin-Request-From') === 'admin' || app()->runningInConsole();
		});
		
		$this->app->singleton('user_agent', function()
		{
			if(str_contains(Input::server('HTTP_USER_AGENT'), 'iPhone'))
			{
				$ua = 'iOS app';
			}
			elseif(str_contains(Input::server('HTTP_USER_AGENT'), 'Android'))
			{
				$ua = 'Android app';
			}
			else
			{
				$ua = 'browser';
			}
			
			return $ua;
		});
	}

}
