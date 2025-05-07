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
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Data de Vencimento</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody id="editar_listaParcelas">
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
@endsection

@section('scripts')
<script>
let parcelasEdicao = {!! json_encode($venda->parcelas ?? []) !!};

document.addEventListener('DOMContentLoaded', function () {
    const formaPagamentoSelect = document.getElementById('editar_forma_pagamento');
    const boxParcelas = document.getElementById('editar_boxParcelas');
    const btnGerarParcelas = document.getElementById('btnEditarGerarParcelas');
    const qtdParcelasInput = document.getElementById('editar_qtdParcelas');
    const vencimentoInicialInput = document.getElementById('editar_vencimentoInicial');
    const tipoPagamentoSelect = document.getElementById('editar_tipo_pagamento');
    const formEditarVenda = document.getElementById('formEditarVendaPrincipal');
    const parcelasDataInput = document.getElementById('parcelas_data');

    function toggleParcelasBox() {
        if (formaPagamentoSelect.value === 'parcelado') {
            boxParcelas.style.display = 'block';
        } else {
            boxParcelas.style.display = 'none';
        }
    }

    function atualizarParcelasEditor() {
        const tbody = document.getElementById('editar_listaParcelas');
        tbody.innerHTML = '';
        if (parcelasEdicao.length > 0) {
            parcelasEdicao.forEach((p, index) => {
                 const vencimentoDate = new Date(p.vencimento + 'T12:00:00');
                 const vencimentoValue = isNaN(vencimentoDate) ? '' : vencimentoDate.toISOString().split('T')[0];

                tbody.innerHTML += `
                    <tr>
                        <td>${p.numero}</td>
                        <td>
                             <input type="date" class="form-control" value="${vencimentoValue}"
                                    onchange="parcelasEdicao[${index}].vencimento = this.value">
                        </td>
                        <td>
                            <input type="number" class="form-control" step="0.01" value="${parseFloat(p.valor).toFixed(2)}"
                                   onchange="parcelasEdicao[${index}].valor = parseFloat(this.value)">
                        </td>
                    </tr>
                `;
            });
        } else {
             tbody.innerHTML = '<tr><td colspan="3" class="text-center">Nenhuma parcela gerada.</td></tr>';
        }
    }

    function calcularTotalVenda() {
        return {{ $venda->valor_total ?? 0 }};
    }

    function ajustarUltimaParcelaEditor() {
        const totalVenda = calcularTotalVenda();
        if (parcelasEdicao.length === 0 || totalVenda <= 0) {
            return;
        }
        let somaAtual = 0;
        for (let i = 0; i < parcelasEdicao.length - 1; i++) {
             somaAtual += parseFloat(parcelasEdicao[i].valor);
        }

        const restante = parseFloat((totalVenda - somaAtual).toFixed(2));
        parcelasEdicao[parcelasEdicao.length - 1].valor = Math.max(0, restante);
    }

    formaPagamentoSelect.addEventListener('change', toggleParcelasBox);

    btnGerarParcelas.addEventListener('click', function () {
        const totalVenda = calcularTotalVenda();
        const qtd = parseInt(qtdParcelasInput.value);
        const vencimentoInicialStr = vencimentoInicialInput.value;
        const tipoPagamento = tipoPagamentoSelect.value;

        if (!qtd || qtd < 1 || !vencimentoInicialStr || totalVenda <= 0) {
            alert("Por favor, preencha a quantidade de parcelas e a data do primeiro vencimento, e verifique se o valor total da venda é maior que zero.");
            return;
        }

        const vencimentoInicial = new Date(vencimentoInicialStr);
        vencimentoInicial.setUTCHours(12, 0, 0, 0);

        const valorParcelaBase = totalVenda / qtd;

        parcelasEdicao = [];

        for (let i = 0; i < qtd; i++) {
            const venc = new Date(vencimentoInicial);
            venc.setMonth(venc.getMonth() + i);
            const formattedVencimento = venc.toISOString().split('T')[0];

            parcelasEdicao.push({
                numero: i + 1,
                vencimento: formattedVencimento,
                valor: parseFloat(valorParcelaBase.toFixed(2)),
                tipo_pagamento: tipoPagamento,
                status: 'aberto'
            });
        }

        ajustarUltimaParcelaEditor();
        atualizarParcelasEditor();
    });

    formEditarVenda.addEventListener('submit', function(event) {
         if (formaPagamentoSelect.value === 'parcelado') {
             ajustarUltimaParcelaEditor();
             parcelasDataInput.value = JSON.stringify(parcelasEdicao);
         } else {
              parcelasDataInput.value = '';
         }
    });

    toggleParcelasBox();
    atualizarParcelasEditor();
    ajustarUltimaParcelaEditor();
});
</script>
@endsection