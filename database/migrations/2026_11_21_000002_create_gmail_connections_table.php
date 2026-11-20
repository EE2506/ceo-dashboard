<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('audit_events', function (Blueprint $table): void {
            $table->unsignedBigInteger('client_id')->nullable()->change();
        });

        Schema::create('gmail_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->text('email_address');
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('expires_at');
            $table->boolean('requires_reconnect')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gmail_connections');

        // Account-level audit history remains valid without a client.
    }
};
