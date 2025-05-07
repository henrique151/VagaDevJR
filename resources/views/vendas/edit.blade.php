@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Venda #{{ $venda->id }}</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vendas.update', $venda->id) }}" method="POST" id="formEditarVendaPrincipal">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="cliente_id">Selecione o novo cliente</label>
            <select name="cliente_id" id="cliente_id" class="form-control" required>
                <option value="">Selecione o cliente</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}"
                        {{ old('cliente_id', $venda->cliente_id) == $cliente->id ? 'selected' : '' }}>
                        {{ $cliente->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        

        <h3 class="mt-5">Editar Pagamento</h3>
        <div id="editorPagamento" class="border p-3 rounded bg-light mb-4">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="editar_tipo_pagamento" class="form-label">Tipo de Pagamento</label>
                    <select id="editar_tipo_pagamento" class="form-select" name="tipo_pagamento">
                        <option value="">Selecione</option>
                        <option value="cartao_credito" {{ old('tipo_pagamento', $venda->tipo_pagamento) === 'cartao_credito' ? 'selected' : '' }}>Cartão de Crédito</option>
                        <option value="cartao_debito" {{ old('tipo_pagamento', $venda->tipo_pagamento) === 'cartao_debito' ? 'selected' : '' }}>Cartão de Débito</option>
                        <option value="boleto" {{ old('tipo_pagamento', $venda->tipo_pagamento) === 'boleto' ? 'selected' : '' }}>Boleto</option>
                        <option value="transferencia" {{ old('tipo_pagamento', $venda->tipo_pagamento) === 'transferencia' ? 'selected' : '' }}>Transferência</option>
                        <option value="dinheiro" {{ old('tipo_pagamento', $venda->tipo_pagamento) === 'dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="editar_forma_pagamento" class="form-label">Forma de Pagamento</label>
                    <select id="editar_forma_pagamento" class="form-select" name="forma_pagamento">
                        <option value="">Selecione</option>
                        <option value="avista" {{ old('forma_pagamento', $venda->forma_pagamento) === 'avista' ? 'selected' : '' }}>À Vista</option>
                        <option value="parcelado" {{ old('forma_pagamento', $venda->forma_pagamento) === 'parcelado' ? 'selected' : '' }}>Parcelado</option>
                    </select>
                </div>
            </div>
            <div id="avisoMensagem" class="alert alert-warning" style="display:none;"></div>
            <div id="editar_boxParcelas" style="display: {{ old('forma_pagamento', $venda->forma_pagamento) === 'parcelado' ? 'block' : 'none' }};">
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-md-3">
                        <label for="editar_qtdParcelas" class="form-label">Qtd. Parcelas</label>
                        <input type="number" id="editar_qtdParcelas" class="form-control"
                               value="{{ old('qtdParcelas', ($venda->parcelas ?? collect())->count() > 0 ? ($venda->parcelas ?? collect())->count() : '') }}" min="1"> {{-- Adicionado check com collect() --}}
                    </div>
                    <div class="col-md-4">
                        <label for="editar_vencimentoInicial" class="form-label">1º Vencimento</label>
                        <input type="date" id="editar_vencimentoInicial" class="form-control"
                               value="{{ old('vencimento_inicial', optional($venda->parcelas->first())->vencimento?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100" id="btnEditarGerarParcelas">Gerar Parcelas</button>
                    </div>
                </div>

                <table class="table table-striped table-bordered mt-3">
                    <tbody id="editar_listaParcelas">
                        <table class="table table-striped table-bordered mt-3">
                        <thead>
                            <tr>
                            <th>#</th>
                            <th>Data de Vencimento</th>
                            <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody id="editar_listaParcelas">
                            @forelse($venda->parcelas as $p)
                            <tr>
                                <td>{{ $p->numero }}</td>
                                <td>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    value="{{ $p->vencimento->format('Y-m-d') }}"
                                    onchange="parcelasEdicao[{{ $loop->index }}].vencimento = this.value"
                                >
                                </td>
                                <td>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    step="0.01" 
                                    value="{{ number_format($p->valor, 2, '.', '') }}"
                                    onchange="parcelasEdicao[{{ $loop->index }}].valor = parseFloat(this.value)"
                                >
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center">Nenhuma parcela cadastrada.</td></tr>
                            @endforelse
                        </tbody>
                        </table>
                    </tbody>
                </table>
            </div>
        </div>

        <input type="hidden" name="parcelas_data" id="parcelas_data">

        <button type="submit" class="btn btn-success">Salvar Alterações da Venda</button>
        <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>

    <hr class="my-5">

    @if($venda->itens && $venda->itens->count())
    <div class="mt-4">
        <h4>Editar Produtos desta venda:</h4>

        <form action="{{ route('venda_itens.updateMultiplos', $venda->id) }}" method="POST">
            @csrf
            @method('PUT')

            @php $valorTotalItens = 0; @endphp

            @foreach($venda->itens as $index => $item)
            @php $valorTotalItens += $item->preco_unitario * $item->quantidade; @endphp
            <div class="card mb-3 p-3">
                <input type="hidden" name="itens[{{ $index }}][id]" value="{{ $item->id }}">
                <input type="hidden" name="itens[{{ $index }}][remover]" value="0" id="remover_item_{{ $item->id }}">

                <div class="form-group mb-3">
                    <label>Produto</label>
                    <select name="itens[{{ $index }}][produto_id]" class="form-control">
                        @foreach($produtos as $produto)
                            <option value="{{ $produto->id }}"
                                {{ old("itens.$index.produto_id", $item->produto_id) == $produto->id ? 'selected' : '' }}>
                                {{ $produto->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Quantidade</label>
                    <input type="number" name="itens[{{ $index }}][quantidade]" class="form-control"
                            value="{{ old("itens.$index.quantidade", $item->quantidade) }}" min="1" required>
                </div>

                <div class="form-group mb-3">
                    <label>Preço Unitário</label>
                    <input type="number" step="0.01" name="itens[{{ $index }}][preco_unitario]" class="form-control"
                            value="{{ old("itens.$index.preco_unitario", $item->preco_unitario) }}" required>
                </div>

                <div class="form-group">
                    <a href="{{ route('venda_itens.destroy', $item->id) }}" class="btn btn-danger"
                       onclick="return confirm('Tem certeza que deseja excluir este item?')">Excluir Item</a>
                </div>
            </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Salvar alterações dos produtos</button>
        </form>

        <hr class="my-4">
        <h5>Valor Total Atual dos Itens: R$ {{ number_format($valorTotalItens, 2, ',', '.') }}</h5>
        <h5>Valor Total da Venda (Do Modelo): R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</h5>

    </div>
    @endif

    <hr class="my-5">

    <div class="mt-5">
        <h4>Adicionar novo produto à venda:</h4>

        <form action="{{ route('venda_itens.store') }}" method="POST">
            @csrf
            <input type="hidden" name="venda_id" value="{{ $venda->id }}">

            <div class="form-group mb-3">
                <label>Produto</label>
                <select name="produto_id" class="form-control" required>
                    <option value="">Selecione</option>
                    @foreach($produtos as $produto)
                        <option value="{{ $produto->id }}" {{ old('produto_id') == $produto->id ? 'selected' : '' }}>
                            {{ $produto->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label>Quantidade</label>
                <input type="number" name="quantidade" class="form-control" min="1" required value="{{ old('quantidade') }}">
            </div>

            <div class="form-group mb-3">
                <label>Preço Unitário</label>
                <input type="number" step="0.01" name="preco_unitario" class="form-control" required value="{{ old('preco_unitario') }}">
            </div>

            <button type="submit" class="btn btn-success">Adicionar Produto</button>
        </form>
    </div>

</div>

<script src="{{ asset('js/edit.js') }}"></script>
@endsection