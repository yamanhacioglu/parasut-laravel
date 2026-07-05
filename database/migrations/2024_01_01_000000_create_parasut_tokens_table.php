<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parasut_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('connection_key')->unique()->default('default');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->string('token_type')->default('bearer');
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parasut_tokens');
    }
};
