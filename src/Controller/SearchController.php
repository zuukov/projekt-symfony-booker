<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    #[Route('/szukaj', name: 'app_search', methods: ['GET'])]
    public function search(Request $request): RedirectResponse|Response
    {
        $q = trim((string) $request->query->get('q', ''));

        if ($q === '') {
            return $this->redirectToRoute('app_home');
        }

        $term = mb_strtolower($q);
        $term = preg_replace('~[^a-z0-9ąćęłńóśżź\s-]+~u', '', $term) ?? '';
        $term = preg_replace('~[\s-]+~u', '-', trim($term)) ?? '';
        $term = trim($term, '-');

        if ($term === '') {
            $term = 'wyniki';
        }

        return $this->redirectToRoute('app_search_results', ['term' => $term]);
    }

    #[Route('/szukaj/{term}', name: 'app_search_results', methods: ['GET'], requirements: ['term' => '.+'])]
    public function results(string $term): Response
    {
        // na razie mock, potem dodamy zeby to dzialalo legit
        $queryHuman = str_replace('-', ' ', $term);

        $results = [
            [
                'name' => 'Studio Fryzur Diamond',
                'category' => 'Fryzjer',
                'city' => 'Warszawa',
                'searchScore' => 0.92,
            ],
            [
                'name' => 'Barber King',
                'category' => 'Barber shop',
                'city' => 'Kraków',
                'searchScore' => 0.84,
            ],
        ];

        return $this->render('search/results.html.twig', [
            'term' => $term,
            'q' => $queryHuman,
            'results' => $results,
        ]);
    }
}
