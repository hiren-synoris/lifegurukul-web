<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CheckBasicDeviceFieldsValidationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $header_data = $request->header();
        $validator_header_data = Validator::make($header_data, [
            'deviceid' => 'required|filled',
            'devicetype' => ['required','filled','min:1', 'max:3'],
            'devicetoken' => 'required|filled'
        ]);

        // Validate the input and return correct response


        if ($validator_header_data->fails())
        {
            return Response::json(array(
                'errors' => $validator_header_data->getMessageBag()->toArray()

            ), 422); // 400 being the HTTP code for an invalid request.
        }

        return $next($request);
    }
}
