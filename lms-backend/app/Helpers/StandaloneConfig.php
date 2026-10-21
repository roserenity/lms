<?php

namespace App\Helpers;

/**
 * Drop-in replacements for tenancy()->tenant->* calls.
 *
 * In the multi-tenant version, identity is resolved at runtime from the
 * incoming subdomain. Here it comes from .env so no Stancl bootstrap is needed.
 *
 * Usage:
 *   StandaloneConfig::tenantId()     → replaces  tenancy()->tenant->id / tenant()->id
 *   StandaloneConfig::domainName()   → replaces  tenancy()->tenant->domain_name
 *   StandaloneConfig::apiEndpoint()  → replaces  tenancy()->tenant->api_endpoint
 *
 * Required .env keys:
 *   TENANT_ID      short identifier, e.g. "myorg"
 *   APP_URL        full URL,         e.g. "https://lms.mycompany.com"
 *   APP_DOMAIN     bare hostname,    e.g. "lms.mycompany.com"
 */
class StandaloneConfig
{
    public static function tenantId(): string
    {
        return env('TENANT_ID', 'standalone');
    }

    public static function domainName(): string
    {
        return env('APP_URL', 'http://localhost');
    }

    public static function apiEndpoint(): string
    {
        return env('APP_DOMAIN', 'localhost');
    }
}
