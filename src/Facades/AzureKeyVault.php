<?php

namespace AnanduKrishna\AzureKeyVault\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null get(string $secret, ?string $fallback = null)
 * 
 * @see \AnanduKrishna\AzureKeyVault\AzureKeyVaultService
 */
class AzureKeyVault extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \AnanduKrishna\AzureKeyVault\AzureKeyVaultService::class;
    }
}
