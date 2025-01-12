<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class AbstractFilter implements FilterInterface
{
    private array $queryParams;

    public function __construct(array $queryParams)
    {
        $this->queryParams = $queryParams;
    }

    public function apply(Builder $builder): void
    {
        $this->before($builder);

        foreach ($this->getCallbacks() as $name => $callback) {
            if (isset($this->queryParams[$name])) {
                $callback($builder, $this->queryParams[$name]);
            }
        }
    }

    abstract protected function getCallbacks(): array;

    protected function before(Builder $builder)
    {
    }

    /**
     * @return mixed|null
     */
    protected function getQueryParam(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    /**
     * @param string[] $keys
     *
     * @return AbstractFilter
     */
    protected function removeQueryParam(string ...$keys): static
    {
        foreach ($keys as $key) {
            unset($this->queryParams[$key]);
        }

        return $this;
    }
}
