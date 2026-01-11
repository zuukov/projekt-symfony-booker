<?php

namespace App\EventSubscriber;

use App\Repository\BusinessRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

class TwigGlobalVariablesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private Security $security,
        private BusinessRepository $businessRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $user = $this->security->getUser();
        $firstBusiness = null;

        if ($user && method_exists($user, 'getRole') && $user->getRole()->value === 'business_owner') {
            $businesses = $this->businessRepository->findBy(['owner' => $user]);
            if (!empty($businesses)) {
                $firstBusiness = $businesses[0];
            }
        }

        $this->twig->addGlobal('firstBusiness', $firstBusiness);
    }
}
