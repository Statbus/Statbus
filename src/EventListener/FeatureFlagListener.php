<?php

namespace App\EventListener;

use App\Attribute\FeatureEnabled;
use App\Service\FeatureFlagService;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FeatureFlagListener
{
    public function __construct(
        private FeatureFlagService $featureFlags
    ) {}

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        [$object, $methodName] = $controller;

        $reflectionMethod = new ReflectionMethod($object, $methodName);
        $reflectionClass = new ReflectionClass($object);

        $methodAttributes = $reflectionMethod->getAttributes(FeatureEnabled::class);
        foreach ($methodAttributes as $attr) {
            $feature = $attr->newInstance()->feature;
            if (!$this->featureFlags->isEnabled($feature)) {
                throw new NotFoundHttpException(); // 404 if disabled
            }
        }

        $classAttributes = $reflectionClass->getAttributes(FeatureEnabled::class);
        foreach ($classAttributes as $attr) {
            $feature = $attr->newInstance()->feature;
            if (!$this->featureFlags->isEnabled($feature)) {
                throw new NotFoundHttpException();
            }
        }
    }
}
