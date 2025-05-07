<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    use HasFactory;

    protected $fillable = [
        'venda_id',
        'numero',
        'valor',
        'vencimento',
        'tipo_pagamento',
        'status',
    ];

    protected $casts = [
        'vencimento' => 'date',
    ];

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}