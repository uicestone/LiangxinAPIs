<?php namespace App\Commands;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use App\Sms;

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
		if(is_array($this->mobile))
		{
			foreach(array_chunk($this->mobile, 500) as $mobiles)
			{
				Sms::send(implode($mobiles), $this->text);
			}
		}
		else
		{
			Sms::send($this->mobile, $this->text);
		}
	}

}
