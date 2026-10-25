<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * The dev server is public through the tunnel: the profiler is only for the local requests.
 *
 * Cloudflare adds the Cf-Connecting-IP header to each request of the tunnel. For these requests,
 * the profiler does not collect (so there is no toolbar), and the pages of the dev tools do not
 * exist (404). The profiler is on again for the next request (Profiler::reset()). Only the dev
 * server has the profiler.
 */
#[When('dev')]
final class TunnelProfiler
{
    public function __construct(
        #[Autowire(service: 'profiler')]
        private readonly Profiler $profiler,
    ) {
    }

    // After the router (32): the route name is the check, not the path (the router decodes it,
    // so /%5Fprofiler is a profiler page too).
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
