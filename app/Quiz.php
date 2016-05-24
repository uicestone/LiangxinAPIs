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
		$attempts = Quiz::where('user_id', app()->user->id)->where('id', '!=', $this->id)->count();
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

}
