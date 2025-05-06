<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function criar()
    {
        return view('usuarios.criar');
    }

    public function salvar(Request $requisicao)
    {
        $requisicao->validate([
            'nome' => 'required|string|max:255',
            'rg'   => 'required|string|max:20',
            'cpf'  => 'required|string|max:14|unique:usuarios,cpf',
        ]);

        Usuario::create([
            'nome' => $requisicao->nome,
            'rg'   => $requisicao->rg,
            'cpf'  => $requisicao->cpf,
        ]);

        return redirect()->route('usuarios.criar')->with('sucesso', 'Cliente cadastrado com sucesso!');
    }
}
