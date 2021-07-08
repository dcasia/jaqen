<?php

namespace DigitalCreative\Jaqen\Http\Middlewares;

use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use Illuminate\Http\Response;

class Authorize
{

    public function handle(BaseRequest $request, callable $next): Response
    {
        return $request->resourceInstance() ? $next($request) : abort(403);
    }

}