# Laravel Azure Key Vault

Azure Key Vault integration for Laravel 12, supporting Managed Identity and Service Principal authentication. This package automatically fetches database credentials from Key Vault and injects them into your configuration at runtime.

## Features

- **Automatic DB Credentials**: Injects `DB_USERNAME` and `DB_PASSWORD` from Key Vault into `config('database.connections.pgsql')`.
- **Managed Identity Support**: Zero-config authentication when running on Azure App Service or Virtual Machines.
- **Service Principal Support**: Fallback to Client ID/Secret for local development or non-Azure environments.
- **Support for Azure PostgreSQL Managed Identity**: Can automatically fetch an AAD token for database authentication if `DB_PASSWORD` is set to `MANAGED_IDENTITY`.
- **Token Caching**: Efficiently caches Azure access tokens in the filesystem to avoid repeated OAuth calls.
- **Test Command**: Built-in CLI tool to verify connectivity.

## Installation

### 1. Add Local Repository (Optional)
If you are developing this package locally, add the following to your main project's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/anandukrishnakk/laravel-azure-keyvault"
    }
],
```

### 2. Install via Composer
```bash
composer require anandukrishnakk/laravel-azure-keyvault:@dev
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=config --provider="AnanduKrishna\AzureKeyVault\AzureKeyVaultServiceProvider"
```

### Environment Variables

Add these to your `.env` file:

```env
# Vault Details
AZURE_KEY_VAULT_URL=https://your-vault.vault.azure.net

# Secret Names in Key Vault
DB_USERNAME_SECRET=pg-username
DB_PASSWORD_SECRET=pg-password

# Optional: Service Principal (for local development)
AZURE_TENANT_ID=your-tenant-id
AZURE_CLIENT_ID=your-client-id
AZURE_CLIENT_SECRET=your-client-secret

# Optional: Managed Identity Client ID (if using Multiple Identities)
AZURE_MANAGED_IDENTITY_CLIENT_ID=your-identity-client-id

# CLI Settings
AZURE_KV_CLI_ENABLED=false
AZURE_KV_AUTO_RECONNECT=true
```

## Usage

### Automatic Configuration
The package automatically runs during the `register` phase. It will:
1. Fetch the secret named in `DB_USERNAME_SECRET` and set it as `database.connections.pgsql.username`.
2. Fetch the secret named in `DB_PASSWORD_SECRET` and set it as `database.connections.pgsql.password`.
3. If `config('database.connections.pgsql.password')` is exactly `MANAGED_IDENTITY`, it will instead fetch an Azure Active Directory token for the PostgreSQL resource.

### Using for Other Databases or Services

If you need to fetch secrets for multiple database connections or other services (like Redis, Mail, etc.), you can use the `AzureKeyVault` facade in your `AppServiceProvider.php`:

```php
use AnanduKrishna\AzureKeyVault\Facades\AzureKeyVault;

public function boot(): void
{
    // Fetch and set custom database credentials
    if ($mysqlPass = AzureKeyVault::get('mysql-production-password')) {
        config(['database.connections.mysql.password' => $mysqlPass]);
    }
    
    // Fetch and set third-party API keys
    if ($stripeKey = AzureKeyVault::get('stripe-secret-key')) {
        config(['services.stripe.secret' => $stripeKey]);
    }
}
```

## Advanced Database Configuration

Use the built-in command to verify that your app can talk to the Vault:

```bash
php artisan azure:test-kv
```

You can also specify a specific secret name to fetch:

```bash
php artisan azure:test-kv my-custom-secret
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
