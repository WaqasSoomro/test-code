<?php
namespace App\Exceptions;
use App\Helpers\Helper;
use ErrorException;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Exception $exception
     * @return void
     *
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception))
        {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Exception $exception
     * @return Response
     *
     * @throws Exception
     */
    public function render($request, Exception $exception)
    {
        if ($request->is("api/*"))
        {
            if ($exception instanceof AuthenticationException)
            {
                return Helper::apiUnAuthenticatedResponse(false, 'Unauthenticated user to access this route', new stdClass());
            }
            else if ($exception instanceof ValidationException)
            {
                return Helper::apiInvalidParamResponse(false, 'Invalid Parameters', $exception->errors());
            }
            else if ($exception instanceof UnauthorizedException)
            {
                return Helper::response(false, 403, 'Unauthorized user to access this route', new stdClass());
            }
            else if ($exception instanceof NotFoundHttpException)
            {
                return Helper::apiNotFoundResponse(false, 'Invalid Route', new stdClass());
            }
            else if ($exception instanceof MethodNotAllowedHttpException)
            {
                return Helper::apiInvalidReqMethodResponse(false, 'Invalid Request Method GET or POST', new stdClass());
            }
            else if ($exception instanceof ThrottleRequestsException)
            {
                return Helper::response(false, 429, 'Too Many Requests', new stdClass());
            }
        }

        return parent::render($request, $exception);
    }
}