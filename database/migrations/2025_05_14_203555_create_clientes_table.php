<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\StatusEnum;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('address')->nullable();
            $table->string('cif')->nullable();
            $table->string('mail')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedTinyInteger('status')->default(StatusEnum::ACTIVE)->comment(StatusEnum::ACTIVE.'='.trans('statuse.'.StatusEnum::ACTIVE).', ' .StatusEnum::INACTIVE.'='.trans('statuse.'.StatusEnum::INACTIVE));
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
