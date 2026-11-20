<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('site_id')->nullable();
            $table->text('title');
            $table->date('due_on');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            $table->foreign(['client_id', 'site_id'])->references(['client_id', 'id'])->on('sites');
            $table->index('site_id');
            $table->index(['is_completed', 'due_on', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
