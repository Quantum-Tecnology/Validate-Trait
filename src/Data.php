<?php

declare(strict_types = 1);

namespace QuantumTecnology\ValidateTrait;

use Illuminate\Support\Str;
use ReflectionProperty;

#[\AllowDynamicProperties]
class Data
{
    protected bool $populate = false;

    public function __construct(array | object $data = [])
    {
        foreach ($data as $key => $value) {
            $this->populate = true;
            $this->$key     = $value;
        }
    }

    public function __get(string $name): mixed
    {
        return $this->$name ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->$name = $value;
    }

    public function toArray(): array
    {
        $publicProperties = [];

        foreach ($this as $key => $value) {
            $reflection = new ReflectionProperty($this, $key);

            if ($reflection->isPublic()) {
                $publicProperties[$key] = $value;
            }
        }

        return $publicProperties;
    }

    public function toObject(): object
    {
        return json_decode(json_encode($this));
    }

    public function toJson(): string
    {
        return json_encode($this);
    }

    public function toUpperCamelCase(
        array $exception = [],
        bool $excludeException = true,
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
        bool $toUpperCamelCase = false,
    ): mixed {
        $data = $this->toArray();
        $data = $this->arrayKeyUcfirst(
            $data,
            $exception,
            $excludeException
        );

        $result = new self($data);

        return match (true) {
            $toArray          => $result->toArray(),
            $toObject         => $result->toObject(),
            $toJson           => $result->toJson(),
            $toUpperCamelCase => $result->toUpperCamelCase(),
            default           => $result,
        };
    }

    public function check(): bool
    {
        return $this->populate;
    }

    public function merge(array | object $data = []): self
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    public function only(
        array $keys,
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
        bool $toUpperCamelCase = false,
    ): mixed {
        $data = $this->toArray();
        $data = array_filter($data, fn ($key) => in_array($key, $keys), ARRAY_FILTER_USE_KEY);

        $result = new self($data);

        return match (true) {
            $toArray          => $result->toArray(),
            $toObject         => $result->toObject(),
            $toJson           => $result->toJson(),
            $toUpperCamelCase => $result->toUpperCamelCase(),
            default           => $result,
        };
    }

    public function except(
        array $keys,
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
        bool $toUpperCamelCase = false,
    ): mixed {
        $data = $this->toArray();
        $data = array_filter($data, fn ($key) => !in_array($key, $keys), ARRAY_FILTER_USE_KEY);

        $result = new self($data);

        return match (true) {
            $toArray          => $result->toArray(),
            $toObject         => $result->toObject(),
            $toJson           => $result->toJson(),
            $toUpperCamelCase => $result->toUpperCamelCase(),
            default           => $result,
        };
    }

    public function has(string $key): bool
    {
        return isset($this->$key);
    }

    public function isEmpty(): bool
    {
        return empty($this->toArray());
    }

    public function isNotEmpty(): bool
    {
        return !empty($this->toArray());
    }

    public function dd(): void
    {
        dd($this);
    }

    public function dump(): self
    {
        dump($this);

        return $this;
    }

    public function setSegment(
        array $segmentAttributes = [],
        string $segment = 'segments',
    ): object {
        collect($segmentAttributes)->each(function ($value) use ($segment) {
            if (data()->has($value)) {

                if (data()->has($segment)) {
                    data()->$segment[] = [
                        $value => data()->$value,
                    ];
                }

                if (!data()->has($segment)) {
                    data()->$segment = [
                        [$value => data()->$value],
                    ];
                }

                unset(data()->$value);
            }
        });

        return $this;
    }

    public function onlySegment(
        string $segment = 'segments',
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
        bool $toUpperCamelCase = false,
    ): mixed {

        if (!$this->has($segment)) {
            return new self([]);
        }

        $result = new self($this->$segment);

        return match (true) {
            $toArray          => $result->toArray(),
            $toObject         => $result->toObject(),
            $toJson           => $result->toJson(),
            $toUpperCamelCase => $result->toUpperCamelCase(),
            default           => $result,
        };
    }

    public function each(
        callable $callback,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->each($callback);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function map(
        callable $callback,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->map($callback);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function transform(
        callable $callback,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->transform($callback);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function filter(
        callable $callback,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->filter($callback);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function reduce(
        callable $callback,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->reduce($callback);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function reject(
        callable $callback,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->reject($callback);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function partition(
        callable $callback,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->partition($callback);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function pluck(
        string $key,
        ?string $value = null,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->pluck($key, $value);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function sort(
        string $key,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->sort($key);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function sortBy(
        string $key,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->sortBy($key);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function sortByDesc(
        string $key,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->sortByDesc($key);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function sortKeys(
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->sortKeys();

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function sortKeysDesc(
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->sortKeysDesc();

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    public function sortKeysUsing(
        callable $callback,
        array $data = [],
        bool $toArray = false,
        bool $toObject = false,
        bool $toJson = false,
    ): mixed {
        $data = collect($data)->sortKeysUsing($callback);

        $result = new self($data);

        return match (true) {
            $toArray  => $result->toArray(),
            $toObject => $result->toObject(),
            $toJson   => $result->toJson(),
            default   => $result,
        };
    }

    private function arrayKeyUcfirst(
        array | object $data,
        array $exception = [],
        bool $excludeException = true
    ): array {
        $data_modify = [];

        foreach ($data as $key => $value) {
            if (!in_array($key, $exception)) {
                if (is_string($value) || is_numeric($value)) {
                    $data_modify[ucfirst(Str::camel($key))] = $value;
                } elseif (is_array($value)) {
                    $data_modify[ucfirst(Str::camel($key))] = $this->arrayKeyUcfirst($value, $exception);
                }

                continue;
            }

            if (!$excludeException) {
                $data_modify[$key] = $value;

                continue;
            }
        }

        return $data_modify;
    }
}
