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
		if($this->isHttpException($e))
		{
			return $this->renderHttpException($e);
		}
		else
		{
			if(config('app.debug'))
			{
				return parent::render($request, $e);
			}
			else
			{
				$status = 503;
				$message = '服务器好像出了一些问题';
				return response(['message'=>$message, 'code'=>$status])
					->setStatusCode($status, json_encode($message));
			}
		}


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
		$message = $e->getMessage() ? $e->getMessage() : Response::$statusTexts[$e->getStatusCode()];

		if(Request::wantsJson())
		{
			return response(['message'=>$message, 'code'=>$status])
				->setStatusCode($status, json_encode($message));
		}
		else
		{
			if (view()->exists("errors.{$status}"))
			{
				return response()->view("errors.{$status}", ['code'=>$status, 'message'=>$message], $status);
			}
			else
			{
				return (new SymfonyDisplayer(config('app.debug')))->createResponse($e);
			}
		}
		
	}

}
