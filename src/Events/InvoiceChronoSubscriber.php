<?php

namespace App\Events;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvoiceChronoSubscriber implements EventSubscriberInterface
{
    private $invoiceRepo;
    private $security;

    public function __construct(
        InvoiceRepository $invoiceRepo,
        Security $security
    ) {
        $this->invoiceRepo = $invoiceRepo;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                'setChronoForInvoice',
                EventPriorities::PRE_VALIDATE,
            ],
        ];
    }

    public function setChronoForInvoice(ViewEvent $event)
    {
        $invoice = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ($invoice instanceof Invoice && $method === 'POST') {
            $nextChrono = $this->invoiceRepo->findNextChrono(
                $this->security->getUser()
            );

            $invoice->setChrono($nextChrono);
            $invoice->setSentAt(new \DateTime());
        }
    }
}
