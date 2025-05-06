@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Venda</h2>

    <form action="{{ route('vendas.update', $venda->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Cliente -->
        <div class="form-group">
            <label for="cliente_id">Selecione o novo cliente</label>
            <select name="cliente_id" class="form-control" required>
                <option value="">Selecione o cliente</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" 
                        {{ old('cliente_id', $venda->cliente_id) == $cliente->id ? 'selected' : '' }}>
                        {{ $cliente->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- CPF do Cliente -->
        <div class="form-group">
            <label for="cpf">CPF</label>
            <input type="text" name="cpf" class="form-control" value="{{ old('cpf', $venda->cliente->cpf ?? '') }}" required>
        </div>

        <!-- RG do Cliente -->
        <div class="form-group">
            <label for="rg">RG</label>
            <input type="text" name="rg" class="form-control" value="{{ old('rg', $venda->cliente->rg ?? '') }}" required>
        </div>

        <button type="submit" class="btn btn-success">Salvar Alterações</button>
        <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>

    @if($venda->itens && $venda->itens->count())
    <div class="mt-4">
        <h4>Editar Produtos desta venda:</h4>

        <form action="{{ route('venda_itens.updateMultiplos', $venda->id) }}" method="POST">
            @csrf
            @method('PUT')

            @php $valorTotal = 0; @endphp

            @foreach($venda->itens as $index => $item)
            @php $valorTotal += $item->preco_unitario * $item->quantidade; @endphp
            <div class="card mb-3 p-3">
                <input type="hidden" name="itens[{{ $index }}][id]" value="{{ $item->id }}">

                <div class="form-group">
                    <label>Produto</label>
                    <select name="itens[{{ $index }}][produto_id]" class="form-control">
                        @foreach($produtos as $produto)
                            <option value="{{ $produto->id }}"
                                {{ $item->produto_id == $produto->id ? 'selected' : '' }}>
                                {{ $produto->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Quantidade</label>
                    <input type="number" name="itens[{{ $index }}][quantidade]" class="form-control"
                           value="{{ $item->quantidade }}" min="1">
                </div>

                <div class="form-group">
                    <label>Preço Unitário</label>
                    <input type="number" step="0.01" name="itens[{{ $index }}][preco_unitario]" class="form-control"
                           value="{{ $item->preco_unitario }}" required>
                </div>

                <div class="form-group">
                    <a href="{{ route('venda_itens.destroy', $item->id) }}" class="btn btn-danger"
                       onclick="return confirm('Tem certeza que deseja excluir este item?')">Excluir Item</a>
                </div>
            </div>
            @endforeach

            <button type="submit" class="btn btn-primary">Salvar alterações dos produtos</button>
        </form>

        <hr>
        <h5>Valor Total da Venda: R$ {{ number_format($valorTotal, 2, ',', '.') }}</h5>
    </div>
    @endif

    <!-- Adicionar novo produto -->
    <div class="mt-5">
        <h4>Adicionar novo produto à venda:</h4>

        <form action="{{ route('venda_itens.store') }}" method="POST">
            @csrf
            <input type="hidden" name="venda_id" value="{{ $venda->id }}">

            <div class="form-group">
                <label>Produto</label>
                <select name="produto_id" class="form-control" required>
                    <option value="">Selecione</option>
                    @foreach($produtos as $produto)
                        <option value="{{ $produto->id }}">{{ $produto->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Quantidade</label>
                <input type="number" name="quantidade" class="form-control" min="1" required>
            </div>

            <div class="form-group">
                <label>Preço Unitário</label>
                <input type="number" step="0.01" name="preco_unitario" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Adicionar Produto</button>
        </form>

        <h3 class="mt-5">Editar Pagamento</h3>
        <div id="editorPagamento" class="border p-3 rounded bg-light">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="editar_tipo_pagamento" class="form-label">Tipo de Pagamento</label>
                    <select id="editar_tipo_pagamento" class="form-select" name="tipo_pagamento">
                        <option value="">Selecione</option>
                        <option value="cartao_credito" {{ $venda->tipo_pagamento === 'cartao_credito' ? 'selected' : '' }}>Cartão de Crédito</option>
                        <option value="cartao_debito" {{ $venda->tipo_pagamento === 'cartao_debito' ? 'selected' : '' }}>Cartão de Débito</option>
                        <option value="boleto" {{ $venda->tipo_pagamento === 'boleto' ? 'selected' : '' }}>Boleto</option>
                        <option value="transferencia" {{ $venda->tipo_pagamento === 'transferencia' ? 'selected' : '' }}>Transferência</option>
                        <option value="dinheiro" {{ $venda->tipo_pagamento === 'dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="editar_forma_pagamento" class="form-label">Forma de Pagamento</label>
                    <select id="editar_forma_pagamento" class="form-select" name="forma_pagamento">
                        <option value="">Selecione</option>
                        <option value="avista" {{ $venda->forma_pagamento === 'avista' ? 'selected' : '' }}>À Vista</option>
                        <option value="parcelado" {{ $venda->forma_pagamento === 'parcelado' ? 'selected' : '' }}>Parcelado</option>
                    </select>
                </div>
            </div>

            <div id="editar_boxParcelas" style="display: {{ $venda->forma_pagamento === 'parcelado' ? 'block' : 'none' }};">
                <div class="row">
                    <div class="col-md-3">
                        <label for="editar_qtdParcelas" class="form-label">Qtd. Parcelas</label>
                        <input type="number" id="editar_qtdParcelas" class="form-control" value="{{ count($venda->parcelas ?? []) }}" min="1">
                    </div>
                    <div class="col-md-4">
                        <label for="editar_vencimentoInicial" class="form-label">1º Vencimento</label>
                        <input type="date" id="editar_vencimentoInicial" class="form-control" value="{{ old('vencimento_inicial', $venda->parcelas->first()->vencimento ?? '') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" id="btnEditarGerarParcelas">Gerar Parcelas</button>
                    </div>
                </div>

                <table class="table table-striped table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Data de Vencimento</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody id="editar_listaParcelas"></tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

<script>
let parcelasEdicao = {!! json_encode($venda->parcelas ?? []) !!};

document.addEventListener('DOMContentLoaded', function () {
    // Definir inicialmente se o parcelamento deve ser exibido
    const forma = document.getElementById('editar_forma_pagamento').value;
    const boxParcelas = document.getElementById('editar_boxParcelas');
    if (forma === 'parcelado') {
        boxParcelas.style.display = 'block';
    }
    atualizarParcelasEditor();
});

document.getElementById('editar_forma_pagamento').addEventListener('change', function () {
    const box = document.getElementById('editar_boxParcelas');
    if (this.value === 'parcelado') {
        // Se a forma de pagamento for parcelado, exibe a caixa de parcelamento
        box.style.display = 'block';
    } else {
        // Se a forma de pagamento for à vista, esconde a caixa de parcelamento
        box.style.display = 'none';
        parcelasEdicao = [];  // Limpa as parcelas
        atualizarParcelasEditor();  // Atualiza a lista de parcelas
    }
});

document.getElementById('btnEditarGerarParcelas').addEventListener('click', function () {
    const totalVenda = calcularTotalVenda();
    const qtd = parseInt(document.getElementById('editar_qtdParcelas').value);
    const vencimentoInicialStr = document.getElementById('editar_vencimentoInicial').value;

    if (!qtd || qtd < 1 || !vencimentoInicialStr || totalVenda <= 0) {
        alert("Preencha os dados corretamente.");
        return;
    }

    const vencimentoInicial = new Date(vencimentoInicialStr);
    const valorParcela = parseFloat((totalVenda / qtd).toFixed(2));

    parcelasEdicao = [];

    for (let i = 0; i < qtd; i++) {
        const venc = new Date(vencimentoInicial);
        venc.setMonth(venc.getMonth() + i);
        parcelasEdicao.push({
            numero: i + 1,
            vencimento: venc.toISOString().split('T')[0],
            valor: valorParcela
        });
    }

    ajustarUltimaParcelaEditor();
    atualizarParcelasEditor();
});

function ajustarUltimaParcelaEditor() {
    const totalVenda = calcularTotalVenda();
    let somaAtual = 0;
    parcelasEdicao.forEach((p, i) => {
        if (i !== parcelasEdicao.length - 1) somaAtual += p.valor;
    });
    const restante = parseFloat((totalVenda - somaAtual).toFixed(2));
    parcelasEdicao[parcelasEdicao.length - 1].valor = restante;
}

function atualizarParcelasEditor() {
    const tbody = document.getElementById('editar_listaParcelas');
    tbody.innerHTML = '';
    if (parcelasEdicao.length > 0) {
        parcelasEdicao.forEach(p => {
            tbody.innerHTML += `
                <tr>
                    <td>${p.numero}</td>
                    <td><input type="date" class="form-control" value="${p.vencimento}" onchange="parcelasEdicao[${p.numero - 1}].vencimento = this.value"></td>
                    <td><input type="number" class="form-control" step="0.01" value="${p.valor}" onchange="parcelasEdicao[${p.numero - 1}].valor = parseFloat(this.value)"></td>
                </tr>
            `;
        });
    }
}
</script>
