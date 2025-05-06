<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function criar()
    {
        return view('produtos.criar');
    }

    public function salvar(Request $requisicao)
    {
        $requisicao->validate([
            'nome'       => 'required|string|max:255',
            'preco'      => 'required|numeric|min:0',
            'quantidade' => 'required|integer|min:1',
        ]);

        Produto::create($requisicao->only(['nome', 'preco', 'quantidade']));

        return redirect()->route('produtos.criar')->with('sucesso', 'Produto cadastrado com sucesso!');
    }
}
