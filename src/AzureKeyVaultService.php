<?php

namespace AnanduKrishna\AzureKeyVault;

use GuzzleHttp\Client;
use Throwable;
use Illuminate\Support\Facades\Log;

class AzureKeyVaultService
{
    protected Client $client;
    protected string $vaultUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->vaultUrl = rtrim(config('azure-keyvault.key_vault.url'), '/');
    }

    protected function token(): ?string
    {
        return AzureTokenService::getAccessToken('https://vault.azure.net');
    }

    /**
     * Get a secret from Azure Key Vault.
     * 
     * @param string $secret Name of the secret
     * @param string|null $fallback Default value if secret not found
     * @return string|null
     */
    public function get(string $secret, ?string $fallback = null): ?string
    {
        if (!$this->vaultUrl) {
            return $fallback;
        }

        $token = $this->token();
       
        if (!$token) {
            Log::warning("Azure Token not available. Could not fetch secret '{$secret}' from Key Vault.");
            return $fallback;
        }

        try {
            $response = $this->client->get(
                "{$this->vaultUrl}/secrets/{$secret}?api-version=7.4",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$token}",
                        'Accept'        => 'application/json',
                    ],
                    'timeout' => 5,
                ]
            );

            $data = json_decode($response->getBody(), true);
            return $data['value'] ?? $fallback;

        } catch (Throwable $e) {
            Log::error("Azure Key Vault secret '{$secret}' fetch error: " . $e->getMessage());
            return $fallback;
        }
    }
}
