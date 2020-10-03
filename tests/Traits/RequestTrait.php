<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Traits;

use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\BelongsToResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\CreateResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\DetailResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\IndexResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\StoreResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use Illuminate\Routing\Route;

trait RequestTrait
{

    /**
     * @param array|string $uri
     * @param string $method
     * @param array $parameters
     * @param array $query
     * @param string $request
     *
     * @return BaseRequest
     */
    protected function makeRequest($uri, string $method = 'GET', array $parameters = [], array $query = [], string $request = BaseRequest::class): BaseRequest
    {

        if (is_array($uri)) {

            $route = array_key_first($uri);
            $uri = $uri[$route];

        }

        $query = http_build_query($query);

        if (filled($query)) {

            $query = "?$query";

        }

        /**
         * @var BaseRequest $request
         */
        $request = $request::create($uri . $query, $method, $parameters);

        /**
         * If pass a object like [ '{route}/{bindings}' => '/route/binding' ]
         */
        if (isset($route)) {

            $request->setRouteResolver(static function() use ($route, $method, $request) {
                return (new Route($method, $route, []))->bind($request);
            });

        }

        app()->instance(BaseRequest::class, $request);

        return $request;

    }

    protected function storeRequest(string $resourceKey, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/{resource}' => "/$resourceKey" ], 'POST', $data, $query, StoreResourceRequest::class
        );
    }

    protected function createRequest(string $resourceKey, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/{resource}/create' => "/$resourceKey/create" ], 'GET', $data, $query, CreateResourceRequest::class
        );
    }

    protected function updateRequest(string $resourceKey, int $key, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/{resource}/{key}' => "$resourceKey/$key" ], 'POST', $data, $query, UpdateResourceRequest::class
        );
    }

    protected function indexRequest(string $resourceKey, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest($resourceKey, 'GET', $data, $query, IndexResourceRequest::class);
    }

    protected function detailRequest(string $resourceKey, int $key, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/{resource}/{key}' => "/$resourceKey/$key" ], 'GET', $data, $query, DetailResourceRequest::class
        );
    }

    protected function belongsToRequest(string $resourceKey, int $key, string $field, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/belongs-to/{resource}/{key}/{field}' => "/belongs-to/$resourceKey/$key/$field" ], 'GET', $data, $query, BelongsToResourceRequest::class
        );
    }

    protected function blankRequest(): BaseRequest
    {
        return $this->makeRequest('/', 'GET', [], [], BaseRequest::class);
    }

}
