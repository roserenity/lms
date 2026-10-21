<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

use App\Helpers\Helper;

use App\Services\Central\TenantService;

use App\Models\Central\Tenant;

class CreateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:tenant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create tenant.';

    private $tenantService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TenantService $tenantService)
    {
        parent::__construct();
        $this->tenantService = $tenantService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenant_name = $this->ask('Tenant name:');

        $email = $this->ask('Email address:');

        $tenant_domain = $this->ask('Tenant website domain: https://');

        $sub_domain = Helper::stripSubDomainName($tenant_name);

        $tenant_id = Helper::generateTenantId($sub_domain);

        $domain = $sub_domain . '.' . config('services.app_central');

        $tenant = Tenant::create([
            'tenant_name' => $tenant_name,
            'tenant_description' => 'This is a description.',
            'domain_name' => 'https://' . $tenant_domain,
            'status' => 'active'
        ] + [
            'id' => $tenant_id,
            'api_endpoint' => $domain,
        ]);

        $tenant->domains()->create([
            'domain' => $domain
        ]);

        $tenant->info()->create([
            'contact_person' => 'Juan Dela Cruz',
            'email' => $email,
            'organization' => 'general_education',
            'mobile_no' => '09123456789',
            'tel_no' => '09123456789'
        ] + ['tenant_id' => $tenant->id]);

        if (!$tenant->passport_installed && $tenant->status === 'active') {

            $tenant->run(function () use ($tenant) {
                $this->tenantService->createTenantAdminAccount($tenant->info->email, $tenant->id);
                Artisan::call("tenants:run passport:client --option='personal=personal' --option='name={$tenant->id}' --tenants={$tenant->id}");
            });
        }

        $access_token = config('vimeo.connections.main.access_token');

        Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$access_token}"
        ])
            ->post('https://api.vimeo.com/me/projects', [
                'name' => $tenant->id
            ]);

        $this->info("You have successfully added a new tenant. Tenant ID: {$tenant->id}");
    }
}
