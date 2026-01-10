<?php

namespace App\Controller;

use App\Entity\Business;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;


final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $featured = [
            [
                'name' => 'Ciach&Style Barbershop',
                'address' => 'Księdza Józefa • 30-004, Kraków',
                'url' => '/business/4',
                'rating' => 5.0,
                'reviews' => 168,
                'image' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=1200&q=80',
                'promoted' => true,
            ],
            [
                'name' => 'Barber SHOP & Tattoo Studio',
                'address' => 'Top Market • 04-920, Piła',
                'url' => '/business/4',
                'rating' => 4.9,
                'reviews' => 220,
                'image' => 'https://images.unsplash.com/photo-1517832207067-4db24a2ae47c?auto=format&fit=crop&w=1200&q=80',
                'promoted' => true,
            ],
            [
                'name' => 'CUT & GO Barbershop',
                'address' => 'ul. Łużycka • 10-406, Warszawa',
                'url' => '/business/4',
                'rating' => 4.9,
                'reviews' => 321,
                'image' => 'https://images.unsplash.com/photo-1517832207067-4db24a2ae47c?auto=format&fit=crop&w=1200&q=80',
                'promoted' => false,
            ],
            [
                'name' => 'Barber Barbershop Skawina',
                'address' => 'ul. Kamienna • 32-050, Skawina',
                'url' => '/business/4',
                'rating' => 4.9,
                'reviews' => 115,
                'image' => 'https://images.unsplash.com/photo-1517832207067-4db24a2ae47c?auto=format&fit=crop&w=1200&q=80',
                'promoted' => true,
            ],
            [
                'name' => 'Studio Urody Nova',
                'address' => 'Centrum • 90-001, Łódź',
                'url' => '/business/4',
                'rating' => 4.8,
                'reviews' => 98,
                'image' => 'https://images.unsplash.com/photo-1517832207067-4db24a2ae47c?auto=format&fit=crop&w=1200&q=80',
                'promoted' => false,
            ],
            [
                'name' => 'zzz test',
                'address' => 'Księdza Józefa • 30-004, Kraków',
                'url' => '/business/4',
                'rating' => 5.0,
                'reviews' => 168,
                'image' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=1200&q=80',
                'promoted' => true,
            ],
        ];

        $slugger = new AsciiSlugger('pl');
        
        $cityNames = [
            'Gliwice','Radom','Poznań','Sosnowiec','Toruń',
            'Rzeszów','Kraków','Lublin','Kielce','Warszawa',
            'Łódź','Gdańsk','Gdynia','Katowice','Opole',
            'Szczecin','Częstochowa','Bydgoszcz','Wrocław','Białystok',
        ];

        $cities = [];

        foreach ($cityNames as $city) {
            $cities[$city] = strtolower($slugger->slug($city)->toString());
        }
        
        $blog = [
            [
                'title' => 'Zabieg kosmetyczny na twarz — co warto wiedzieć?',
                'image' => 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=1200&q=80',
                'href' => '/test/',
            ],
            [
                'title' => 'Karta podarunkowa — idealny prezent last minute',
                'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=1200&q=80',
                'href' => '#',
            ],
            [
                'title' => 'Fryzury do ramion — inspiracje na ten sezon',
                'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&q=80',
                'href' => '#',
            ],
            [
                'title' => 'Masaż: jak wybrać najlepszy dla siebie?',
                'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=1200&q=80',
                'href' => '#',
            ],
        ];

        return $this->render('home/index.html.twig', [
            'featured' => $featured,
            'cities' => $cities,
            'blog' => $blog,
        ]);
    }

    #[Route('/firma/{id}', name: 'business_view')]
    public function businessView(Business $business): Response
    {
        $services = $business->getServices()->filter(fn($s) => $s->isActive());
        $staff = $business->getStaff();

        return $this->render('home/business_view.html.twig', [
            'business' => $business,
            'services' => $services,
            'staff' => $staff,
        ]);
    }
}
