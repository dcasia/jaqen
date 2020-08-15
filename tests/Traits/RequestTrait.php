<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Traits;

use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\CreateResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\DetailResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\IndexResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use Illuminate\Routing\Route;

trait RequestTrait
{

    /**
     * @param array|string $uri
     * @param string $method
     * @param array $parameters
     * @param string $request
     *
     * @return BaseRequest
     */
    protected function makeRequest($uri, string $method = 'GET', array $parameters = [], string $request = BaseRequest::class): BaseRequest
    {

        if (is_array($uri)) {

            $route = array_key_first($uri);
            $uri = $uri[ $route ];

        }

        /**
         * @var BaseRequest $request
         */
        $request = $request::create($uri, $method, $parameters);

        /**
         * If pass a object like [ '{route}/{bindings}' => '/route/binding' ]
         */
        if (isset($route)) {

            $request->setRouteResolver(static function () use ($route, $method, $request) {
                return (new Route($method, $route, []))->bind($request);
            });

        }

        app()->instance(BaseRequest::class, $request);

        return $request;

    }

    protected function createRequest(string $resourceKey, array $data = []): BaseRequest
    {
        return $this->makeRequest([ '/create/{resource}' => "/create/$resourceKey" ], 'POST', $data, CreateResourceRequest::class);
    }

    protected function updateRequest(string $resourceKey, int $key, array $data = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/{resource}/{key}' => "$resourceKey/$key" ], 'POST', $data, UpdateResourceRequest::class
        );
    }

    protected function indexRequest(string $resourceKey, array $data = []): BaseRequest
    {
        return $this->makeRequest($resourceKey, 'GET', $data, IndexResourceRequest::class);
    }

    protected function detailRequest(string $resourceKey, int $key, array $data = []): BaseRequest
    {
        return $this->makeRequest([ '/{resource}/{key}' => "/$resourceKey/$key" ], 'GET', $data, DetailResourceRequest::class);
    }

    protected function belongsToRequest(string $resourceKey, int $key, string $field, array $data = []): BaseRequest
    {
        return $this->makeRequest([ '/belongs-to/{resource}/{key}/{field}' => "/belongs-to/$resourceKey/$key/$field" ], 'GET', $data, BaseRequest::class);
    }

    protected function blankRequest(): BaseRequest
    {
        return $this->makeRequest('/', 'GET', [], BaseRequest::class);
    }

}
