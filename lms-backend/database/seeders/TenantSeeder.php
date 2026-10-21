<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

use App\Helpers\Helper;
use App\Services\Central\TenantService;

use App\Models\Central\Tenant;
use App\Models\Central\TenantInfo;
use App\Models\User;

use Artisan;

class TenantSeeder extends Seeder
{
  private $tenantService;

  public function __construct(TenantService $tenantService)
  {
    $this->tenantService = $tenantService;
  }

  /**
  * Run the database seeds.
  *
  * @return void
  */
  public function run()
  {
    $data = [
      'tenant_name' => 'Client Name',
      'organization' => 'general_education',
      'domain_name' => 'http://localhost:8080',
      'sub_domain' => 'localhost',
      'status' => 'active',
      'tenant_description' => 'This is a tenant description',
      'contact_person' => 'Juan Dela Cruz',
      'email' => 'tenant-admin@mail.com',
      'mobile_no' => '09123456789',
      'tel_no' => '(01) 2345 6789'
    ];

    $sub_domain = Helper::stripSubDomainName($data['sub_domain']);

    $tenant_id = Helper::generateTenantId($sub_domain);

    $domain = $sub_domain.'.'.config('services.app_central');

    $tenant = Tenant::create([
      'id' => $tenant_id,
      'tenant_name' => $data['tenant_name'],
      'tenant_description' => $data['tenant_description'],
      'status' => $data['status'],
      'domain_name' => $data['domain_name'],
      'api_endpoint' => $domain,
    ]);

    $tenant->domains()->create([
      'domain' => $domain
    ]);

    $tenant->info()->create([
      'tenant_id' => $tenant->id,
      'contact_person' => $data['contact_person'],
      'email' => $data['email'],
      'organization' => $data['organization'],
      'mobile_no' => $data['mobile_no'],
      'tel_no' => $data['tel_no'],
    ]);

    if(config('app.env') != 'local') {

      $access_token = config('vimeo.connections.main.access_token');

      Http::withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => "Bearer {$access_token}"
      ])
      ->post('https://api.vimeo.com/me/projects', [
        'name' => $tenant->id
      ]);

    }


    if (!$tenant->passport_installed && $tenant->status === 'active') {

      $tenant->run(function() use ($tenant){
        $this->tenantService->createTenantAdminAccount($tenant->info->email, $tenant->id);
        Artisan::call('passport:client', [
          '--personal' => true,
          '--name' => $tenant->id,
        ]);
        
        $tenant->passport_installed = true;
        $tenant->save();
        
        //Artisan::call("tenants:run passport:client --option='personal=personal' --option='name={$tenant->id}' --tenants={$tenant->id}");
      });

    }

    Artisan::call('tenants:seed', [
      '--tenants' => [$tenant_id]
    ]);
  }
}
