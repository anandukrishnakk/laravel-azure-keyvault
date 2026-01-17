<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Azure Key Vault URL
    |--------------------------------------------------------------------------
    |
    | The full URL to your Azure Key Vault instance.
    | Example: https://your-vault.vault.azure.net
    |
    */
    'key_vault' => [
        'url' => env('AZURE_KEY_VAULT_URL'),
        'cli_enabled' => env('AZURE_KV_CLI_ENABLED', false),
        'auto_reconnect' => env('AZURE_KV_AUTO_RECONNECT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Secret Names
    |--------------------------------------------------------------------------
    |
    | The names of the secrets in Key Vault that store your database credentials.
    |
    */
    'database_secrets' => [
        'username' => env('DB_USERNAME_SECRET', 'pg-username'),
        'password' => env('DB_PASSWORD_SECRET', 'pg-password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Azure Credentials
    |--------------------------------------------------------------------------
    |
    | Configuration for authenticating with Azure.
    |
    */
    'credentials' => [
        'tenant_id' => env('AZURE_TENANT_ID'),
        'client_id' => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'managed_identity_client_id' => env('AZURE_MANAGED_IDENTITY_CLIENT_ID'),
        'metadata_url' => env('AZURE_METADATA_URL', 'http://169.254.169.254'),
    ],
];
