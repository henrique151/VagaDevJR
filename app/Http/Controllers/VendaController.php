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

    public function update(Request $request, $id)
    {
        // Validar se o cliente_id foi fornecido
        $request->validate([
            'cliente_id' => 'required|exists:usuarios,id',  // Verifica se o cliente existe
        ]);

        // Atualizar a venda com o cliente encontrado
        $venda = Venda::findOrFail($id);
        $venda->cliente_id = $request->input('cliente_id');  // Atribuir o cliente_id diretamente
        $venda->save();

        return redirect()->route('vendas.index')->with('success', 'Venda atualizada com sucesso.');
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer|min:1',
            'preco_unitario' => 'required|numeric|min:0',
        ]);

        $subtotal = $request->quantidade * $request->preco_unitario;

        VendaItem::create([
            'venda_id' => $request->venda_id,
            'produto_id' => $request->produto_id,
            'quantidade' => $request->quantidade,
            'preco_unitario' => $request->preco_unitario,
            'subtotal' => $subtotal,
        ]);

        // Atualiza o valor total
        $venda = Venda::findOrFail($request->venda_id);
        $venda->valor_total = $venda->itens()->sum('subtotal');
        $venda->save();

        return redirect()->route('vendas.edit', $request->venda_id)->with('success', 'Produto adicionado com sucesso.');
    }

    public function destroyItem($id)
    {
        $item = VendaItem::findOrFail($id);
        $vendaId = $item->venda_id;
        $item->delete();

        // Atualiza o valor total
        $venda = Venda::findOrFail($vendaId);
        $venda->valor_total = $venda->itens()->sum('subtotal');
        $venda->save();

        return redirect()->route('vendas.edit', $vendaId)->with('success', 'Item excluído e valor total atualizado.');
    }


    public function updateMultiplos(Request $request, $vendaId)
    {
    $venda = Venda::findOrFail($vendaId);
    $novoValorTotal = 0;

    foreach ($request->itens as $itemData) {
        $item = \App\Models\VendaItem::find($itemData['id']);

        if (!$item) continue;

        if (isset($itemData['remover']) && $itemData['remover'] == '1') {
            $item->delete();
        } else {
            $preco = floatval($itemData['preco_unitario']);
            $quantidade = intval($itemData['quantidade']);
            $subtotal = $preco * $quantidade;
            $novoValorTotal += $subtotal;

            $item->update([
                'produto_id' => $itemData['produto_id'],
                'quantidade' => $quantidade,
                'preco_unitario' => $preco,
                'subtotal' => $subtotal,
            ]);
        }
    }

        // Recalcula o valor total somando os subtotais dos itens restantes
        $novoValorTotal = $venda->itens()->sum('subtotal');
        $venda->valor_total = $novoValorTotal;
        $venda->save();

        return redirect()->route('vendas.edit', $vendaId)->with('success', 'Itens atualizados e valor total recalculado.');
    }

    public function edit($id)
    {
        $venda = Venda::find($id);
        $clientes = Usuario::all();
        $produtos = Produto::all();

        return view('vendas.edit', [
            'venda' => $venda,
            'clientes' => $clientes,
            'produtos' => $produtos,
        ]);
    }

    public function destroy($id)
    {
        $venda = Venda::findOrFail($id);
        $venda->delete();

        return redirect()->route('vendas.index');
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
