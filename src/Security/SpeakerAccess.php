<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SpeakerAccess
{
    private const string SESSION_KEY = 'speaker';

    public function __construct(
        #[Autowire('%env(SPEAKER_KEY)%')]
        private readonly string $key,
        private readonly RequestStack $requests,
    ) {
    }

    #[AsEventListener(priority: 8)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !str_starts_with($request->getPathInfo(), '/speaker')) {
            return;
        }

        $key = $request->query->getString('key');
        if ('' !== $this->key && '' !== $key && hash_equals($this->key, $key)) {
            $request->getSession()->migrate(true);
            $request->getSession()->set(self::SESSION_KEY, true);
            $event->setResponse(new RedirectResponse($request->getBaseUrl().$request->getPathInfo()));

            return;
        }

        if (!$this->granted()) {
            throw new NotFoundHttpException();
        }
    }

    public function granted(): bool
    {
        $request = $this->requests->getMainRequest();

        return '' !== $this->key
            && null !== $request
            && $request->hasPreviousSession()
            && true === $request->getSession()->get(self::SESSION_KEY);
    }
}
