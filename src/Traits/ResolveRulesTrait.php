<?php

namespace DigitalCreative\Dashboard\Traits;


use DigitalCreative\Dashboard\Http\Requests\BaseRequest;

trait ResolveRulesTrait
{

    /**
     * @var null|callable|array
     */
    private $rules;

    /**
     * @var null|callable|array
     */
    private $createRules;

    /**
     * @var null|callable|array
     */
    private $updateRules;

    /**
     * @param array|callable $rules
     *
     * @return $this
     */
    public function rules($rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @param array|callable $rules
     *
     * @return $this
     */
    public function rulesForCreate($rules): self
    {
        $this->createRules = $rules;

        return $this;
    }

    /**
     * @param array|callable $rules
     *
     * @return $this
     */
    public function rulesForUpdate($rules): self
    {
        $this->updateRules = $rules;

        return $this;
    }

    public function resolveRules(BaseRequest $request): ?array
    {

        $rules = $this->rules;

        if ($request->isCreate()) {
            $rules = $this->createRules ?? $this->rules;
        } else if ($request->isUpdate()) {
            $rules = $this->updateRules ?? $this->rules;
        }

        return is_callable($rules) ? $rules($request) : (array) $rules;

    }

}
