<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {
    }

    /**
     * Geocode address using Nominatim (OpenStreetMap)
     * 
     * @param string $address Full address to geocode
     * @return array{lat: float, lng: float}|null Returns coordinates or null if not found
     */
    public function geocodeAddress(string $address): ?array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'Booker-App/1.0', // Required by Nominatim
                ],
            ]);

            $data = $response->toArray();

            if (empty($data)) {
                return null;
            }

            $result = $data[0];

            return [
                'lat' => (float) $result['lat'],
                'lng' => (float) $result['lon'],
            ];
        } catch (\Exception $e) {
            // Log error or handle it appropriately
            return null;
        }
    }
}
