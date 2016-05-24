<?php namespace App\Commands;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Buzz, Log;

class SendSms extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue, SerializesModels;

	protected $mobile;
	protected $text;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($mobile, $text)
	{
		$this->mobile = $mobile;
		$this->text = $text;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		Log::info('Sending SMS to ' . $this->mobile . ', content: ' . $this->text);

		$client = new Buzz\Browser();

		$client->post('http://yunpian.com/v1/sms/send.json', [], http_build_query([
			'apikey'=>env('YUNPIAN_APIKEY'),
			'mobile'=>$this->mobile,
			'text'=>$this->text
		]));

	}

}
