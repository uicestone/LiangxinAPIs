<?php namespace App\Exceptions;

use Exception, Request;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException'
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
		return parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e)
	{
		if(!env('APP_DEBUG') && Request::wantsJson())
		{
			if($this->isHttpException($e))
			{
				return response()->json(['message'=>$e->getMessage() ? $e->getMessage() : Response::$statusTexts[$e->getStatusCode()], 'code'=>$e->getStatusCode()], $e->getStatusCode())
					->setStatusCode($e->getStatusCode(), json_encode($e->getMessage()));
			}
			else
			{
				return response()->json(['message'=>$e->getMessage(), 'code'=>$e->getCode()], $e->getCode());
			}
		}

		return parent::render($request, $e);
	}

	/**
	 * Render the given HttpException.
	 *
	 * @param  \Symfony\Component\HttpKernel\Exception\HttpException  $e
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function renderHttpException(HttpException $e)
	{
		$status = $e->getStatusCode();

		if(env('APP_DEBUG'))
		{
			return parent::renderHttpException($e);
		}
		else
		{
			return response(['code'=>$status, 'message'=>$e->getMessage()]);
		}
		
	}

}
