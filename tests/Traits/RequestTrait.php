<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Traits;

use DigitalCreative\Dashboard\Http\Controllers\FieldsController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\DeleteController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\IndexController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\StoreController;
use DigitalCreative\Dashboard\Http\Controllers\Resources\UpdateController;
use DigitalCreative\Dashboard\Http\Requests\BaseRequest;
use DigitalCreative\Dashboard\Http\Requests\DeleteResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\DetailResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\FieldsResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\IndexResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\StoreResourceRequest;
use DigitalCreative\Dashboard\Http\Requests\UpdateResourceRequest;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use Illuminate\Routing\Route;
use Illuminate\Testing\TestResponse;

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

    protected function callStore(AbstractResource $resource, array $data = [], array $query = []): TestResponse
    {
        $query = http_build_query($query);
        $resourceUriKey = $resource::uriKey();

        if (filled($query)) {
            $query = "?$query";
        }

        return $this->postJson("/dashboard-api/{$resourceUriKey}{$query}", $data);
    }

    public function indexResponse(AbstractResource $resource, array $data = [], array $query = []): array
    {
        return (new IndexController())->handle($this->indexRequest($resource, $data, $query))->getData(true);
    }

    public function storeResponse(AbstractResource $resource, array $data = [], array $query = []): array
    {
        return (new StoreController())->handle($this->storeRequest($resource, $data, $query))->getData(true);
    }

    public function updateResponse(AbstractResource $resource, int $key, array $data = [], array $query = []): array
    {
        return (new UpdateController())->handle($this->updateRequest($resource, $key, $data, $query))->getData(true);
    }

    public function detailResponse(AbstractResource $resource, int $key, array $data = [], array $query = []): array
    {
        return (new UpdateController())->handle($this->detailRequest($resource, $key, $data, $query))->getData(true);
    }

    public function deleteResponse(AbstractResource $resource, array $keys, array $data = [], array $query = []): array
    {
        return (new DeleteController())->handle($this->deleteRequest($resource, $keys, $data, $query))->getData(true);
    }

    public function fieldsResponse(AbstractResource $resource, array $data = [], array $query = []): array
    {
        return (new FieldsController())->fields($this->fieldsRequest($resource, $data, $query))->getData(true);
    }

}
