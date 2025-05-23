<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'preco',
        'quantidade',
        'estoque',
    ];

    public function vendaItems()
    {
        return $this->hasMany(VendaItem::class);
    }
}