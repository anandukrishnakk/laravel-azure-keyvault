<?php

namespace AnanduKrishna\AzureKeyVault\Console;

use Illuminate\Console\Command;
use AnanduKrishna\AzureKeyVault\AzureKeyVaultService;
use AnanduKrishna\AzureKeyVault\AzureTokenService;

class TestAzureKeyVault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'azure:test-kv {secret=pg-username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Azure Key Vault connectivity and secret retrieval';

    /**
     * Execute the console command.
     */
    public function handle(AzureKeyVaultService $vault)
    {
        $this->info("Testing Azure Key Vault Integration...");
        
        $vaultUrl = config('azure-keyvault.key_vault.url');
        $this->line("Vault URL: <comment>{$vaultUrl}</comment>");

        $this->info("\n1. Testing Token Retrieval...");
        $token = AzureTokenService::getAccessToken();
        
        if ($token) {
            $this->info("Successfully retrieved token (Length: " . strlen($token) . ")");
        } else {
            $this->error("Failed to retrieve token. Check logs or credentials.");
            return 1;
        }

        $secretName = $this->argument('secret');
        $this->info("\n2. Testing Secret Retrieval ('{$secretName}')...");
        
        $secretValue = $vault->get($secretName);

        if ($secretValue) {
            $this->info("Successfully retrieved secret!");
            $this->line("Value: <comment>{$secretValue}</comment>");
        } else {
            $this->error("Failed to retrieve secret. The vault might be empty or permissions are missing.");
        }

        return 0;
    }
}
