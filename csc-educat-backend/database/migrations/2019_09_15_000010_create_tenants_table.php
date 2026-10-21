<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            // your custom columns may go here
            $table->string('tenant_name');
            $table->longText('tenant_description');
            $table->string('status')->comment('active or inactive');
            $table->string('domain_name')->unique();
            $table->string('api_endpoint')->unique();
            $table->boolean('passport_installed')->default(false)->comment('boolean');
            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
