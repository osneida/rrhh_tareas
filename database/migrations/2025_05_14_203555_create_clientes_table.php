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
            $table->string('name', 45)->unique();
            $table->string('address')->nullable();
            $table->string('cif')->nullable()->unique();
            $table->string('mail')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->boolean('status')->default(StatusEnum::ACTIVE);//->comment(StatusEnum::INACTIVE . ' = Inactivo, ' . StatusEnum::ACTIVE . ' = Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
