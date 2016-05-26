<?php namespace App;

use Buzz, Log;

class Sms {
	
	public static function send($mobile, $text)
	{
		if(is_array($mobile))
		{
			$results = [];
			
			foreach(array_chunk($mobile, 500) as $mobiles)
			{
				$response = self::send(implode(',', $mobiles), $text);
				$results[] = $response;
			}
			
			return $results;
		}
		
		Log::info('Sending SMS to ' . $mobile . ', content: ' . $text);
		
		$client = new Buzz\Browser();

		$response = $client->post('http://yunpian.com/v1/sms/send.json', [], http_build_query([
			'apikey'=>env('YUNPIAN_APIKEY'),
			'mobile'=>$mobile,
			'text'=>$text
		]));
		
		return json_decode($response->getContent());
	}
	
}
