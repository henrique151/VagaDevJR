<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParcelasColumnToVendasTable extends Migration
{
    public function up()
    {
        Schema::table('vendas', function (Blueprint $table) {
            if (!Schema::hasColumn('vendas', 'parcelas')) {
                $table->json('parcelas')->default(json_encode([]));
            }
        });
    }

    public function down()
    {
        Schema::table('vendas', function (Blueprint $table) {
            if (Schema::hasColumn('vendas', 'parcelas')) {
                $table->dropColumn('parcelas');
            }
        });
    }
}

