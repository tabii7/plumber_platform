<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VatValidationService
{
    protected $btpUrl = 'https://www.easyred.com/btp.php';

    /**
     * Validate VAT number and get company information
     */
    public function validateVat(string $vatNumber): array
    {
        try {
            // Clean VAT number (remove spaces, ensure BE prefix)
            $cleanVat = $this->cleanVatNumber($vatNumber);
            
            // Make request to BTP.PHP
            $response = Http::timeout(10)->get($this->btpUrl, [
                'vat' => $cleanVat
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['valid']) && $data['valid']) {
                    return [
                        'valid' => true,
                        'company_name' => $data['company_name'] ?? null,
                        'address' => $data['address'] ?? null,
                        'postal_code' => $data['postal_code'] ?? null,
                        'city' => $data['city'] ?? null,
                        'country' => $data['country'] ?? 'Belgium',
                        'vat_number' => $cleanVat,
                        'raw_response' => $data
                    ];
                } else {
                    return [
                        'valid' => false,
                        'error' => $data['error'] ?? 'Invalid VAT number',
                        'vat_number' => $cleanVat
                    ];
                }
            }

            return [
                'valid' => false,
                'error' => 'Failed to connect to VAT validation service',
                'vat_number' => $cleanVat
            ];

        } catch (\Exception $e) {
            Log::error('VAT validation error', [
                'vat_number' => $vatNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'error' => 'VAT validation service temporarily unavailable',
                'vat_number' => $vatNumber
            ];
        }
    }

    /**
     * Clean and format VAT number
     */
    protected function cleanVatNumber(string $vatNumber): string
    {
        // Remove all non-alphanumeric characters
        $clean = preg_replace('/[^A-Za-z0-9]/', '', $vatNumber);
        
        // Ensure it starts with BE
        if (!str_starts_with(strtoupper($clean), 'BE')) {
            $clean = 'BE' . $clean;
        }
        
        return strtoupper($clean);
    }

    /**
     * Parse address from VAT response
     */
    public function parseAddress(string $address): array
    {
        // Example: "Noordzandstraat 66 8000 Brugge"
        // Pattern: Street Name + Number + Postal Code + City
        
        $parts = explode(' ', trim($address));
        $result = [
            'street_name' => '',
            'house_number' => '',
            'postal_code' => '',
            'city' => ''
        ];

        if (count($parts) < 3) {
            return $result;
        }

        // Find postal code (4 digits)
        $postalCodeIndex = -1;
        foreach ($parts as $i => $part) {
            if (preg_match('/^\d{4}$/', $part)) {
                $postalCodeIndex = $i;
                break;
            }
        }

        if ($postalCodeIndex !== -1) {
            // Everything after postal code is city
            $result['city'] = implode(' ', array_slice($parts, $postalCodeIndex + 1));
            $result['postal_code'] = $parts[$postalCodeIndex];
            
            // Everything before postal code
            $beforePostal = array_slice($parts, 0, $postalCodeIndex);
            
            if (count($beforePostal) >= 2) {
                // Last part before postal code is house number
                $result['house_number'] = end($beforePostal);
                // Everything else is street name
                $result['street_name'] = implode(' ', array_slice($beforePostal, 0, -1));
            } else {
                $result['street_name'] = implode(' ', $beforePostal);
            }
        }

        return $result;
    }

    /**
     * Validate VAT number format
     */
    public function isValidFormat(string $vatNumber): bool
    {
        $clean = $this->cleanVatNumber($vatNumber);
        
        // BE + 10 digits
        return preg_match('/^BE\d{10}$/', $clean);
    }
}

