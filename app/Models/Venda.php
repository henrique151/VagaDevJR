<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    protected $fillable = ['cliente_id', 'valor_total', 'forma_pagamento'];

    public function cliente()
    {
        return $this->belongsTo(Usuario::class, 'cliente_id');
    }

    public function itens()
    {
        return $this->hasMany(VendaItem::class);
    }

    public function parcelas() {
        return $this->hasMany(Parcela::class);
    }

}
