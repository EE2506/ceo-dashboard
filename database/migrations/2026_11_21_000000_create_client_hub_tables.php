<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->text('name');
            $table->timestamps();
        });

        Schema::create('sites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->text('name');
            $table->text('description')->nullable();
            $table->text('technologies')->nullable();
            $table->text('repo_url')->nullable();
            $table->text('hosting_url')->nullable();
            $table->text('design_url')->nullable();
            $table->timestamps();
            $table->unique(['client_id', 'id']);
        });

        Schema::create('site_changes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('site_id');
            $table->date('changed_on');
            $table->text('title');
            $table->text('description');
            $table->text('reason');
            $table->text('links')->nullable();
            $table->boolean('is_client_visible')->default(false);
            $table->timestamps();
            // A site reference must belong to the same client, even outside the dashboard.
            $table->foreign(['client_id', 'site_id'])->references(['client_id', 'id'])->on('sites');
            $table->index(['site_id', 'changed_on']);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('site_id')->nullable();
            $table->date('recorded_on');
            $table->date('due_on')->nullable();
            $table->decimal('amount', 12, 2);
            $table->text('purpose');
            $table->string('status', 16);
            $table->text('invoice_url')->nullable();
            $table->boolean('is_client_visible')->default(false);
            $table->timestamps();
            $table->foreign(['client_id', 'site_id'])->references(['client_id', 'id'])->on('sites');
            $table->index(['client_id', 'recorded_on']);
            $table->index(['site_id', 'recorded_on']);
            $table->index(['status', 'due_on']);
        });

        Schema::create('audit_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            // Retain the client identifier in the audit trail after a record is removed.
            $table->unsignedBigInteger('client_id')->index();
            $table->string('operation', 24);
            $table->string('target_type', 32);
            $table->unsignedBigInteger('target_id');
            $table->string('outcome', 16);
            $table->timestamp('occurred_at');
            $table->index(['target_type', 'target_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('site_changes');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('clients');
    }
};
