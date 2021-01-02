<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Services\Scaffold\Http\Controllers;

use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Jaqen;
use DigitalCreative\Jaqen\Services\Scaffold\SidebarService;
use Illuminate\Routing\Controller;

class ScaffoldController extends Controller
{

    public function sidebar(BaseRequest $request, Jaqen $jaqen)
    {
        return $jaqen->invokeProviderMethod('scaffold');
    }

}
