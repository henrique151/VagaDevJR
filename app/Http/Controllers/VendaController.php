<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\Parcela;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class VendaController extends Controller
{
    public function criar(Request $request)
    {
        $clientes = Usuario::all();
        $produtos = Produto::all();
 
        $itens = json_decode($request->input('itens_data', '[]'), true) ?? [];
        $parcelas = json_decode($request->input('parcelas_data', '[]'), true) ?? [];

        return view('vendas.criar', compact('clientes', 'produtos', 'parcelas', 'itens'));
    }

    public function index(Request $request)
    {
        $query = Venda::with(['cliente', 'itens.produto']);

        if ($request->filled('cpf')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('cpf', 'like', '%' . $request->cpf . '%');
            });
        }

        if ($request->filled('rg')) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('rg', 'like', '%' . $request->rg . '%');
            });
        }

        if ($request->filled('produto')) {
            $query->whereHas('itens.produto', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->produto . '%');
            });
        }

        $vendas = $query->get();

        return view('vendas.index', compact('vendas'));
    }

    public function edit($id)
    {
        $venda = Venda::with('parcelas', 'itens.produto')->findOrFail($id);
        $clientes = Usuario::all();
        $produtos = Produto::all();

        return view('vendas.edit', [
            'venda' => $venda,
            'clientes' => $clientes,
            'produtos' => $produtos,
        ]);
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'cliente_id' => 'required|exists:usuarios,id',
        'tipo_pagamento' => 'required|string',
        'forma_pagamento' => 'required|string',
        'parcelas_data' => 'nullable|json',
    ]);

    $venda = Venda::with(['parcelas', 'itens'])->findOrFail($id);
    $valorTotalVenda = $venda->itens->sum('subtotal');

    DB::beginTransaction();

    try {
        $venda->update([
            'cliente_id' => $request->cliente_id,
            'tipo_pagamento' => $request->tipo_pagamento,
            'forma_pagamento' => $request->forma_pagamento,
        ]);

        if ($request->forma_pagamento === 'parcelado' && $request->filled('parcelas_data')) {
            $parcelasNovas = json_decode($request->input('parcelas_data'), true);

            \Log::info('Parcelas recebidas:', ['parcelas' => $parcelasNovas]);

            if (!is_array($parcelasNovas)) {
                throw new \Exception('Formato inválido de parcelas_data.');
            }

            foreach ($parcelasNovas as $p) {
                if (!isset($p['numero'], $p['valor'], $p['vencimento'], $p['tipo_pagamento'])) {
                    throw new \Exception('Dados de parcelas incompletos.');
                }
            }

            $totalParcelas = array_sum(array_map(fn($p) => floatval($p['valor']), $parcelasNovas));
            $diferenca = $valorTotalVenda - $totalParcelas;

            if (abs($diferenca) > 0.01) {
                $ultimaIndex = array_key_last($parcelasNovas);
                $parcelasNovas[$ultimaIndex]['valor'] += $diferenca;
                \Log::info("Ajuste automático na última parcela: +{$diferenca}");
            }

            $parcelasExistentesIds = $venda->parcelas->pluck('id')->toArray();
            $parcelasNovasIds = collect($parcelasNovas)->pluck('id')->filter()->toArray();
            $parcelasParaRemover = array_diff($parcelasExistentesIds, $parcelasNovasIds);

            if ($parcelasParaRemover) {
                Parcela::whereIn('id', $parcelasParaRemover)->delete();
            }

            foreach ($parcelasNovas as $p) {
                $vencimento = Carbon::parse($p['vencimento'])->format('Y-m-d');

                $parcelaData = [
                    'numero' => (int) $p['numero'],
                    'valor' => (float) $p['valor'],
                    'vencimento' => $vencimento,
                    'tipo_pagamento' => $p['tipo_pagamento'],
                    'status' => $p['status'] ?? 'aberto',
                ];

                if (!empty($p['id'])) {
                    $parcela = Parcela::find($p['id']);
                    if ($parcela) {
                        $parcela->update($parcelaData);
                        \Log::info("Parcela atualizada (ID {$p['id']})", $parcelaData);
                    } else {
                        $novaParcela = $venda->parcelas()->create($parcelaData);
                        \Log::info("Parcela criada (ID não encontrada):", ['id' => $novaParcela->id]);
                    }
                } else {
                    $novaParcela = $venda->parcelas()->create($parcelaData);
                    \Log::info("Nova parcela criada:", ['id' => $novaParcela->id]);
                }
            }
        } else {
            // Se não for parcelado, remove todas as parcelas
            $venda->parcelas()->delete();
        }

        DB::commit();
        return redirect()->route('vendas.index')->with('success', 'Venda atualizada com sucesso.');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Erro ao atualizar venda:', [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);
        return back()->withErrors(['erro_salvar' => 'Erro ao atualizar venda: ' . $e->getMessage()])->withInput();
    }
}

    public function store(Request $request)
    {
    // Primeiro, validar apenas os campos básicos e formato JSON
        $request->validate([
            'cliente_id' => 'required|exists:usuarios,id',
            'itens_data' => 'required|json',
            'forma_pagamento' => 'required|string',
            'tipo_pagamento' => 'required|string',
            'parcelas_data' => 'nullable|json',
        ]);

        // Agora sim, decodifica os JSONs
        $itens = json_decode($request->input('itens_data'), true);
        $parcelas = json_decode($request->input('parcelas_data'), true) ?? [];

        // Substitui os campos no $request para que a próxima validação funcione
        $request->merge([
            'itens_data' => $itens,
            'parcelas_data' => $parcelas,
        ]);
    
        $request->validate([
        'itens_data' => 'required|array|min:1',
        'itens_data.*.produto_id' => 'required|exists:produtos,id',
        'itens_data.*.quantidade' => 'required|integer|min:1',
        'itens_data.*.preco_final' => 'required|numeric|min:0',

        'parcelas_data' => 'nullable|array',
        'parcelas_data.*.numero' => 'required|integer|min:1',
        'parcelas_data.*.valor' => 'required|numeric|min:0',
        'parcelas_data.*.vencimento' => 'required|date_format:Y-m-d',
        'parcelas_data.*.tipo_pagamento' => 'required|string',
        'parcelas_data.*.status' => 'nullable|string',
    ]);
    
        DB::beginTransaction();

        try {
          
            $totalVenda = 0;
            if (!empty($itens)) { 
                 foreach ($itens as $itemData) {
                     $subtotal = $itemData['quantidade'] * $itemData['preco_final']; 
                     $totalVenda += $subtotal;
                 }
            }


            $venda = Venda::create([
                'cliente_id' => $request->cliente_id,
                'forma_pagamento' => $request->forma_pagamento,
                'tipo_pagamento' => $request->tipo_pagamento,
                'valor_total' => $totalVenda, 
            ]);

             if (!empty($itens)) { 
                 foreach ($itens as $itemData) {
                     $venda->itens()->create([
                         'produto_id' => $itemData['produto_id'],
                         'quantidade' => $itemData['quantidade'],
                         'preco_unitario' => $itemData['preco_final'], 
                         'subtotal' => $itemData['quantidade'] * $itemData['preco_final'], 
                     ]);
                 }
             }
          
            if ($request->forma_pagamento === 'parcelado' && !empty($parcelas)) {
                foreach ($parcelas as $p) {
                    $venda->parcelas()->create([
                        'numero' => $p['numero'],
                        'valor' => $p['valor'],
                        'vencimento' => $p['vencimento'],
                        'tipo_pagamento' => $p['tipo_pagamento'] ?? $request->tipo_pagamento, 
                        'status' => $p['status'] ?? 'aberto',
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('vendas.index')->with('success', 'Venda registrada com sucesso.');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Erro ao salvar venda: ' . $e->getMessage(), ['exception' => $e, 'request' => $request->all()]);
            return back()->withErrors(['erro_salvar' => 'Erro interno ao registrar venda: ' . $e->getMessage()])->withInput();
        }
    }

     public function storeItem(Request $request)
     {
         $request->validate([
             'venda_id' => 'required|exists:vendas,id',
             'produto_id' => 'required|exists:produtos,id',
             'quantidade' => 'required|integer|min:1',
             'preco_unitario' => 'required|numeric|min:0',
         ]);

         $venda = Venda::findOrFail($request->venda_id);

         DB::beginTransaction();
         try {
             $subtotal = $request->quantidade * $request->preco_unitario;

             $item = $venda->itens()->create([ 
                 'produto_id' => $request->produto_id,
                 'quantidade' => $request->quantidade,
                 'preco_unitario' => $request->preco_unitario,
                 'subtotal' => $subtotal,
             ]);

             // Recalcula o total da venda principal
             $venda->valor_total = $venda->itens()->sum('subtotal'); 
             $venda->save();

             DB::commit();
            
             return redirect()->route('vendas.edit', $request->venda_id)->with('success', 'Produto adicionado à venda.');

         } catch (\Exception $e) {
             DB::rollback();
             \Log::error('Erro ao adicionar item à venda: ' . $e->getMessage(), ['exception' => $e, 'request' => $request->all()]);
             return back()->withErrors(['erro_item' => 'Erro ao adicionar item: ' . $e->getMessage()])->withInput();
         }
     }

    public function destroyItem($id)
    {
         // ... (código para excluir um item individualmente, parece ok) ...
         $item = VendaItem::with('venda')->findOrFail($id);
         $venda = $item->venda;

         DB::beginTransaction();
         try {
             $item->delete(); // Exclui o item

             // Recalcula o total da venda principal
             if ($venda) { // Verifica se a venda pai ainda existe
                 $venda->valor_total = $venda->itens()->sum('subtotal'); // Soma os subtotais restantes
                 $venda->save();
             }

             DB::commit();
             // Redireciona de volta para a tela de edição da venda
             return redirect()->route('vendas.edit', $venda->id)->with('success', 'Item excluído e total atualizado.');

         } catch (\Exception $e) {
             DB::rollback();
             \Log::error('Erro ao excluir item da venda: ' . $e->getMessage(), ['exception' => $e]);
             return back()->withErrors(['erro_excluir_item' => 'Erro ao excluir item: ' . $e->getMessage()]);
         }
    }

     public function updateMultiplos(Request $request, $vendaId)
     {

         $venda = Venda::with('itens')->findOrFail($vendaId); // Eager load itens para manipulação local

         $request->validate([
             'itens' => 'required|array', // Valida que 'itens' é um array (vem direto dos inputs)
             'itens.*.id' => 'required|exists:venda_itens,id',
             'itens.*.produto_id' => 'required|exists:produtos,id', // Verifique se o produto existe
             'itens.*.quantidade' => 'required|integer|min:1',
             // Assumindo que o input na tela de edição para preço é name="itens[...][preco_unitario]"
             'itens.*.preco_unitario' => 'required|numeric|min:0',
             'itens.*.remover' => 'nullable|boolean', // Campo hidden para marcar remoção
         ]);

         DB::beginTransaction();
         try {
             // Iterar sobre os itens recebidos no request
             foreach ($request->itens as $itemData) {
                 // Encontrar o item existente na venda carregada ou no BD (find no BD pode ser mais seguro se a coleção carregada não estiver completa)
                 $item = $venda->itens()->find($itemData['id']); // Use o relacionamento para encontrar no BD
                 if (!$item) continue; // Pula se o item não for encontrado (ou foi deletado por outra requisição)

                 // Lógica de remoção vs atualização
                 // O campo 'remover' virá como '0' ou '1' (string) do input hidden
                 if (isset($itemData['remover']) && (bool)$itemData['remover']) { // Converte para booleano
                     $item->delete(); // Exclui o item
                 } else {
                     // Validações específicas para o item antes de atualizar (já feitas acima pelo validate)
                     $preco = floatval($itemData['preco_unitario']);
                     $quantidade = intval($itemData['quantidade']);
                     $subtotal = $preco * $quantidade;

                     // Atualiza os dados do item existente
                     $item->update([
                         'produto_id' => $itemData['produto_id'], // Permite mudar o produto
                         'quantidade' => $quantidade,
                         'preco_unitario' => $preco,
                         'subtotal' => $subtotal,
                     ]);
                 }
             }

             // Recalcular o valor total da venda após todas as operações (atualizações/remoções)
             // Acessa os itens atualizados/restantes através do relacionamento para somar os subtotais no banco de dados
             $venda->valor_total = $venda->itens()->sum('subtotal');
             $venda->save(); // Salva o novo total na venda principal

             DB::commit();
             return redirect()->route('vendas.edit', $vendaId)->with('success', 'Alterações nos itens salvas com sucesso.');

         } catch (\Exception $e) {
             DB::rollback();
             \Log::error('Erro ao atualizar múltiplos itens da venda: ' . $e->getMessage(), ['exception' => $e, 'request' => $request->all()]);
             return back()->withErrors(['erro_atualizar_itens' => 'Erro ao salvar alterações nos itens: ' . $e->getMessage()])->withInput();
         }
     }


    public function destroy($id)
    {
         // ... (código para excluir uma venda, parece ok) ...
         $venda = Venda::findOrFail($id);

         DB::beginTransaction();
         try {
             $venda->delete(); // Exclui a venda principal

             DB::commit();
             return redirect()->route('vendas.index')->with('success', 'Venda excluída com sucesso.');

         } catch (\Exception $e) {
             DB::rollback();
             \Log::error('Erro ao excluir venda: ' . $e->getMessage(), ['exception' => $e]);
             return back()->withErrors(['erro_excluir_venda' => 'Erro ao excluir venda: ' . $e->getMessage()]);
         }
    }
}