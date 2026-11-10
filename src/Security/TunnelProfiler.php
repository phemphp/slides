<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;

#[When('dev')]
final class TunnelProfiler
{
    public function __construct(
        #[Autowire(service: 'profiler')]
        private readonly Profiler $profiler,
    ) {
    }

    #[AsEventListener(priority: 31)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->headers->has('Cf-Connecting-IP')) {
            return;
        }

        $this->profiler->disable();

        // The routes of the dev tools (_profiler, _wdt, _preview_error) start with "_".
        if (str_starts_with((string) $request->attributes->get('_route'), '_')) {
            throw new NotFoundHttpException();
        }
    }
}
