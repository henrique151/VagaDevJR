<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    protected $fillable = ['venda_id', 'valor', 'vencimento', 'tipo_pagamento'];

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}
