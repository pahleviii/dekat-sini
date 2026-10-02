<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->boolean('is_provider')->default(false)->after('google_id');
            $table->string('phone', 20)->nullable()->after('is_provider');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'is_provider', 'phone']);
        });
    }
};
