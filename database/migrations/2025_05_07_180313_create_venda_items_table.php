<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendaItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venda_items', function (Blueprint $table) {
            $table->id(); // Chave primária auto-increment
            $table->foreignId('venda_id') // Chave estrangeira para a tabela 'vendas'
                  ->constrained('vendas') // Nome da tabela referenciada
                  ->onDelete('cascade'); // Opcional: se a venda for deletada, delete os itens

            $table->foreignId('produto_id') // Chave estrangeira para a tabela 'produtos'
                  ->constrained('produtos') // Nome da tabela referenciada
                  ->onDelete('cascade'); // Opcional: se o produto for deletado, o que fazer?

            $table->integer('quantidade'); // Quantidade do item
            $table->decimal('preco_unitario', 10, 2); // Preço unitário no momento da venda (Ex: 10 dígitos no total, 2 depois da vírgula)
            $table->decimal('subtotal', 10, 2); // Subtotal do item (quantidade * preco_unitario)

            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('venda_items');
    }
}
