<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'valor_total',
        'forma_pagamento',
        'tipo_pagamento',
    ];

    public function cliente()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function itens()
    {
        return $this->hasMany(VendaItem::class);
    }

    public function parcelas()
    {
        return $this->hasMany(Parcela::class)->orderBy('vencimento');
    }

    public function listaDeParcelas()
    {
        return $this->hasMany(Parcela::class)->orderBy('vencimento');
    }
}