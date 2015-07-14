<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use File;
use App\Post, App\Group;

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
	protected $description = 'Create posts from files under public image & attachment folder and attach them to Classes.';

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
			
			if(!$matches)
			{
				continue;
			}
			
			$post = Post::firstOrCreate(['title'=>$matches[3], 'type'=>'附件']);
			$post->url='attachments/' . $matches[2] . '-' . $matches[3] . '.' . $matches[4];
			$parent = Post::where('title', $matches[2])->where('type', '课堂')->first();
			
			if(!$parent)
			{
				$this->error($matches[2] . ' not found');
				continue;
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
//			$this->info('post ' . $post->title . ' saved');
		}
		
		// save image files to image post
		foreach(File::files(public_path('images')) as $file)
		{
			$matches = [];
			preg_match('/(^.*\/)(.*?)-(.*)\.(.*)/', $file, $matches);
			
			if(!$matches)
			{
				continue;
			}
			
			$post = Post::firstOrNew(['title'=>$matches[2] . '-' . $matches[3], 'type'=>'图片']);
			$post->url='images/' . $matches[2] . '-' . $matches[3] . '.' . $matches[4];
			
			$group = Group::where('name', $matches[2])->first();
			$article = Post::where('title', $matches[2])->first();
			
			if($article)
			{
				$post->parent()->associate($article);
				$article->group && $post->group()->associate($article->group);
				$article->author && $post->author()->associate($article->author);
			}
			elseif($group)
			{
				$post->group()->associate($group);
			}
			else
			{
				$this->error($matches[2] . ' is neither a post nor a group');
				continue;
			}
			
			$post->save();
//			$this->info('image ' . $post->title . ' saved');
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
