<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model {

	protected $fillable = ['questions', 'round', 'score', 'duration'];
	protected $casts = ['round'=>'integer'];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function getQuestionsAttribute($value)
	{
		return json_decode($value);
	}

	public function setQuestionsAttribute($value)
	{
		$this->attributes['questions'] = json_encode($value, JSON_UNESCAPED_UNICODE);
	}
	
	public function getTimeoutAtAttribute()
	{
		return date('Y-m-d H:i:s', $this->created_at->timestamp + Config::get('quiz_round_time_limit')->{$this->round});
	}
	
	public function getAttemptsAttribute()
	{
		$quiz_round_date = Config::get('quiz_round_date');
		$round_start_date = $quiz_round_date[$this->round - 1];
		$attempts = Quiz::where('user_id', app()->user->id)->where('round', $this->round)->where('id', '!=', $this->id)->where('created_at', '>=', $round_start_date)->where('created_at', '<', $this->created_at)->count();
		return $attempts + 1;
	}

	public function getAttemptsAllowedAttribute()
	{
		$quiz_round_attempts_limit = Config::get('quiz_round_attempt_limit');

		if(is_object($quiz_round_attempts_limit) && isset($quiz_round_attempts_limit->{$this->round}))
		{
			return $quiz_round_attempts_limit->{$this->round};
		}
	}

	public function scopeOfCurrentRound($query)
	{
		$quiz_round_date = Config::get('quiz_round_date');

		$round = 1;

		foreach($quiz_round_date as $index => $date)
		{
			if(time() < strtotime($date))
			{
				break;
			}

			$round = $index + 1;
		}

		$round_start_date = $quiz_round_date[$round - 1];

		$query->where('round', $round);
		$query->where('created_at', '>', $round_start_date);
		
		return $query;
	}

}
