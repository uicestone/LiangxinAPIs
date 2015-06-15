<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use File, Exception;
use App\Post;

class CreateImagePosts extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'create:image-posts';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create posts from files under public image & attachment folder.';

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
		foreach(File::files(public_path('attachments')) as $file)
		{
			$matches = [];
			preg_match('/(^.*\/)(.*?)-(.*)\.(.*)/', $file, $matches);
			
			$post = Post::firstOrCreate(['title'=>$matches[3], 'type'=>'附件']);
			$post->url='attachments/' . $matches[2] . '-' . $matches[3] . '.' . $matches[4];
			$parent = Post::where('title', $matches[2])->where('type', '课堂')->first();
			
			if(!$parent)
			{
				throw new Exception($matches[2] . ' not found');
			}
			$post->parent()->associate($parent);
			
			if($parent->group)
			{
				$post->group()->associate($parent->group);
			}
			
			if($parent->author)
			{
				$post->author()->associate($parent->author);
			}
			
			$post->save();
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
			['example', InputArgument::REQUIRED, 'An example argument.'],
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
			['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
