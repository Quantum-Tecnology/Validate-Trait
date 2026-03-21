<?php

declare(strict_types = 1);

namespace QuantumTecnology\ValidateTrait;

trait AutoDataTrait
{
    /**
     * Initialize the Set Schema trait for an instance.
     *
     * @return void
     */
    public function initializeAutoDataTrait()
    {
        $this->setSchema();
    }

    public function setSchema()
    {
        $route = request()->route();

        if (
            $route
            && isset($this->initializedAutoDataTrait)
            && in_array($route->getActionMethod(), $this->initializedAutoDataTrait)
            && class_basename(static::class) === str_replace('Controller', 'Service', class_basename($route->getControllerClass()))
        ) {
            data($this->validate());
        }
    }
}
