<?php namespace App;

use Buzz;

class Sms {
	
	public static function send($mobile, $text)
	{
		$client = new Buzz\Browser();

		$response = $client->post('http://yunpian.com/v1/sms/send.json', [], http_build_query([
			'apikey'=>env('YUNPIAN_APIKEY'),
			'mobile'=>$mobile,
			'text'=>$text
		]));
		
		return $response;
	}
	
}
