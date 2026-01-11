<?php

namespace App\Constants;

class BusinessFeaturesConstants
{
    public const SAFETY_RULES = [
        'ventilation' => ['label' => 'Wentylacja pomieszczeń', 'icon' => 'fa-solid fa-wind'],
        'disinfection' => ['label' => 'Regularna dezynfekcja stanowiska', 'icon' => 'fa-solid fa-spray-can-sparkles'],
        'sterilization' => ['label' => 'Sterylizacja narzędzi', 'icon' => 'fa-solid fa-syringe'],
        'cashless_payment' => ['label' => 'Możliwość płatności bezgotówkowej', 'icon' => 'fa-solid fa-credit-card'],
        'masks_available' => ['label' => 'Maseczki dostępne na życzenie', 'icon' => 'fa-solid fa-mask-face'],
        'hand_sanitizer' => ['label' => 'Płyn do dezynfekcji rąk', 'icon' => 'fa-solid fa-pump-soap'],
        'social_distancing' => ['label' => 'Zachowanie dystansu społecznego', 'icon' => 'fa-solid fa-people-arrows'],
        'temperature_check' => ['label' => 'Pomiar temperatury przy wejściu', 'icon' => 'fa-solid fa-thermometer'],
        'appointment_only' => ['label' => 'Obsługa wyłącznie na umówione wizyty', 'icon' => 'fa-solid fa-calendar-check'],
        'limited_capacity' => ['label' => 'Ograniczona liczba klientów', 'icon' => 'fa-solid fa-users-slash'],
        'disposable_items' => ['label' => 'Jednorazowe materiały', 'icon' => 'fa-solid fa-box-tissue'],
        'uv_sterilization' => ['label' => 'Sterylizacja UV', 'icon' => 'fa-solid fa-sun'],
        'contactless_service' => ['label' => 'Usługi bezkontaktowe gdzie możliwe', 'icon' => 'fa-solid fa-hand-dots'],
        'certified_products' => ['label' => 'Certyfikowane produkty i kosmetyki', 'icon' => 'fa-solid fa-certificate'],
        'air_purifier' => ['label' => 'Oczyszczacz powietrza', 'icon' => 'fa-solid fa-fan'],
        'protective_equipment' => ['label' => 'Personel w rękawiczkach i maskach', 'icon' => 'fa-solid fa-user-shield'],
        'hygiene_training' => ['label' => 'Przeszkolony personel w zakresie higieny', 'icon' => 'fa-solid fa-graduation-cap'],
        'no_waiting_room' => ['label' => 'Brak poczekalni (klienci czekają na zewnątrz)', 'icon' => 'fa-solid fa-door-open'],
        'online_payment' => ['label' => 'Płatność online przed wizytą', 'icon' => 'fa-brands fa-paypal'],
        'health_declaration' => ['label' => 'Deklaracja zdrowia przed wizytą', 'icon' => 'fa-solid fa-file-medical'],
    ];

    public const AMENITIES = [
        'parking' => ['label' => 'Parking', 'icon' => 'fa-solid fa-square-parking'],
        'wifi' => ['label' => 'Internet (Wi-Fi)', 'icon' => 'fa-solid fa-wifi'],
        'card_payment' => ['label' => 'Akceptacja kart płatniczych', 'icon' => 'fa-solid fa-credit-card'],
        'wheelchair_accessible' => ['label' => 'Dostępne dla niepełnosprawnych', 'icon' => 'fa-solid fa-wheelchair'],
        'pets_allowed' => ['label' => 'Zwierzęta dozwolone', 'icon' => 'fa-solid fa-paw'],
        'child_friendly' => ['label' => 'Przyjazne dla dzieci', 'icon' => 'fa-solid fa-child-reaching'],
        'air_conditioning' => ['label' => 'Klimatyzacja', 'icon' => 'fa-solid fa-snowflake'],
        'coffee_tea' => ['label' => 'Darmowa kawa i herbata', 'icon' => 'fa-solid fa-mug-hot'],
        'waiting_area' => ['label' => 'Poczekalnia', 'icon' => 'fa-solid fa-couch'],
        'magazines' => ['label' => 'Czasopisma i gazety', 'icon' => 'fa-solid fa-newspaper'],
        'tv' => ['label' => 'Telewizja', 'icon' => 'fa-solid fa-tv'],
        'music' => ['label' => 'Muzyka w tle', 'icon' => 'fa-solid fa-music'],
        'charging_station' => ['label' => 'Stacja ładowania telefonów', 'icon' => 'fa-solid fa-charging-station'],
        'lockers' => ['label' => 'Szafki na rzeczy osobiste', 'icon' => 'fa-solid fa-lock'],
        'shower' => ['label' => 'Prysznic', 'icon' => 'fa-solid fa-shower'],
        'changing_room' => ['label' => 'Przebieralnia', 'icon' => 'fa-solid fa-door-closed'],
        'vegan_products' => ['label' => 'Produkty wegańskie', 'icon' => 'fa-solid fa-leaf'],
        'gift_cards' => ['label' => 'Karty podarunkowe', 'icon' => 'fa-solid fa-gift'],
        'loyalty_program' => ['label' => 'Program lojalnościowy', 'icon' => 'fa-solid fa-award'],
        'online_booking' => ['label' => 'Rezerwacja online 24/7', 'icon' => 'fa-solid fa-laptop'],
    ];

    public static function getSafetyRuleLabel(string $key): ?string
    {
        return self::SAFETY_RULES[$key]['label'] ?? null;
    }

    public static function getSafetyRuleIcon(string $key): ?string
    {
        return self::SAFETY_RULES[$key]['icon'] ?? 'fa-regular fa-star';
    }

    public static function getAmenityLabel(string $key): ?string
    {
        return self::AMENITIES[$key]['label'] ?? null;
    }

    public static function getAmenityIcon(string $key): ?string
    {
        return self::AMENITIES[$key]['icon'] ?? 'fa-solid fa-check';
    }
}
