<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('google_avatar');
            }

            if (! Schema::hasColumn('users', 'phone_is_whatsapp')) {
                $table->boolean('phone_is_whatsapp')->default(false)->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'avatar_path')) {
                $table->dropColumn('avatar_path');
            }

            if (Schema::hasColumn('users', 'phone_is_whatsapp')) {
                $table->dropColumn('phone_is_whatsapp');
            }
        });
    }
};
