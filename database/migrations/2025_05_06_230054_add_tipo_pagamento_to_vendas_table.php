<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoPagamentoToVendasTable extends Migration
{
    public function up()
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->string('tipo_pagamento')->nullable();  // Adiciona o campo tipo_pagamento
        });
    }

    public function down()
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn('tipo_pagamento');  // Remove o campo tipo_pagamento caso a migração seja revertida
        });
    }
}
