<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceLandingController extends AbstractController
{
    private const LANDINGS = [
        'fryzjer' => [
            'label' => 'Fryzjer',
            'template' => 'services/fryzjer.html.twig',
        ],
        'barber-shop' => [
            'label' => 'Barber shop',
            'template' => 'services/barber-shop.html.twig',
        ],
        'salon-kosmetyczny' => [
            'label' => 'Salon kosmetyczny',
            'template' => 'services/salon-kosmetyczny.html.twig',
        ],
        'paznokcie' => [
            'label' => 'Paznokcie',
            'template' => 'services/paznokcie.html.twig',
        ],
        'fizjoterapia' => [
            'label' => 'Fizjoterapia',
            'template' => 'services/fizjoterapia.html.twig',
        ],
        'brwi-i-rzesy' => [
            'label' => 'Brwi i rzęsy',
            'template' => 'services/brwi-i-rzesy.html.twig',
        ],
        'masaz' => [
            'label' => 'Masaż',
            'template' => 'services/masaz.html.twig',
        ],
        'zdrowie' => [
            'label' => 'Zdrowie',
            'template' => 'services/zdrowie.html.twig',
        ],
        'wiecej' => [
            'label' => 'Więcej',
            'template' => 'services/wiecej.html.twig',
        ],
    ];

    #[Route(
        '/usluga/{slug}',
        name: 'app_service_landing',
        requirements: ['slug' => '[a-z0-9\-]+']
    )]
    public function show(string $slug): Response
    {
        $landing = self::LANDINGS[$slug] ?? null;

        if (!$landing) {
            throw new NotFoundHttpException();
        }

        return $this->render($landing['template'], [
            'title' => $landing['label'],
            'slug' => $slug,
            'allServices' => array_map(
                fn (string $s, array $cfg) => ['slug' => $s, 'label' => $cfg['label']],
                array_keys(self::LANDINGS),
                self::LANDINGS
            ),
        ]);
    }
}
