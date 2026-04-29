<?php

declare(strict_types = 1);

namespace QuantumTecnology\ValidateTrait;

use Illuminate\Support\Arr;
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
        $data = self::normalizeToArray($data);
        $data = self::expandDotKeys($data);

        foreach ($data as $key => $value) {
            if (isset($this->$key)) {
                $current = $this->$key;
                // Ambos arrays associativos
                if (is_array($current) && is_array($value) && self::isAssoc($current) && self::isAssoc($value)) {
                    $this->$key = self::arrayMergeAssoc($current, $value);
                }
                // Ambos objetos
                elseif (is_object($current) && is_object($value)) {
                    $this->$key = (new self((array)$current))->merge((array)$value);
                }
                // Caso contrário, sobrescreve
                else {
                    $this->$key = $value;
                }
            } else {
                $this->$key = $value;
            }
        }

        return $this;

    }

    /**
     * Converte objeto/array em array público, removendo chaves de propriedades
     * protected/private (que ao serem cast para array recebem prefixo "\0").
     */
    private static function normalizeToArray(array | object $data): array
    {
        if ($data instanceof self) {
            return $data->toArray();
        }

        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return $data->toArray();
            }
            $data = (array) $data;
        }

        $clean = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && str_contains($key, "\0")) {
                continue;
            }
            $clean[$key] = $value;
        }

        return $clean;
    }

    /**
     * Expande chaves com notação de ponto em arrays aninhados.
     * Ex.: ['a.b' => 1, 'a' => ['c' => 2]] → ['a' => ['b' => 1, 'c' => 2]]
     */
    private static function expandDotKeys(array $data): array
    {
        $expanded = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && str_contains($key, '.')) {
                $nested = [];
                Arr::set($nested, $key, $value);
                $expanded = self::arrayMergeAssoc($expanded, $nested);
            } else {
                if (isset($expanded[$key]) && is_array($expanded[$key]) && is_array($value) && self::isAssoc($expanded[$key]) && self::isAssoc($value)) {
                    $expanded[$key] = self::arrayMergeAssoc($expanded[$key], $value);
                } else {
                    $expanded[$key] = $value;
                }
            }
        }

        return $expanded;
    }

    /**
     * Faz merge profundo apenas de arrays associativos.
     */
    private static function arrayMergeAssoc(array $a, array $b): array
    {
        foreach ($b as $key => $value) {
            if (isset($a[$key]) && is_array($a[$key]) && is_array($value) && self::isAssoc($a[$key]) && self::isAssoc($value)) {
                $a[$key] = self::arrayMergeAssoc($a[$key], $value);
            } else {
                $a[$key] = $value;
            }
        }
        return $a;
    }

    /**
     * Verifica se um array é associativo.
     */
    private static function isAssoc(array $arr): bool
    {
        if ([] === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
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
        return property_exists($this, $key);
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
