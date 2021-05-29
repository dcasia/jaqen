<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Traits;

use DigitalCreative\Jaqen\Http\Requests\BaseRequest;
use DigitalCreative\Jaqen\Http\Requests\FieldsResourceRequest;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\DeleteResourceRequest;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\DetailResourceRequest;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\IndexResourceRequest;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\StoreResourceRequest;
use DigitalCreative\Jaqen\Services\ResourceManager\Http\Requests\UpdateResourceRequest;
use Illuminate\Routing\Route;

trait RequestTrait
{

    protected function makeRequest(
        array|string $uri,
        string $method = 'GET',
        array $parameters = [],
        array $query = [],
        string $request = BaseRequest::class
    ): BaseRequest
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

            $request->setRouteResolver(static function () use ($route, $method, $request) {
                return (new Route($method, $route, []))->bind($request);
            });

        }

        app()->instance(BaseRequest::class, $request);

        return $request;

    }

    protected function storeRequest(AbstractResource $resource, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/{resource}' => $resource::uriKey() ], 'POST', $data, $query, StoreResourceRequest::class
        );
    }

    protected function fieldsRequest(AbstractResource $resource, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/{resource}/fields' => "/{$resource::uriKey()}/fields" ], 'GET', $data, $query, FieldsResourceRequest::class
        );
    }

    protected function updateRequest(AbstractResource $resource, int $key, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/{resource}/{key}' => "{$resource::uriKey()}/$key" ], 'POST', $data, $query, UpdateResourceRequest::class
        );
    }

    protected function deleteRequest(AbstractResource $resource, array $keys, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/{resource}' => $resource::uriKey() ], 'DELETE', array_merge($data, [ 'ids' => $keys ]), $query, DeleteResourceRequest::class
        );
    }

    protected function indexRequest(AbstractResource $resource, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/{resource}' => $resource::uriKey() ], 'GET', $data, $query, IndexResourceRequest::class
        );
    }

    protected function detailRequest(AbstractResource $resource, int $key, array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest(
            [ '/{resource}/{key}' => "/{$resource::uriKey()}/$key" ], 'GET', $data, $query, DetailResourceRequest::class
        );
    }

    protected function blankRequest(array $data = [], array $query = []): BaseRequest
    {
        return $this->makeRequest('/', 'GET', $data, $query, BaseRequest::class);
    }

}
