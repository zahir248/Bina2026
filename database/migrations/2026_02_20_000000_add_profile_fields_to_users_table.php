<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('contact_number')->nullable()->after('email');
            $table->string('gender')->nullable()->after('contact_number');
            $table->string('nric_passport')->nullable()->after('gender');
            $table->string('country_region')->nullable()->after('nric_passport');
            $table->string('street_address')->nullable()->after('country_region');
            $table->string('town_city')->nullable()->after('street_address');
            $table->string('state')->nullable()->after('town_city');
            $table->string('postcode_zip')->nullable()->after('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'contact_number',
                'gender',
                'nric_passport',
                'country_region',
                'street_address',
                'town_city',
                'state',
                'postcode_zip',
            ]);
        });
    }
};
