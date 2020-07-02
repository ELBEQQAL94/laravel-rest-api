<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;

trait ExceptionTrait {
    public function customException($request, $exception)
    {
        if($this->isModel($exception)) {
            $model = strtolower(class_basename($exception->getModel()));
            return $this->responseException("Does not exists any {$model} with specified ID.", 404);
        }

        if($this->isHttpNotFound($exception)) {
            return $this->responseException('Invalid Request.', 404);
        }

        if($this->isAuthenticationUser($exception)) {
            // check if there is a frontend request
            if($this->isFrontend($request)) {
                return redirect()->guest('login');
            }

            return $this->responseException('You have to logge in for see this content.', 401);
        }

        if($this->isAuthorizationUser($exception)) {
            return $this->responseException("You don't have enough permissions for see this content, please contact your manager. Thanks", 409);
        }

        if($this->methodNotAllowed($exception)) {
            return $this->responseException('method not allowed.', 405);
        }

        if($this->isValidationException($exception)) {

            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if($this->isTokenMismatch($exception)) {
            return redirect()->back()->withInput($request->input());
        }

        if($this->isQueryException($exception)) {
            $errorCode = $exception->errorInfo[1];

            if($errorCode == 1451) {
                return $this->responseException('Cannote remove this resource permanently, It is related to any other resource', $exception->getStatusCode());
            }
        }

        if(config('app.debug')) {
            return parent::render($request, $exception);
        }

        return $this->responseException('Unexpected Exception, Try later.', 500);

    }

    // AuthenticationException: Call whene user Unauthenticated
    public function isAuthenticationUser($exception)
    {
        return $exception instanceof AuthenticationException;
    }

    // AuthorizationException: Call whene user Unauthorized
    public function isAuthorizationUser($exception)
    {
        return $exception instanceof AuthorizationException;
    }

    // ModelNotFoundException: Call whene model or not found
    public function isModel($exception)
    {
        return $exception instanceof ModelNotFoundException;
    }

    // NotFoundHttpException: Call whene request not valid or not exists
    public function isHttpNotFound($exception)
    {
        return $exception instanceof NotFoundHttpException;
    }

    // response error message
    public function responseException($message, $code)
    {
        return response()->json(['errors' => $message, 'code' => $code],$code);
    }

    // HttpException: Call whene HTTP method not allowed for specific model
    public function methodNotAllowed($exception)
    {
        return $exception instanceof HttpException;
    }

    // QueryException: Call whene query not valid or not authorized
    public function isQueryException($exception)
    {
        return $exception instanceof QueryException;
    }

    // check if validation exception
    public function isValidationException ($exception)
    {
        return $exception instanceof ValidationException;
    }

    // check if validation exception
    public function isTokenMismatch ($exception)
    {
        return $exception instanceof TokenMismatchException;
    }

    // response validation errors
    public function convertValidationExceptionToResponse(ValidationException $exception, $request)
    {
        $errors = $exception->validator->errors()->getMessages();
        $returnErrors =  $this->responseException($errors, 422);
        $redirectBack = redirect()->
            back()->
            withInput($request->input())->
            withError($errors);

        // check if there is a frontend request
         if($this->isFrontend($request)) {
            return $request->ajax() ? $returnErrors : $redirectBack;
        }

        return $returnErrors;
    }

    private function isFrontend($request)
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }
}
