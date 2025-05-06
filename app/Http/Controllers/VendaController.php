<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Support\Facades\DB;

class VendaController extends Controller
{
    public function criar()
    {
      
        $clientes = Usuario::all();
        $produtos = Produto::all();

        return view('vendas.criar', compact('clientes', 'produtos'));
    }

    public function index()
    {
        $vendas = Venda::with(['cliente', 'itens.produto', 'parcelas'])->latest()->get();

        return view('vendas.index', compact('vendas'));
    }


    
    public function salvar(Request $request)
    {
        $itens = json_decode($request->input('itens'), true);

        if (!$itens || count($itens) === 0) {
            return back()->with('erro', 'Nenhum item foi adicionado à venda.');
        }

        $valorTotal = 0;
        foreach ($itens as $item) {
            $valorTotal += $item['preco_final'];  
        }

        // Iniciar transação
        DB::beginTransaction();
        $formaPagamento = $request->input('forma_pagamento'); 

        if (!$formaPagamento) {
            return back()->with('erro', 'A forma de pagamento não foi informada.');
        }

        try {
        
            $venda = Venda::create([
                'cliente_id' => $request->input('cliente_id'), // Verifique se o cliente_id está correto
                'valor_total' => $valorTotal,
                'forma_pagamento' => $formaPagamento,  // Defina a forma de pagamento conforme necessário
            ]);

            foreach ($itens as $item) {
                $precoUnitario = $item['preco_inicial']; 
                $quantidade = $item['quantidade'];
                $subtotal = $precoUnitario * $quantidade; 

                VendaItem::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $item['produto_id'], // Certifique-se que 'produto_id' existe no array
                    'quantidade' => $item['quantidade'], // Verifique se a quantidade existe
                    'preco_unitario' => $precoUnitario,
                    'subtotal' => $subtotal,
                ]);
            }

            DB::commit();

            return redirect()->route('vendas.criar')->with('sucesso', 'Venda salva com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());  
            return back()->with('erro', 'Erro ao salvar a venda: ' . $e->getMessage());
        }
    }
}
