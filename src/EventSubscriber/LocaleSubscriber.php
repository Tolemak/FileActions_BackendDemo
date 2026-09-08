<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_LOCALES = ['en', 'pl'];

    public function __construct(private readonly string $defaultLocale = 'en')
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        $requestedLocale = $request->query->get('_locale');
        if (\is_string($requestedLocale) && \in_array($requestedLocale, self::SUPPORTED_LOCALES, true)) {
            $session->set('_locale', $requestedLocale);
        }

        $request->setLocale($session->get('_locale', $this->defaultLocale));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Must run before RouterListener (priority 32), so the locale is
            // set from the session even when routing fails (e.g. a 404),
            // otherwise the error page would always render in the default locale.
            KernelEvents::REQUEST => [['onKernelRequest', 100]],
        ];
    }
}
