<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[When('dev')]
final class ResetPeakMemory
{
    // A FrankenPHP worker keeps the peak of all its requests: the profiler must show only the peak of this one.
    #[AsEventListener(priority: 4096)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            memory_reset_peak_usage();
        }
    }
}
