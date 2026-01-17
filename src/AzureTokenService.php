<?php

namespace AnanduKrishna\AzureKeyVault;

use GuzzleHttp\Client;
use Throwable;
use Illuminate\Support\Facades\Log;

class AzureTokenService
{
    /**
     * Get an access token for a specific Azure resource.
     * Uses a direct file-based cache to avoid circular dependencies with Laravel's Cache system.
     */
    public static function getAccessToken(string $resource = 'https://vault.azure.net'): ?string
    {
        $cacheFile = storage_path('framework/cache/azure_token_' . md5($resource) . '.json');

        // 1. Try to get from local file cache
        if (file_exists($cacheFile)) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (isset($cached['token']) && isset($cached['expires_at']) && $cached['expires_at'] > time()) {
                return $cached['token'];
            }
        }

        $client = new Client();

        // 2. Try Managed Identity (Azure Environment)
        try {
            $baseUrl = config('azure-keyvault.credentials.metadata_url', 'http://169.254.169.254');
            $clientId = config('azure-keyvault.credentials.managed_identity_client_id'); 
            
            $url = "{$baseUrl}/metadata/identity/oauth2/token?api-version=2018-02-01&resource=" . urlencode($resource);
            if ($clientId) {
                $url .= "&client_id=" . urlencode($clientId);
            }

            $response = $client->get($url, [
                'headers' => ['Metadata' => 'true'],
                'timeout' => 2,
            ]);

            $data = json_decode($response->getBody(), true);
            return self::saveToken($cacheFile, $data);
        } catch (Throwable $e) {
            // Managed Identity not available
        }

        // 3. Fallback to Service Principal
        try {
            $tenantId = config('azure-keyvault.credentials.tenant_id');
            $clientId = config('azure-keyvault.credentials.client_id');
            $clientSecret = config('azure-keyvault.credentials.client_secret');

            if ($tenantId && $clientId && $clientSecret) {
                $response = $client->post("https://login.microsoftonline.com/{$tenantId}/oauth2/token", [
                    'form_params' => [
                        'grant_type'    => 'client_credentials',
                        'client_id'     => $clientId,
                        'client_secret' => $clientSecret,
                        'resource'      => $resource,
                    ],
                ]);

                $data = json_decode($response->getBody(), true);
                return self::saveToken($cacheFile, $data);
            }
        } catch (Throwable $e) {
            Log::error('Azure Token fetch failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Save the token to a local JSON file.
     */
    private static function saveToken(string $cacheFile, array $data): ?string
    {
        $token = $data['access_token'] ?? null;
        if (!$token) {
            return null;
        }

        $expiresIn = (int) ($data['expires_in'] ?? 3600);
        
        // Save to file with expiration timestamp (subtract 300s for safety)
        file_put_contents($cacheFile, json_encode([
            'token'      => $token,
            'expires_at' => time() + ($expiresIn - 300),
        ]));

        return $token;
    }
}
