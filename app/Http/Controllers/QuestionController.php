<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Question;
use Input, Response;

class QuestionController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$query = Question::query();
		
		if(Input::query('keyword'))
		{
			$query->where('title', 'like', '%' . Input::query('keyword') . '%');
		}
		
		$page = Input::query('page') ? Input::query('page') : 1;
		
		$per_page = Input::query('per_page') ? Input::query('per_page') : false;
		
		$list_total = $query->count();
		
		if($per_page)
		{
			$query->skip(($page - 1) * $per_page)->take($per_page);
			$list_start = ($page - 1) * $per_page + 1;
			$list_end = ($page - 1) * $per_page + $per_page;
			if($list_end > $list_total)
			{
				$list_end = $list_total;
			}
		}
		else
		{
			$list_start = 1; $list_end = $list_total;
		}
		
		$results = $query->get();
		
		return response($results)->header('Items-Total', $list_total)->header('Items-Start', $list_start)->header('Items-End', $list_end);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$question = new Question();
		return $this->update($question);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Question $question
	 * @return Response
	 */
	public function show($question)
	{
		return $question;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  Question $question
	 * @return Response
	 */
	public function update($question)
	{
		$question->fill(Input::data());
		$question->save();
		return $this->show($question);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Question $question
	 * @return Response
	 */
	public function destroy($question)
	{
		$question->delete();
	}

}
