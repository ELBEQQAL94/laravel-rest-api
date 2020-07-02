<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Validation\ValidationException;

class TransformInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $transformer)
    {
        $transformedInput = [];

        foreach($request->request->all() as $input => $value) {
            $transformedInput[$transformer::originalAttribute($input)] = $value;
        }

        // replace all request inputs to original one
        $request->replace($transformedInput);

        $response = $next($request);

        $exceptionError = $response->exception;

        // check if response is equal to errors
        if(isset($exceptionError) && $exceptionError instanceof ValidationException) {
            $data = $response->getData();

            $transformedErrors = [];

            foreach($data->errors as $field => $value) {
                $transformedField = $transformer::transformAttribute($field);
                $transformedErrors[$transformedField] = str_replace($field, $transformedField, $value);
            }

            $data->errors = $transformedErrors;
            $response->setData($data);
        }

        return $response;
    }
}
