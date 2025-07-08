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
        $controller = $service = request()?->route()?->getController();

        $service = null;
        if(method_exists($controller, 'getService')) {
            $service = $controller->getService();
        }

        if (
            isset($this->initializedAutoDataTrait)
            && filled($service)
            && (is_object($service) ? get_class($service) : $service) === get_class($this)
            && in_array(request()->route()->getActionMethod(), $this->initializedAutoDataTrait)
        ) {
            request()->data('validated', $this->validate());
        }
    }
}
