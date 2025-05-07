<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->json('parcelas')->default(json_encode([]))->change();
        });
    }

    public function down()
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->json('parcelas')->nullable()->change(); // Caso precise reverter para o estado anterior
        });
    }
};
