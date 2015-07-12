<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Post, App\Youku;

class ParseVideoUrl extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'parse:video-url';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Parse Youku video urls.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$video_posts = Post::where('type', '视频')->get();
		
		foreach($video_posts as $video_post)
		{
			
			if($video_post->excerpt)
			{
				continue;
			}
			
			$data = Youku::parse($video_post->url);
			$video_post->excerpt = json_encode($data);
			
			$video_post->save();
			$this->info('Video ' . $video_post->title . ' parsed.');
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
//			['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
//			['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
