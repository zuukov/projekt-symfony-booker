<?php

namespace App\DataFixtures;

use App\Entity\ServiceCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ServiceCategoryFixtures extends Fixture
{
    private const CATEGORIES = [
        [
            'slug' => 'fryzjer',
            'name' => 'Fryzjer',
        ],
        [
            'slug' => 'barber-shop',
            'name' => 'Barber shop',
        ],
        [
            'slug' => 'salon-kosmetyczny',
            'name' => 'Salon kosmetyczny',
        ],
        [
            'slug' => 'paznokcie',
            'name' => 'Paznokcie',
        ],
        [
            'slug' => 'fizjoterapia',
            'name' => 'Fizjoterapia',
        ],
        [
            'slug' => 'brwi-i-rzesy',
            'name' => 'Brwi i rzęsy',
        ],
        [
            'slug' => 'masaz',
            'name' => 'Masaż',
        ],
        [
            'slug' => 'zdrowie',
            'name' => 'Zdrowie',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORIES as $categoryData) {
            $category = new ServiceCategory();
            $category->setCategoryFullName($categoryData['name']);
            $category->setCategoryFriendlyName($categoryData['slug']);

            $manager->persist($category);
        }

        $manager->flush();
    }
}
