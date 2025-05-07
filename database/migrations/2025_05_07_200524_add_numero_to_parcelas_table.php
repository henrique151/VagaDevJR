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
        Schema::table('parcelas', function (Blueprint $table) {
            $table->unsignedInteger('numero')->after('id'); // ou posicione onde desejar
        });
    }

    public function down()
    {
        Schema::table('parcelas', function (Blueprint $table) {
            $table->dropColumn('numero');
        });
    }
};
