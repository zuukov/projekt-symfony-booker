<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Business;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class ServiceLandingController extends AbstractController
{
    private const CITIES = [
        'Warszawa','Kraków','Wrocław','Poznań',
        'Gdańsk','Łódź','Katowice','Szczecin',
        'Bydgoszcz','Lublin','Białystok','Rzeszów',
        'Toruń','Gliwice','Częstochowa','Radom'
    ];

    // na teraz jako mvp 9 landingow hardcoded z poziomu tego kontrolera
    // w przyszlosci mozna to wpiac w baze i np dodac panel admina z edycja
    private const LANDINGS = [
        'fryzjer' => [
            'name' => 'Usługi fryzjerskie',
            'badge' => 'Usługi fryzjerskie',
            'hero' => [
                'title' => 'Usługi fryzjerskie',
                'subtitle' => 'Korzystanie z usług fryzjera to nie tylko strzyżenie — to szybki sposób na odświeżenie wyglądu, poprawę samopoczucia i zadbanie o włosy w profesjonalnym salonie. Wybierz usługę, sprawdź opinie i umów wizytę online w kilka chwil.',
                'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1600&q=80',
                'imageAlt' => 'Usługi fryzjerskie',
                'hints' => ['Rezerwacja online', 'Opinie klientów', 'Wolne terminy 24/7'],
            ],
            'cities' => [
                'titlePrefix' => 'Fryzjer',
                'title' => 'Fryzjer — w Twojej okolicy',
                'subtitle' => 'Szybko przejdź do popularnych miast albo użyj wyszukiwarki, aby znaleźć salon blisko Ciebie.',
            ],
            'parallax' => [
                'bgClass' => 'parallax-cta__bg--fryzjer',
                'titleHtml' => "Zadbaj o swój styl<br class=\"hidden sm:block\">bez wychodzenia z domu",
                'text' => 'Sprawdź najlepsze salony fryzjerskie w Twojej okolicy, porównaj opinie i zarezerwuj wizytę online w kilka sekund.',
                'primary' => ['label' => 'Znajdź fryzjera', 'href' => '#miasta'],
                'ghost' => ['label' => 'Zobacz usługi', 'href' => '#popularne-zabiegi'],
            ],
            'reviews' => [
                'title' => 'Fryzjer — opinie klientów',
                'subtitle' => 'Realne wrażenia po wizycie. Wybieraj pewnie — na podstawie ocen i komentarzy.',
                'items' => [
                    [
                        'stars' => 5,
                        'rating' => 5.0,
                        'text' => '“Jestem bardzo zadowolona z wizyty! Świetna atmosfera, dokładne cięcie i włosy układają się idealnie. Na pewno wrócę.”',
                        'person' => 'Anna R.',
                        'studio' => 'Hair Lab',
                        'city' => 'Warszawa',
                        'muted' => false,
                    ],
                    [
                        'stars' => 5,
                        'rating' => 5.0,
                        'text' => '“Profesjonalnie i szybko. Fryzjer doradził mi fryzurę do kształtu twarzy, a efekt wyszedł lepiej niż się spodziewałem.”',
                        'person' => 'Michał P.',
                        'studio' => 'Cut & Go',
                        'city' => 'Kraków',
                        'muted' => false,
                    ],
                    [
                        'stars' => 5,
                        'rating' => 4.7,
                        'text' => '“Bardzo miła obsługa i czysty salon. Koloryzacja wyszła równo, a pielęgnacja po zabiegu uratowała moje włosy.”',
                        'person' => 'Magda S.',
                        'studio' => 'Studio Nova',
                        'city' => 'Gdańsk',
                        'muted' => true,
                    ],
                ],
            ],
            'content' => [
                'image' => 'https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?auto=format&fit=crop&w=1600&q=80',
                'imageAlt' => 'Wybierz dobrego fryzjera',
                'title' => 'Wybierz dobrego fryzjera',
                'paragraphs' => [
                    'Dobry fryzjer to nie tylko technika — to także konsultacja, dopasowanie fryzury do stylu życia i pielęgnacja dobrana do rodzaju włosów. Zamiast ryzykować, wybierz salon na podstawie ocen, portfolio i opisów usług.',
                    'W Booker łatwo porównasz oferty, sprawdzisz wolne terminy i zarezerwujesz wizytę online. Bez dzwonienia, bez stresu — dokładnie tak, jak powinno być.',
                ],
                'primary' => ['label' => 'Znajdź salon w okolicy', 'href' => '#miasta'],
                'secondary' => ['label' => 'Zobacz popularne usługi', 'href' => '#popularne-zabiegi'],
            ],
            'treatments' => [
                'title' => 'Popularne zabiegi',
                'subtitle' => 'Najczęściej wybierane usługi fryzjerskie — kliknij, żeby przejść do listy miejsc.',
                'pills' => [
                    ['label' => 'Strzyżenie męskie', 'href' => '#'],
                    ['label' => 'Strzyżenie damskie', 'href' => '#'],
                    ['label' => 'Koloryzacja', 'href' => '#'],
                    ['label' => 'Baleyage', 'href' => '#'],
                    ['label' => 'Modelowanie', 'href' => '#'],
                    ['label' => 'Botoks włosów', 'href' => '#'],
                    ['label' => 'Keratynowe prostowanie', 'href' => '#'],
                    ['label' => 'Tonowanie', 'href' => '#'],
                    ['label' => 'Upięcia okolicznościowe', 'href' => '#'],
                ],
            ],
        ],

        'barber-shop' => [
            'name' => 'Barber shop',
            'badge' => 'Barber shop',
            'hero' => [
                'title' => 'Barber shop',
                'subtitle' => 'Od klasycznego cięcia po precyzyjne kontury brody — wybierz barbera, sprawdź portfolio i umów wizytę online w kilka chwil.',
                'image' => 'https://images.unsplash.com/photo-1519699047748-de8e457a634e?auto=format&fit=crop&w=1600&q=80',
                'imageAlt' => 'Barber shop',
                'hints' => ['Szybka rezerwacja', 'Sprawdzone opinie', 'Męski styl bez stresu'],
            ],
            'cities' => [
                'titlePrefix' => 'Barber shop',
                'title' => 'Barber shop — w Twojej okolicy',
                'subtitle' => 'Wybierz miasto i znajdź najlepszych barberów w okolicy.',
            ],
            'parallax' => [
                'bgClass' => 'parallax-cta__bg--barber',
                'titleHtml' => "Broda i włosy<br class=\"hidden sm:block\">w najlepszych rękach",
                'text' => 'Porównaj opinie, ceny i wolne terminy. Rezerwuj od razu — online.',
                'primary' => ['label' => 'Znajdź barbera', 'href' => '#miasta'],
                'ghost' => ['label' => 'Popularne usługi', 'href' => '#popularne-zabiegi'],
            ],
            'reviews' => [
                'title' => 'Barber shop — opinie klientów',
                'subtitle' => 'Wybieraj po ocenach. To najszybsza droga do dobrego barbera.',
                'items' => [
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Mega klimat i perfekcyjna broda. Dokładnie tak jak chciałem.”', 'person' => 'Kamil W.', 'studio' => 'Old Town Barber', 'city' => 'Wrocław', 'muted' => false],
                    ['stars' => 5, 'rating' => 4.9, 'text' => '“Szybko, konkretnie i bez gadania. Wrócę na pewno.”', 'person' => 'Daniel S.', 'studio' => 'Fade Factory', 'city' => 'Poznań', 'muted' => true],
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Najlepsze cieniowanie jakie miałem. Polecam każdemu.”', 'person' => 'Paweł K.', 'studio' => 'King’s Cut', 'city' => 'Kraków', 'muted' => false],
                ],
            ],
            'content' => [
                'image' => 'https://images.unsplash.com/photo-1517832606299-7ae9b720a186?auto=format&fit=crop&w=1600&q=80',
                'imageAlt' => 'Dobry barber',
                'title' => 'Wybierz barbera po stylu',
                'paragraphs' => [
                    'Dobry barber to precyzja, higiena i styl dopasowany do Ciebie. Sprawdź portfolio, opinie i zakres usług.',
                    'W Booker porównasz ceny, terminy i oceny — a rezerwację zrobisz w minutę.',
                ],
                'primary' => ['label' => 'Znajdź barber shop', 'href' => '#miasta'],
                'secondary' => ['label' => 'Zobacz usługi', 'href' => '#popularne-zabiegi'],
            ],
            'treatments' => [
                'title' => 'Popularne zabiegi',
                'subtitle' => 'Najczęściej wybierane usługi barberskie.',
                'pills' => [
                    ['label' => 'Strzyżenie męskie', 'href' => '#'],
                    ['label' => 'Fade / cieniowanie', 'href' => '#'],
                    ['label' => 'Trymowanie brody', 'href' => '#'],
                    ['label' => 'Golenie brzytwą', 'href' => '#'],
                    ['label' => 'Modelowanie brody', 'href' => '#'],
                    ['label' => 'Strzyżenie + broda', 'href' => '#'],
                ],
            ],
        ],

        'salon-kosmetyczny' => [
            'name' => 'Salon kosmetyczny',
            'badge' => 'Salon kosmetyczny',
            'hero' => [
                'title' => 'Salon kosmetyczny',
                'subtitle' => 'Pielęgnacja twarzy i ciała, zabiegi regeneracyjne i relaks. Znajdź salon kosmetyczny, sprawdź opinie i zarezerwuj termin online.',
                'image' => 'https://images.unsplash.com/photo-1595944024804-733665a112db?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'imageAlt' => 'Salon kosmetyczny',
                'hints' => ['Pełen zakres zabiegów', 'Polecane miejsca', 'Rezerwacja 24/7'],
            ],
            'cities' => [
                'titlePrefix' => 'Salon kosmetyczny',
                'title' => 'Salon kosmetyczny — w Twojej okolicy',
                'subtitle' => 'Wybierz miasto i przejdź do listy sprawdzonych salonów.',
            ],
            'parallax' => [
                'bgClass' => 'parallax-cta__bg--kosmetyczny',
                'titleHtml' => "Zadbaj o skórę<br class=\"hidden sm:block\">i dobre samopoczucie",
                'text' => 'Zobacz opinie, ceny i terminy. Umów wizytę bez dzwonienia.',
                'primary' => ['label' => 'Znajdź salon', 'href' => '#miasta'],
                'ghost' => ['label' => 'Popularne zabiegi', 'href' => '#popularne-zabiegi'],
            ],
            'reviews' => [
                'title' => 'Salon kosmetyczny — opinie klientów',
                'subtitle' => 'Wybieraj na podstawie ocen i prawdziwych doświadczeń.',
                'items' => [
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Świetny zabieg i dokładne omówienie pielęgnacji domowej. Super.”', 'person' => 'Klaudia M.', 'studio' => 'Beauty Zone', 'city' => 'Warszawa', 'muted' => false],
                    ['stars' => 5, 'rating' => 4.8, 'text' => '“Czysto, profesjonalnie i bardzo miła obsługa. Efekty widać od razu.”', 'person' => 'Ola N.', 'studio' => 'Skin Lab', 'city' => 'Gdańsk', 'muted' => true],
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Najlepsza kosmetyczka u jakiej byłam. Polecam z całego serca.”', 'person' => 'Natalia P.', 'studio' => 'Glow Studio', 'city' => 'Kraków', 'muted' => false],
                ],
            ],
            'content' => [
                'image' => 'https://images.unsplash.com/photo-1633681926022-84c23e8cb2d6?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'imageAlt' => 'Zabiegi kosmetyczne',
                'title' => 'Wybierz zabieg dopasowany do potrzeb',
                'paragraphs' => [
                    'Dobry salon kosmetyczny zaczyna się od konsultacji. Wybierz miejsce, które pracuje na sprawdzonych markach i ma dobre opinie.',
                    'W Booker porównasz oferty i zarezerwujesz termin online — szybko i wygodnie.',
                ],
                'primary' => ['label' => 'Znajdź kosmetyczkę', 'href' => '#miasta'],
                'secondary' => ['label' => 'Zobacz popularne usługi', 'href' => '#popularne-zabiegi'],
            ],
            'treatments' => [
                'title' => 'Popularne zabiegi',
                'subtitle' => 'Najczęściej wybierane usługi kosmetyczne.',
                'pills' => [
                    ['label' => 'Oczyszczanie twarzy', 'href' => '#'],
                    ['label' => 'Peeling chemiczny', 'href' => '#'],
                    ['label' => 'Mezoterapia', 'href' => '#'],
                    ['label' => 'Zabiegi nawilżające', 'href' => '#'],
                    ['label' => 'Masaż twarzy', 'href' => '#'],
                    ['label' => 'Depilacja woskiem', 'href' => '#'],
                ],
            ],
        ],

        'paznokcie' => [
            'name' => 'Paznokcie',
            'badge' => 'Paznokcie',
            'hero' => [
                'title' => 'Paznokcie',
                'subtitle' => 'Manicure i pedicure — klasycznie lub hybrydowo. Wybierz stylistkę paznokci, sprawdź opinie i umów termin online.',
                'image' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=1600&q=80',
                'imageAlt' => 'Stylizacja paznokci',
                'hints' => ['Higiena i jakość', 'Sprawdzone salony', 'Szybka rezerwacja'],
            ],
            'cities' => [
                'titlePrefix' => 'Paznokcie',
                'title' => 'Paznokcie — w Twojej okolicy',
                'subtitle' => 'Kliknij miasto i przejdź do listy polecanych miejsc.',
            ],
            'parallax' => [
                'bgClass' => 'parallax-cta__bg--paznokcie',
                'titleHtml' => "Perfekcyjny manicure<br class=\"hidden sm:block\">na każdą okazję",
                'text' => 'Wybieraj po ocenach i rezerwuj termin bez dzwonienia.',
                'primary' => ['label' => 'Znajdź salon', 'href' => '#miasta'],
                'ghost' => ['label' => 'Popularne usługi', 'href' => '#popularne-zabiegi'],
            ],
            'reviews' => [
                'title' => 'Paznokcie — opinie klientów',
                'subtitle' => 'Zobacz, co piszą osoby po wizycie.',
                'items' => [
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Hybryda trzyma się idealnie, super dokładnie i szybko.”', 'person' => 'Sandra K.', 'studio' => 'Nails & More', 'city' => 'Poznań', 'muted' => false],
                    ['stars' => 5, 'rating' => 4.8, 'text' => '“Bardzo estetycznie, czysto i piękne zdobienia.”', 'person' => 'Weronika B.', 'studio' => 'Pure Nails', 'city' => 'Wrocław', 'muted' => true],
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Najlepszy pedicure w mieście. Polecam.”', 'person' => 'Karolina Z.', 'studio' => 'Studio Rose', 'city' => 'Gdańsk', 'muted' => false],
                ],
            ],
            'content' => [
                'image' => 'https://plus.unsplash.com/premium_photo-1661919533946-eb49d0aed799?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'imageAlt' => 'Manicure',
                'title' => 'Wybierz stylistkę po efektach',
                'paragraphs' => [
                    'Stylizacja paznokci to precyzja i bezpieczeństwo. Sprawdź portfolio i opinie, zanim wybierzesz miejsce.',
                    'W Booker w kilka chwil porównasz ceny i terminy — a rezerwację zrobisz online.',
                ],
                'primary' => ['label' => 'Znajdź stylizację', 'href' => '#miasta'],
                'secondary' => ['label' => 'Zobacz usługi', 'href' => '#popularne-zabiegi'],
            ],
            'treatments' => [
                'title' => 'Popularne zabiegi',
                'subtitle' => 'Najczęściej wybierane usługi manicure/pedicure.',
                'pills' => [
                    ['label' => 'Manicure hybrydowy', 'href' => '#'],
                    ['label' => 'Manicure klasyczny', 'href' => '#'],
                    ['label' => 'Żel / przedłużanie', 'href' => '#'],
                    ['label' => 'Naprawa paznokcia', 'href' => '#'],
                    ['label' => 'Pedicure', 'href' => '#'],
                    ['label' => 'Zdobienia', 'href' => '#'],
                ],
            ],
        ],

        'fizjoterapia' => [
            'name' => 'Fizjoterapia',
            'badge' => 'Fizjoterapia',
            'hero' => [
                'title' => 'Fizjoterapia',
                'subtitle' => 'Ból pleców, rehabilitacja, praca z ruchem. Znajdź fizjoterapeutę w okolicy i umów wizytę online.',
                'image' => 'https://images.unsplash.com/photo-1650044252595-cacd425982ff?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'imageAlt' => 'Fizjoterapia',
                'hints' => ['Specjaliści z opiniami', 'Szybkie terminy', 'Wygodna rezerwacja'],
            ],
            'cities' => [
                'titlePrefix' => 'Fizjoterapia',
                'title' => 'Fizjoterapia — w Twojej okolicy',
                'subtitle' => 'Wybierz miasto i sprawdź dostępnych specjalistów.',
            ],
            'parallax' => [
                'bgClass' => 'parallax-cta__bg--fizjo',
                'titleHtml' => "Wracaj do formy<br class=\"hidden sm:block\">z dobrym specjalistą",
                'text' => 'Porównaj opinie i umów rehabilitację bez telefonów.',
                'primary' => ['label' => 'Znajdź fizjo', 'href' => '#miasta'],
                'ghost' => ['label' => 'Popularne usługi', 'href' => '#popularne-zabiegi'],
            ],
            'reviews' => [
                'title' => 'Fizjoterapia — opinie pacjentów',
                'subtitle' => 'Oceny i komentarze pomagają wybrać pewnie.',
                'items' => [
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Po 3 wizytach czuję ogromną poprawę. Konkretne ćwiczenia i świetne podejście.”', 'person' => 'Marek D.', 'studio' => 'Rehab Center', 'city' => 'Warszawa', 'muted' => false],
                    ['stars' => 5, 'rating' => 4.9, 'text' => '“Profesjonalnie i bardzo rzeczowo. Polecam przy problemach z kręgosłupem.”', 'person' => 'Ewa K.', 'studio' => 'Motion Lab', 'city' => 'Kraków', 'muted' => true],
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Najlepszy specjalista u jakiego byłem. Wreszcie wiem co robić.”', 'person' => 'Łukasz S.', 'studio' => 'Fizjo Pro', 'city' => 'Wrocław', 'muted' => false],
                ],
            ],
            'content' => [
                'image' => 'https://images.unsplash.com/photo-1606206873764-fd15e242df52?auto=format&fit=crop&w=1600&q=80',
                'imageAlt' => 'Rehabilitacja',
                'title' => 'Wybierz fizjoterapeutę po opiniach',
                'paragraphs' => [
                    'Fizjoterapia to proces — warto wybrać osobę, która jasno tłumaczy plan i uczy pracy z ciałem.',
                    'W Booker sprawdzisz oceny, terminy i zarezerwujesz wizytę online.',
                ],
                'primary' => ['label' => 'Znajdź specjalistę', 'href' => '#miasta'],
                'secondary' => ['label' => 'Zobacz usługi', 'href' => '#popularne-zabiegi'],
            ],
            'treatments' => [
                'title' => 'Popularne usługi',
                'subtitle' => 'Najczęściej wybierane usługi fizjoterapeutyczne.',
                'pills' => [
                    ['label' => 'Terapia manualna', 'href' => '#'],
                    ['label' => 'Rehabilitacja po urazie', 'href' => '#'],
                    ['label' => 'Ból kręgosłupa', 'href' => '#'],
                    ['label' => 'Ćwiczenia korekcyjne', 'href' => '#'],
                    ['label' => 'Masaż leczniczy', 'href' => '#'],
                ],
            ],
        ],

        'brwi-i-rzesy' => [
            'name' => 'Brwi i rzęsy',
            'badge' => 'Brwi i rzęsy',
            'hero' => [
                'title' => 'Brwi i rzęsy',
                'subtitle' => 'Laminacja, henna, stylizacja rzęs — wybierz sprawdzone miejsce i umów termin online.',
                'image' => 'https://images.unsplash.com/photo-1526045478516-99145907023c?auto=format&fit=crop&w=1600&q=80',
                'imageAlt' => 'Brwi i rzęsy',
                'hints' => ['Efekt “wow”', 'Sprawdzone stylistki', 'Szybka rezerwacja'],
            ],
            'cities' => [
                'titlePrefix' => 'Brwi i rzęsy',
                'title' => 'Brwi i rzęsy — w Twojej okolicy',
                'subtitle' => 'Kliknij miasto i przejdź do listy salonów.',
            ],
            'parallax' => [
                'bgClass' => 'parallax-cta__bg--brwi',
                'titleHtml' => "Podkreśl spojrzenie<br class=\"hidden sm:block\">w kilka chwil",
                'text' => 'Zobacz opinie i zarezerwuj termin online — bez czekania.',
                'primary' => ['label' => 'Znajdź salon', 'href' => '#miasta'],
                'ghost' => ['label' => 'Popularne usługi', 'href' => '#popularne-zabiegi'],
            ],
            'reviews' => [
                'title' => 'Brwi i rzęsy — opinie klientów',
                'subtitle' => 'Sprawdź komentarze i wybierz salon pewnie.',
                'items' => [
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Laminacja brwi wyszła idealnie, efekt jest bardzo naturalny.”', 'person' => 'Julia T.', 'studio' => 'Brow Studio', 'city' => 'Warszawa', 'muted' => false],
                    ['stars' => 5, 'rating' => 4.8, 'text' => '“Rzęsy trzymają się świetnie i są lekkie. Polecam.”', 'person' => 'Maja S.', 'studio' => 'Lash Room', 'city' => 'Poznań', 'muted' => true],
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Henna + regulacja — super kształt i dokładność.”', 'person' => 'Patrycja W.', 'studio' => 'Beauty Line', 'city' => 'Kraków', 'muted' => false],
                ],
            ],
            'content' => [
                'image' => 'https://plus.unsplash.com/premium_photo-1684407616442-8d5a1b7c978e?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'imageAlt' => 'Stylizacja brwi',
                'title' => 'Wybierz stylistkę po jakości',
                'paragraphs' => [
                    'Przy brwiach i rzęsach liczy się precyzja i bezpieczeństwo. Zobacz oceny, zdjęcia i opisy usług.',
                    'W Booker rezerwujesz termin online w minutę.',
                ],
                'primary' => ['label' => 'Znajdź usługę', 'href' => '#miasta'],
                'secondary' => ['label' => 'Zobacz popularne usługi', 'href' => '#popularne-zabiegi'],
            ],
            'treatments' => [
                'title' => 'Popularne zabiegi',
                'subtitle' => 'Najczęściej wybierane usługi brwi i rzęs.',
                'pills' => [
                    ['label' => 'Laminacja brwi', 'href' => '#'],
                    ['label' => 'Henna + regulacja', 'href' => '#'],
                    ['label' => 'Przedłużanie rzęs 1:1', 'href' => '#'],
                    ['label' => 'Volume / 2-3D', 'href' => '#'],
                    ['label' => 'Lifting rzęs', 'href' => '#'],
                ],
            ],
        ],

        'masaz' => [
            'name' => 'Masaż',
            'badge' => 'Masaż',
            'hero' => [
                'title' => 'Masaż',
                'subtitle' => 'Relaks, regeneracja, ulga dla spiętych mięśni. Wybierz masażystę, sprawdź opinie i umów wizytę online.',
                'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=1600&q=80',
                'imageAlt' => 'Masaż',
                'hints' => ['Relaks i regeneracja', 'Wolne terminy', 'Szybka rezerwacja'],
            ],
            'cities' => [
                'titlePrefix' => 'Masaż',
                'title' => 'Masaż — w Twojej okolicy',
                'subtitle' => 'Znajdź masażystę w popularnych miastach.',
            ],
            'parallax' => [
                'bgClass' => 'parallax-cta__bg--masaz',
                'titleHtml' => "Zwolnij tempo<br class=\"hidden sm:block\">i zadbaj o siebie",
                'text' => 'Porównaj miejsca i zarezerwuj masaż online.',
                'primary' => ['label' => 'Znajdź masaż', 'href' => '#miasta'],
                'ghost' => ['label' => 'Popularne masaże', 'href' => '#popularne-zabiegi'],
            ],
            'reviews' => [
                'title' => 'Masaż — opinie klientów',
                'subtitle' => 'Wybierz miejsce na podstawie ocen i komentarzy.',
                'items' => [
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Mega ulga dla pleców, świetna atmosfera i profesjonalizm.”', 'person' => 'Tomek P.', 'studio' => 'Relax Point', 'city' => 'Łódź', 'muted' => false],
                    ['stars' => 5, 'rating' => 4.9, 'text' => '“Masaż sportowy zrobił robotę. Polecam po treningach.”', 'person' => 'Kinga O.', 'studio' => 'Body Care', 'city' => 'Wrocław', 'muted' => true],
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Najlepszy relaks po pracy. Wrócę na pewno.”', 'person' => 'Agnieszka S.', 'studio' => 'Zen Studio', 'city' => 'Gdańsk', 'muted' => false],
                ],
            ],
            'content' => [
                'image' => 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'imageAlt' => 'Relaks',
                'title' => 'Dobierz masaż do potrzeb',
                'paragraphs' => [
                    'Inny masaż sprawdzi się na relaks, inny przy napięciach i bólu. Sprawdź opisy usług i opinie klientów.',
                    'W Booker łatwo porównasz terminy i ceny, a rezerwację zrobisz online.',
                ],
                'primary' => ['label' => 'Znajdź masażystę', 'href' => '#miasta'],
                'secondary' => ['label' => 'Zobacz popularne masaże', 'href' => '#popularne-zabiegi'],
            ],
            'treatments' => [
                'title' => 'Popularne masaże',
                'subtitle' => 'Najczęściej wybierane rodzaje masażu.',
                'pills' => [
                    ['label' => 'Masaż relaksacyjny', 'href' => '#'],
                    ['label' => 'Masaż klasyczny', 'href' => '#'],
                    ['label' => 'Masaż sportowy', 'href' => '#'],
                    ['label' => 'Masaż tkanek głębokich', 'href' => '#'],
                    ['label' => 'Masaż pleców', 'href' => '#'],
                ],
            ],
        ],

        'zdrowie' => [
            'name' => 'Zdrowie',
            'badge' => 'Zdrowie',
            'hero' => [
                'title' => 'Zdrowie',
                'subtitle' => 'Specjaliści i usługi prozdrowotne w Twojej okolicy. Sprawdź opinie i umawiaj wizyty online.',
                'image' => 'https://images.unsplash.com/photo-1580281658223-9b93f18ae9ae?auto=format&fit=crop&w=1600&q=80',
                'imageAlt' => 'Zdrowie',
                'hints' => ['Sprawdzeni specjaliści', 'Oceny pacjentów', 'Rezerwacja online'],
            ],
            'cities' => [
                'titlePrefix' => 'Zdrowie',
                'title' => 'Zdrowie — w Twojej okolicy',
                'subtitle' => 'Szybki skrót do popularnych miast i ofert.',
            ],
            'parallax' => [
                'bgClass' => 'parallax-cta__bg--zdrowie',
                'titleHtml' => "Zadbaj o zdrowie<br class=\"hidden sm:block\">na własnych zasadach",
                'text' => 'Wybierz specjalistę po ocenach i umów wizytę online.',
                'primary' => ['label' => 'Znajdź specjalistę', 'href' => '#miasta'],
                'ghost' => ['label' => 'Zobacz usługi', 'href' => '#popularne-zabiegi'],
            ],
            'reviews' => [
                'title' => 'Zdrowie — opinie pacjentów',
                'subtitle' => 'Realne komentarze i oceny po wizytach.',
                'items' => [
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Bardzo rzeczowa konsultacja, wszystko jasno wytłumaczone.”', 'person' => 'Iwona L.', 'studio' => 'Med Point', 'city' => 'Warszawa', 'muted' => false],
                    ['stars' => 5, 'rating' => 4.8, 'text' => '“Szybki termin i profesjonalne podejście. Polecam.”', 'person' => 'Marcin J.', 'studio' => 'Health Care', 'city' => 'Poznań', 'muted' => true],
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Świetny specjalista i miła obsługa. Wrócę.”', 'person' => 'Edyta K.', 'studio' => 'Clinic Pro', 'city' => 'Kraków', 'muted' => false],
                ],
            ],
            'content' => [
                'image' => 'https://images.unsplash.com/photo-1535914254981-b5012eebbd15?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                'imageAlt' => 'Konsultacja',
                'title' => 'Wybieraj pewnie — po ocenach',
                'paragraphs' => [
                    'W usługach zdrowotnych liczy się zaufanie. Sprawdź opinie, opisy i dostępne terminy.',
                    'W Booker umawiasz wizyty online i masz wszystko w jednym miejscu.',
                ],
                'primary' => ['label' => 'Znajdź usługę', 'href' => '#miasta'],
                'secondary' => ['label' => 'Zobacz popularne', 'href' => '#popularne-zabiegi'],
            ],
            'treatments' => [
                'title' => 'Popularne usługi',
                'subtitle' => 'Najczęściej wyszukiwane usługi w kategorii zdrowie.',
                'pills' => [
                    ['label' => 'Konsultacja', 'href' => '#'],
                    ['label' => 'Rehabilitacja', 'href' => '#'],
                    ['label' => 'Diagnostyka', 'href' => '#'],
                    ['label' => 'Terapia', 'href' => '#'],
                    ['label' => 'Profilaktyka', 'href' => '#'],
                ],
            ],
        ],

        'makijaz' => [
            'name' => 'Makijaż',
            'badge' => 'Makijaż',
            'hero' => [
                'title' => 'Makijaż',
                'subtitle' => 'Makijaż dzienny, wieczorowy i okolicznościowy. Znajdź wizażystkę, sprawdź opinie i umów termin online.',
                'image' => 'https://images.unsplash.com/photo-1526045478516-99145907023c?auto=format&fit=crop&w=1600&q=80',
                'imageAlt' => 'Makijaż',
                'hints' => ['Portfolio i opinie', 'Szybkie terminy', 'Rezerwacja online'],
            ],
            'cities' => [
                'titlePrefix' => 'Makijaż',
                'title' => 'Makijaż — w Twojej okolicy',
                'subtitle' => 'Wybierz miasto i znajdź wizażystkę blisko Ciebie.',
            ],
            'parallax' => [
                'bgClass' => 'parallax-cta__bg--makijaz',
                'titleHtml' => "Makijaż dopasowany<br class=\"hidden sm:block\">do okazji i Ciebie",
                'text' => 'Porównaj opinie i zarezerwuj termin online w minutę.',
                'primary' => ['label' => 'Znajdź wizażystkę', 'href' => '#miasta'],
                'ghost' => ['label' => 'Zobacz usługi', 'href' => '#popularne-zabiegi'],
            ],
            'reviews' => [
                'title' => 'Makijaż — opinie klientów',
                'subtitle' => 'Wybieraj na podstawie ocen i zdjęć prac.',
                'items' => [
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Makijaż trzymał cały dzień, a efekt był dokładnie taki jak chciałam.”', 'person' => 'Monika R.', 'studio' => 'Makeup Room', 'city' => 'Warszawa', 'muted' => false],
                    ['stars' => 5, 'rating' => 4.8, 'text' => '“Bardzo profesjonalnie, super kosmetyki i higiena.”', 'person' => 'Zuzanna K.', 'studio' => 'Glow Makeup', 'city' => 'Kraków', 'muted' => true],
                    ['stars' => 5, 'rating' => 5.0, 'text' => '“Piękny efekt, świetne dopasowanie do urody.”', 'person' => 'Natalia S.', 'studio' => 'Beauty Art', 'city' => 'Wrocław', 'muted' => false],
                ],
            ],
            'content' => [
                'image' => 'https://images.unsplash.com/photo-1487412912498-0447578fcca8?auto=format&fit=crop&w=1600&q=80',
                'imageAlt' => 'Makijaż okolicznościowy',
                'title' => 'Wybierz makijaż po stylu',
                'paragraphs' => [
                    'Dobry makijaż to nie maska — to podkreślenie urody. Sprawdź portfolio i opinie.',
                    'W Booker szybko porównasz ceny i terminy, a rezerwację zrobisz online.',
                ],
                'primary' => ['label' => 'Znajdź usługę', 'href' => '#miasta'],
                'secondary' => ['label' => 'Popularne usługi', 'href' => '#popularne-zabiegi'],
            ],
            'treatments' => [
                'title' => 'Popularne usługi',
                'subtitle' => 'Najczęściej wybierane rodzaje makijażu.',
                'pills' => [
                    ['label' => 'Makijaż dzienny', 'href' => '#'],
                    ['label' => 'Makijaż wieczorowy', 'href' => '#'],
                    ['label' => 'Makijaż ślubny', 'href' => '#'],
                    ['label' => 'Makijaż próbny', 'href' => '#'],
                    ['label' => 'Kępki rzęs', 'href' => '#'],
                ],
            ],
        ],
    ];

    #[Route('/usluga/{slug}', name: 'app_service_landing', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function show(string $slug, Request $request, EntityManagerInterface $em): Response {
        $landing = self::LANDINGS[$slug] ?? null;

        if (!$landing) {
            throw new NotFoundHttpException();
        }

        $dbCities = $em->createQueryBuilder()
            ->select('DISTINCT b.city')
            ->from(Business::class, 'b')
            ->where('b.city IS NOT NULL')
            ->andWhere('b.city <> :empty')
            ->setParameter('empty', '')
            ->orderBy('b.city', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        $cities = !empty($dbCities) ? array_values($dbCities) : self::CITIES;

        $selectedCity = trim((string) $request->query->get('city', ''));

        if ($selectedCity === '' || !in_array($selectedCity, $cities, true)) {
            $selectedCity = '';
        }

        $categoryNeedlesBySlug = [
            'fryzjer' => ['Fryzjer', 'Usługi fryzjerskie'],
            'barber-shop' => ['Barber', 'Barber shop'],
            'salon-kosmetyczny' => ['Kosmetyka', 'Salon kosmetyczny'],
            'paznokcie' => ['Paznokcie', 'Manicure', 'Pedicure'],
            'fizjoterapia' => ['Fizjoterapia', 'Rehabilitacja'],
            'brwi-i-rzesy' => ['Brwi', 'Rzęsy', 'Brwi i rzęsy'],
            'masaz' => ['Masaż', 'Masaz'],
            'makijaz' => ['Makijaż', 'Makijaz'],
        ];

        $needles = $categoryNeedlesBySlug[$slug] ?? [$landing['name']];

        $businessCards = [];

        if ($selectedCity !== '') {
            $qb = $em->createQueryBuilder()
                ->select('DISTINCT b', 's')
                ->from(Business::class, 'b')
                ->innerJoin('b.services', 's')
                ->innerJoin('s.category', 'c')
                ->where('b.city = :city')
                ->andWhere('s.isActive = true')
                ->andWhere(
                    $em->createQueryBuilder()->expr()->orX(
                        $em->createQueryBuilder()->expr()->in('c.categoryFriendlyName', ':needles'),
                        $em->createQueryBuilder()->expr()->in('c.categoryFullName', ':needles')
                    )
                )
                ->setParameter('city', $selectedCity)
                ->setParameter('needles', $needles)
                ->orderBy('b.businessName', 'ASC');

            $businesses = $qb->getQuery()->getResult();

            foreach ($businesses as $b) {
                $pills = [];

                foreach ($b->getServices() as $service) {
                    if (!$service->isActive()) {
                        continue;
                    }

                    $cat = $service->getCategory();
                    if (!$cat) {
                        continue;
                    }

                    $friendly = (string) $cat->getCategoryFriendlyName();
                    $full = (string) $cat->getCategoryFullName();

                    if (!in_array($friendly, $needles, true) && !in_array($full, $needles, true)) {
                        continue;
                    }

                    $pills[] = (string) $service->getName();
                    if (count($pills) >= 6) {
                        break;
                    }
                }

                $image = $b->getLogoUrl();

                if (!$image) {
                    $image = 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1600&q=80';
                }

                $businessCards[] = [
                    'id' => $b->getId(),
                    'name' => (string) $b->getBusinessName(),
                    'address' => (string) $b->getAddress() . ', ' . (string) $b->getCity(),
                    'image' => $image,
                    'servicePills' => array_values(array_unique($pills)),
                ];
            }
        }

        $cityLinks = array_map(
            fn (string $city) => [
                'label' => ($landing['cities']['titlePrefix'] ?? $landing['name']) . ' ' . $city,
                'href' => $this->generateUrl('app_service_landing', ['slug' => $slug]) . '?city=' . urlencode($city) . '#uslugi',
                'city' => $city,
            ],
            $cities
        );

        $allServices = array_map(
            fn (string $s, array $cfg) => ['slug' => $s, 'label' => $cfg['name']],
            array_keys(self::LANDINGS),
            self::LANDINGS
        );

        return $this->render('services/landing.html.twig', [
            'landing' => array_merge($landing, [
                'slug' => $slug,
                'cityLinks' => $cityLinks,
            ]),
            'allServices' => $allServices,
            'title' => $landing['name'],
            'slug' => $slug,
            'cities' => $cities,
            'selectedCity' => $selectedCity,
            'businesses' => $businessCards,
        ]);

    }   
}
