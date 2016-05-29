<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Input;

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
		$this->app->singleton('user_agent', function()
		{
			if(str_contains(' iOS ', Input::server('HTTP_USER_AGENT')))
			{
				return 'iOS app';
			}
			elseif(str_contains(' Android ', Input::server('HTTP_USER_AGENT')))
			{
				return 'Android app';
			}
			else
			{
				return 'browser';
			}
		});
	}

}
