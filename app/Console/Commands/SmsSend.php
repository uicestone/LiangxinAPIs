<?php namespace App\Console\Commands;

use App\User, App\Config;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Sms;

class SmsSend extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'sms:send';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '发送短信';

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
		if($this->option('mobile'))
		{
			$mobiles = explode(',', $this->option('mobile'));
			$this->info('向 ' . implode(', ', $mobiles) . ' 发送短信');
		}
		else
		{
			$query = User::query();

			if($this->option('users'))
			{
				$user_ids = explode(',', $this->option('users'));
				$query->whereIn('id', $user_ids);
			}

			if($this->option('user-role'))
			{
				$role = $this->option('user-role');
				$query->where('role', $role);
			}

			if($this->option('user-groups'))
			{
				$group_ids = explode(',', $this->option('user-groups'));
				$query->whereIn('group_id', $group_ids);
			}
			
			if($this->option('user-not-in-quiz'))
			{
				$query->whereNotIn('id', function($query)
				{
					$query->select('user_id')->from('quizzes');
					
					if(is_numeric($this->option('user-not-in-quiz')))
					{
						$query->where('round', $this->option('user-not-in-quiz'));
					}
				});
			}
			
			if($this->option('user-win-round'))
			{
				$query->whereIn('id', Config::get('quiz_round_' . $this->option('user-win-round') . '_winners'));
			}

			if(!$this->option('users') && !$this->option('user-role') && !$this->option('user-groups') && !$this->option('user-not-in-quiz') && !$this->option('user-win-round') && !$this->option('user-all'))
			{
				$this->error('用户筛选错误');
				return;
			}

			$users = $query->get()->filter(function($user)
			{
				return $user->contact;
			});

			$this->info('即将向 ' . implode(',', array_column($users->toArray(), 'name')) . ' 发送短信');

			sleep(5);
			
			$mobiles = $users->map(function($user)
			{
				return $user->contact;
			})
			->toArray();
		}

		$text = $this->option('text');

		$result = Sms::send($mobiles, $text);

		$this->info(json_encode($result, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [

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
			['users', 'u', InputOption::VALUE_OPTIONAL, '筛选用户ID'],
			['user-role', 'r', InputOption::VALUE_OPTIONAL, '筛选用户角色'],
			['user-groups', 'g', InputOption::VALUE_OPTIONAL, '筛选用户组'],
			['user-not-in-quiz', null, InputOption::VALUE_OPTIONAL, '未参加过某轮竞赛'],
			['user-win-round', null, InputOption::VALUE_OPTIONAL, '从某轮竞赛晋级'],
			['user-all', 'a', InputOption::VALUE_NONE, '全体用户'],
			['mobile', 'm', InputOption::VALUE_OPTIONAL, '手机号'],
			['text', 't', InputOption::VALUE_REQUIRED, '文字'],
		];
	}

}
