<?php

namespace Plugin\SEOAllOne\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class RedirectExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof \Plugin\SEOAllOne\Exception\RedirectException) {
            $event->setResponse($event->getException()->getRedirectResponse());
        }
    }
}