@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cadastro de Venda</h2>

    @if(session('sucesso'))
        <div class="alert alert-success">{{ session('sucesso') }}</div>
    @endif

    <form action="{{ route('vendas.salvar') }}" method="POST" id="form-venda">
        @csrf
        <input type="hidden" name="itens" id="itens-json">
        <input type="hidden" name="parcelas" id="inputParcelas">
        <input type="hidden" name="tipo_pagamento" id="inputTipoPagamento">

        <!-- Cliente -->
        <div class="form-group">
            <label for="cliente_id">Cliente</label>
            <select name="cliente_id" id="cliente_id" class="form-control">
                <option value="">Selecione um Cliente</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                @endforeach
            </select>
        </div>

        <!-- Itens da Venda -->
        <h4 class="mt-4">Itens da Venda</h4>
        <table class="table" id="itens-venda">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Preço Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                       <select name="produto_id" class="form-control produto">
                            <option value="">Selecione um Produto</option>
                            @foreach($produtos as $produto)
                                <option value="{{ $produto->id }}" data-preco="{{ $produto->preco }}">{{ $produto->nome }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="quantidade" class="form-control quantidade" value="1"></td>
                    <td><input type="number" name="preco_unitario" class="form-control preco_unitario" step="0.01"></td>
                    <td><input type="text" class="form-control subtotal" readonly></td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-primary" id="btn-adicionar-item">Adicionar Item</button>

        <h4 class="mt-4">Itens Adicionados:</h4>
        <ul id="itens-adicionados" class="list-group mb-4"></ul>

        <!-- Pagamento -->
        <h3 class="mt-5">Pagamento</h3>

        <!-- Tipo de Pagamento (cartão, boleto, etc) -->
        <div class="row mt-3">
            <div class="col-md-4">
                <label for="tipo_pagamento_detalhado" class="form-label">Tipo de Pagamento</label>
                <select name="tipo_pagamento_detalhado" id="tipo_pagamento_detalhado" class="form-select">
                    <option value="">Selecione o tipo</option>
                    <option value="cartao_credito">Cartão de Crédito</option>
                    <option value="cartao_debito">Cartão de Débito</option>
                    <option value="boleto">Boleto</option>
                    <option value="transferencia">Transferência</option>
                    <option value="dinheiro">Dinheiro</option>
                </select>
            </div>
        </div>

        <!-- Forma de Pagamento (à vista, parcelado, personalizado) -->
        <div class="row mt-3">
            <div class="col-md-4">
                <label for="forma_pagamento">Forma de Pagamento</label>
                <select name="forma_pagamento" id="forma_pagamento" class="form-select" required>
                    <option value="">Selecione uma forma de pagamento</option>
                    <option value="avista">À Vista</option>
                    <option value="parcelado">Parcelado</option>
                </select>
            </div>
        </div>

        <!-- Se parcelado: gerar parcelas -->
        <div id="boxParcelas" class="mt-4" style="display: none;">
            <div class="row">
                <div class="col-md-2">
                    <label>Qtd. Parcelas</label>
                    <input type="number" min="1" id="qtdParcelas" class="form-control">
                </div>
                <div class="col-md-4">
                    <label>1º Vencimento</label>
                    <input type="date" id="dataVencimentoInicial" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-secondary" id="gerarParcelas">Gerar Parcelas</button>
                </div>
            </div>

            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Data de Vencimento</th>
                        <th>Valor</th>
                        <th>Tipo de Pagamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="listaParcelas"></tbody>
            </table>
        </div>



        <button type="submit" class="btn btn-success" id="btn-salvar-venda">Salvar Venda</button>
        <a href="{{ url('/') }}" class="btn btn-primary">Voltar</a>
    </form>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="js/venda.js"></script>
<script src="js/formulario.js"></script>
<script src="js/itens.js"></script>

<script>
let parcelas = [];
function calcularTotalVenda() {
    let total = 0;
    $('#itens-adicionados li').each(function () {
        const subtotal = parseFloat($(this).data('subtotal')) || 0;
        total += subtotal;
    });
    return total;
}

document.getElementById('btn-salvar-venda').addEventListener('click', function () {
    // Verificando se todos os campos obrigatórios estão preenchidos
    const clienteId = document.getElementById('cliente_id').value;
    const formaPagamento = document.getElementById('forma_pagamento').value;
    const qtdParcelas = document.getElementById('qtdParcelas').value;
    const dataVencimentoInicial = document.getElementById('dataVencimentoInicial').value;
    const tipoPagamento = document.getElementById('tipo_pagamento_detalhado').value;
    const totalVenda = calcularTotalVenda();

    // Validando campos obrigatórios
    if (!clienteId) {
        alert('Por favor, selecione um cliente.');
        return; 
    }
    if(!btn-adicionar-item) {
        alert('Por favor, adicione um item');
        return; 
    }
    

    if (formaPagamento === 'parcelado') {
        if (!qtdParcelas || qtdParcelas < 1) {
            alert('Informe a quantidade de parcelas.');
            return;
        }

        if (!dataVencimentoInicial) {
            alert('Informe a data de vencimento inicial.');
            return;
        }

        if (totalVenda <= 0) {
            alert('O total da venda deve ser maior que 0.');
            return;
        }

        if (!tipoPagamento) {
            alert('Selecione o tipo de pagamento detalhado.');
            return;
        }
    }
});

document.getElementById('listaParcelas').addEventListener('change', function (e) {
    if (e.target && e.target.nodeName === 'INPUT' && e.target.type === 'date') {
        const index = e.target.closest('tr').rowIndex - 1;
        const novaData = e.target.value;
        atualizarParcelasAutomaticamente(index, novaData);
    }
});

function atualizarParcelasAutomaticamente(index, novaDataStr) {
    const novaData = new Date(novaDataStr);
    if (index === 0) {
        parcelas.forEach((p, i) => {
            if (i > 0) {
                const venc = new Date(novaData);
                venc.setMonth(venc.getMonth() + i);
                p.vencimento = venc.toISOString().split('T')[0];
            } else {
                p.vencimento = novaDataStr;
            }
        });
        atualizarTabelaParcelas();
    }
}

function atualizarTabelaParcelas() {
    const tbody = document.getElementById('listaParcelas');
    tbody.innerHTML = '';
    parcelas.forEach((p, i) => {
        tbody.innerHTML += `
            <tr>
                <td>${p.numero}</td>
                <td><input type="date" class="form-control" value="${p.vencimento}" onchange="validarIntervaloDatas(${i}, this.value)"></td>
                <td><input type="number" step="0.01" class="form-control" value="${p.valor}" onchange="editarParcela(${i}, this.value)"></td>
                <td>
                    <select class="form-control" onchange="editarTipoPagamento(${i}, this.value)">
                        <option value="cartao_credito" ${p.tipo_pagamento === 'cartao_credito' ? 'selected' : ''}>Cartão de Crédito</option>
                        <option value="cartao_debito" ${p.tipo_pagamento === 'cartao_debito' ? 'selected' : ''}>Cartão de Débito</option>
                        <option value="boleto" ${p.tipo_pagamento === 'boleto' ? 'selected' : ''}>Boleto</option>
                        <option value="transferencia" ${p.tipo_pagamento === 'transferencia' ? 'selected' : ''}>Transferência</option>
                        <option value="dinheiro" ${p.tipo_pagamento === 'dinheiro' ? 'selected' : ''}>Dinheiro</option>
                    </select>
                </td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="removerParcela(${i})">Excluir</button></td>
            </tr>
        `;
    });
    document.getElementById('inputParcelas').value = JSON.stringify(parcelas);
}

function editarTipoPagamento(index, tipoPagamento) {
    parcelas[index].tipo_pagamento = tipoPagamento;
    document.getElementById('inputParcelas').value = JSON.stringify(parcelas);
}

function editarParcela(index, novoValor) {
    novoValor = parseFloat(novoValor);
    if (isNaN(novoValor) || novoValor < 0) return;
    parcelas[index].valor = novoValor;
    ajustarUltimaParcela();
    atualizarTabelaParcelas();
}

function ajustarUltimaParcela() {
    const totalVenda = calcularTotalVenda();
    let somaAtual = 0;
    parcelas.forEach((p, i) => {
        if (i !== parcelas.length - 1) {
            somaAtual += p.valor;
        }
    });
    const ultimaParcela = parcelas.length - 1;
    const restante = (totalVenda - somaAtual).toFixed(2);
    parcelas[ultimaParcela].valor = parseFloat(restante);
}

function removerParcela(index) {
    parcelas.splice(index, 1);
    parcelas.forEach((p, i) => p.numero = i + 1);
    ajustarUltimaParcela();
    atualizarTabelaParcelas();
}

function validarIntervaloDatas(index, novaDataStr) {
    const novaData = new Date(novaDataStr);
    if (index > 0) {
        const anterior = new Date(parcelas[index - 1].vencimento);
        const diffMes = novaData.getMonth() - anterior.getMonth() + 12 * (novaData.getFullYear() - anterior.getFullYear());
        if (diffMes !== 1) {
            alert('As parcelas devem ter um intervalo de exatamente 1 mês.');
            atualizarTabelaParcelas();
            return;
        }
    }
    parcelas[index].vencimento = novaDataStr;
    document.getElementById('inputParcelas').value = JSON.stringify(parcelas);
}

document.getElementById('forma_pagamento').addEventListener('change', function () {
    const box = document.getElementById('boxParcelas');
    if (this.value === 'parcelado') {
        box.style.display = 'block';
    } else {
        box.style.display = 'none';
        parcelas = [];
        atualizarTabelaParcelas();
    }
});

document.getElementById('gerarParcelas').addEventListener('click', function () {
    const clienteId = document.getElementById('cliente_id').value; 
    const qtd = parseInt(document.getElementById('qtdParcelas').value);
    const dataInicialStr = document.getElementById('dataVencimentoInicial').value;
    const total = calcularTotalVenda();
    const tipoPagamento = document.getElementById('tipo_pagamento_detalhado').value;

    if (!qtd || qtd < 1 || !dataInicialStr || total <= 0 || !tipoPagamento || !clienteId) {
        alert('Preencha todos os dados corretamente.');
        return;
    }

    const dataInicial = new Date(dataInicialStr);
    parcelas = [];
    const valorParcela = parseFloat((total / qtd).toFixed(2));

    for (let i = 0; i < qtd; i++) {
        const venc = new Date(dataInicial);
        venc.setMonth(venc.getMonth() + i);
        parcelas.push({
            numero: i + 1,
            valor: valorParcela,
            vencimento: venc.toISOString().split('T')[0],
            tipo_pagamento: tipoPagamento
        });
    }

    ajustarUltimaParcela();
    atualizarTabelaParcelas();
});

// Itens da Venda
$(document).ready(function() {
    let itemCount = 0;

    $('#cliente_id, .produto').select2({ width: '100%' });

    function calcularSubtotal() {
        $('#itens-venda tbody tr').each(function() {
            const qtd = parseFloat($(this).find('.quantidade').val()) || 0;
            const preco = parseFloat($(this).find('.preco_unitario').val()) || 0;
            const subtotal = qtd * preco;
            $(this).find('.subtotal').val(subtotal.toFixed(2));
        });
    }

    function atualizarItensJson() {
        const itens = [];
        $('#itens-adicionados li').each(function() {
            itens.push({
                produto_id: $(this).data('produto-id'),
                quantidade: $(this).data('quantidade'),
                preco_inicial: $(this).data('preco-unitario'),
                preco_final: $(this).data('subtotal')
            });
        });
        $('#itens-json').val(JSON.stringify(itens));
    }

        $('#btn-adicionar-item').click(function() {
        const tr = $('#itens-venda tbody tr:first');
        const produtoId = tr.find('.produto').val();
        const produtoNome = tr.find('.produto option:selected').text();
        const quantidade = parseFloat(tr.find('.quantidade').val());
        const precoUnitario = parseFloat(tr.find('.preco_unitario').val());
        const subtotal = quantidade * precoUnitario;

        if (produtoId && quantidade > 0 && precoUnitario > 0) {
            itemCount++;
            $('#itens-adicionados').append(`
                <li class="list-group-item" data-item-id="${itemCount}" data-produto-id="${produtoId}" data-produto="${produtoNome}" data-quantidade="${quantidade}" data-preco-unitario="${precoUnitario}" data-subtotal="${subtotal}">
                    <strong>Item ${itemCount}:</strong> Código: ${produtoId}, Produto: ${produtoNome}, Quantidade: ${quantidade}, Preço Unitário: R$${precoUnitario.toFixed(2)}, Total: R$${subtotal.toFixed(2)}
                    <button type="button" class="btn btn-warning btn-sm float-end ms-2 btn-edit-item">Editar</button>
                    <button type="button" class="btn btn-danger btn-sm float-end btn-remove-item">Excluir</button>
                </li>
            `);
            atualizarItensJson();
            limparCampos();
        } else {
            alert('Preencha corretamente os campos antes de adicionar o item.');
        }
    });

    function limparCampos() {
        const tr = $('#itens-venda tbody tr:first');
        tr.find('.produto').val('').trigger('change');
        tr.find('.quantidade').val(1);
        tr.find('.preco_unitario').val('');
        tr.find('.subtotal').val('');
    }

        $(document).on('change', '.produto', function () {
        const preco = $(this).find('option:selected').data('preco');
        const tr = $(this).closest('tr');
        tr.find('.preco_unitario').val(preco ? preco.toFixed(2) : '');
        tr.find('.quantidade').trigger('change'); 
    });

    $(document).on('input change', '.quantidade, .preco_unitario, .produto', function() {
        calcularSubtotal();
    });


    $(document).on('click', '.btn-remove-item', function() {
        $(this).closest('li').remove();
        atualizarItensJson();
    });

    $(document).on('click', '.btn-edit-item', function() {
        const item = $(this).closest('li');
        const tr = $('#itens-venda tbody tr:first');
        tr.find('.produto').val(item.data('produto-id')).trigger('change');
        tr.find('.quantidade').val(item.data('quantidade'));
        tr.find('.preco_unitario').val(item.data('preco-unitario'));
        tr.find('.subtotal').val(item.data('subtotal'));
        item.remove();
        atualizarItensJson();
    });
    
});
</script>
@endsection  