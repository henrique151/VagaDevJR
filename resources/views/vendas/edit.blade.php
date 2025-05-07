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
@endsection

@section('scripts')
<script>
let parcelasEdicao = {!! json_encode($venda->parcelas ?? []) !!}.map(p => ({
    id: p.id, // Importante: preservar o ID da parcela existente
    numero: p.numero,
    vencimento: p.vencimento,
    valor: parseFloat(p.valor),
    tipo_pagamento: p.tipo_pagamento,
    status: p.status || 'aberto'
}));

document.addEventListener('DOMContentLoaded', function () {
    const formaPagamentoSelect = document.getElementById('editar_forma_pagamento');
    const boxParcelas = document.getElementById('editar_boxParcelas');
    const btnGerarParcelas = document.getElementById('btnEditarGerarParcelas');
    const qtdParcelasInput = document.getElementById('editar_qtdParcelas');
    const vencimentoInicialInput = document.getElementById('editar_vencimentoInicial');
    const tipoPagamentoSelect = document.getElementById('editar_tipo_pagamento');
    const formEditarVenda = document.getElementById('formEditarVendaPrincipal');
    const parcelasDataInput = document.getElementById('parcelas_data');
    const aviso = document.getElementById('avisoMensagem');

    // Função para formatar data para YYYY-MM-DD
    function formatarData(data) {
        if (!(data instanceof Date)) {
            if (typeof data === 'string') {
                // Tenta converter a string para um objeto Date
                data = new Date(data);
            } else {
                return '';
            }
        }
        
        if (isNaN(data.getTime())) {
            return '';
        }
        
        const ano = data.getFullYear();
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        const dia = String(data.getDate()).padStart(2, '0');
        
        return `${ano}-${mes}-${dia}`;
    }

    // Calcula o valor total da venda a partir dos itens
    function calcularTotalDaVenda() {
        // Esta função deve pegar o total dos itens da venda
        // Você pode implementar uma lógica mais robusta aqui 
        // para calcular o total com base nos itens atuais
        
        // Por enquanto, vamos usar o valor do elemento HTML que mostra o valor total
        const valorTotalText = document.querySelector('h5:contains("Valor Total Atual dos Itens")');
        if (valorTotalText) {
            const valorString = valorTotalText.textContent.replace('Valor Total Atual dos Itens: R$ ', '').replace('.', '').replace(',', '.');
            return parseFloat(valorString);
        }
        
        // Caso não encontre o elemento, tente obter do modelo
        const valorModeloText = document.querySelector('h5:contains("Valor Total da Venda")');
        if (valorModeloText) {
            const valorString = valorModeloText.textContent.replace('Valor Total da Venda (Do Modelo): R$ ', '').replace('.', '').replace(',', '.');
            return parseFloat(valorString);
        }
        
        // Se nenhum elemento for encontrado, use o valor das parcelas
        const valorTotalValendo = document.querySelectorAll('h5')[1]; // Pegando o segundo h5 que deve ser o valor total
        if (valorTotalValendo) {
            const valorString = valorTotalValendo.textContent.replace(/[^0-9,]/g, '').replace(',', '.');
            return parseFloat(valorString);
        }
        
        // Se ainda assim não encontrar, calcule das próprias parcelas ou use um valor padrão
        return parcelasEdicao.reduce((total, parcela) => total + parseFloat(parcela.valor || 0), 0) || 1000;
    }

    function toggleParcelasBox() {
        if (formaPagamentoSelect.value === 'parcelado') {
            boxParcelas.style.display = 'block';
        } else {
            boxParcelas.style.display = 'none';
        }
    }

    function atualizarParcelasEditor() {
        const tbody = document.getElementById('editar_listaParcelas');
        
        // Limpa o conteúdo atual
        tbody.innerHTML = '';
        
        if (parcelasEdicao.length > 0) {
            parcelasEdicao.forEach((p, index) => {
                // Garante que vencimento é uma string de data válida
                const vencimentoValue = formatarData(p.vencimento);
                
                // Cria uma nova linha na tabela
                const tr = document.createElement('tr');
                
                // Coluna do número da parcela
                const tdNumero = document.createElement('td');
                tdNumero.textContent = p.numero;
                tr.appendChild(tdNumero);
                
                // Coluna da data de vencimento
                const tdVencimento = document.createElement('td');
                const inputVencimento = document.createElement('input');
                inputVencimento.type = 'date';
                inputVencimento.className = 'form-control';
                inputVencimento.value = vencimentoValue;
                inputVencimento.setAttribute('data-index', index);
                inputVencimento.addEventListener('change', function() {
                    const idx = parseInt(this.getAttribute('data-index'));
                    alterarVencimentoParcelas(idx, this);
                });
                tdVencimento.appendChild(inputVencimento);
                tr.appendChild(tdVencimento);
                
                // Coluna do valor
                const tdValor = document.createElement('td');
                const inputValor = document.createElement('input');
                inputValor.type = 'number';
                inputValor.className = 'form-control';
                inputValor.step = '0.01';
                inputValor.value = parseFloat(p.valor || 0).toFixed(2);
                inputValor.setAttribute('data-index', index);
                inputValor.addEventListener('change', function() {
                    const idx = parseInt(this.getAttribute('data-index'));
                    alterarValorParcelas(idx, this);
                });
                tdValor.appendChild(inputValor);
                tr.appendChild(tdValor);
                
                // Adiciona a linha à tabela
                tbody.appendChild(tr);
            });
        } else {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 3;
            td.className = 'text-center';
            td.textContent = 'Nenhuma parcela gerada.';
            tr.appendChild(td);
            tbody.appendChild(tr);
        }
    }

    window.alterarVencimentoParcelas = function(index, input) {
        const novaData = new Date(input.value);
        
        // Verificar se a data é válida
        if (isNaN(novaData.getTime())) {
            alert("Data inválida. Por favor, insira uma data válida.");
            // Restaura a data anterior se disponível
            if (parcelasEdicao[index] && parcelasEdicao[index].vencimento) {
                input.value = formatarData(parcelasEdicao[index].vencimento);
            }
            return;
        }
        
        // Atualiza a data no modelo
        parcelasEdicao[index].vencimento = formatarData(novaData);
        
        // Opcional: Atualizar datas futuras com base nessa alteração
        if (index < parcelasEdicao.length - 1) {
            const intervaloEmMeses = 1; // Intervalo entre parcelas
            
            for (let i = index + 1; i < parcelasEdicao.length; i++) {
                const proximaData = new Date(novaData);
                proximaData.setMonth(proximaData.getMonth() + (i - index) * intervaloEmMeses);
                parcelasEdicao[i].vencimento = formatarData(proximaData);
            }
            
            // Atualiza a visualização na tela
            atualizarParcelasEditor();
        }
    };

    window.alterarValorParcelas = function(index, input) {
        const novoValor = parseFloat(input.value);
        if (isNaN(novoValor) || novoValor < 0) {
            input.value = parseFloat(parcelasEdicao[index].valor || 0).toFixed(2);
            return;
        }
        
        const valorAntigo = parseFloat(parcelasEdicao[index].valor || 0);
        parcelasEdicao[index].valor = novoValor;
        
        // Calcular o valor total da venda
        const totalVenda = calcularTotalDaVenda();
        
        // Calcular o total atual das parcelas
        const totalParcelas = parcelasEdicao.reduce((total, parcela) => total + parseFloat(parcela.valor || 0), 0);
        
        // Se o total das parcelas não bate com o valor da venda, ajustar a última parcela
        const diferenca = totalVenda - totalParcelas;
        
        if (Math.abs(diferenca) > 0.01) { // Tolerância de 1 centavo
            avisoMensagem.textContent = `Ajustando a última parcela para que o total seja igual ao valor da venda (${totalVenda.toFixed(2)})`;
            avisoMensagem.style.display = 'block';
            
            // Se esta é a última parcela, ajuste para o valor correto
            if (index === parcelasEdicao.length - 1) {
                parcelasEdicao[index].valor = novoValor + diferenca;
            } else {
                // Senão, ajusta a última parcela
                const ultimoIndex = parcelasEdicao.length - 1;
                const novoValorUltimaParcela = parseFloat(parcelasEdicao[ultimoIndex].valor || 0) + diferenca;
                
                // Verifica se o valor da última parcela não fica negativo
                if (novoValorUltimaParcela < 0) {
                    parcelasEdicao[index].valor = valorAntigo; // Reverte a alteração
                    avisoMensagem.textContent = "Alteração inválida: tornaria a última parcela negativa.";
                    input.value = valorAntigo.toFixed(2);
                } else {
                    parcelasEdicao[ultimoIndex].valor = novoValorUltimaParcela;
                }
            }
        } else {
            avisoMensagem.style.display = 'none';
        }
        
        atualizarParcelasEditor();
    };

    function distribuirValorParcelas(totalVenda, qtdParcelas) {
        const valorParcelaBase = totalVenda / qtdParcelas;
        let valorRestante = totalVenda;
        
        for (let i = 0; i < qtdParcelas; i++) {
            // Para as primeiras (n-1) parcelas, usa o valor base
            if (i < qtdParcelas - 1) {
                parcelasEdicao[i].valor = Math.floor(valorParcelaBase * 100) / 100; // Arredonda para baixo nos centavos
                valorRestante -= parcelasEdicao[i].valor;
            } else {
                // Para a última parcela, usa o valor restante (garantindo que a soma seja exata)
                parcelasEdicao[i].valor = Math.round(valorRestante * 100) / 100;
            }
        }
    }

    btnGerarParcelas.addEventListener('click', function () {
        const qtd = parseInt(qtdParcelasInput.value);
        const vencimentoInicialStr = vencimentoInicialInput.value;
        const tipoPagamento = tipoPagamentoSelect.value;
        const totalVenda = calcularTotalDaVenda();

        if (!qtd || qtd < 1 || !vencimentoInicialStr || isNaN(totalVenda) || totalVenda <= 0) {
            alert("Por favor, preencha a quantidade de parcelas e a data do primeiro vencimento, e verifique se o valor total da venda é maior que zero.");
            return;
        }

        const vencimentoInicial = new Date(vencimentoInicialStr);
        
        // Verifica se a data é válida
        if (isNaN(vencimentoInicial.getTime())) {
            alert("Data de vencimento inicial inválida. Por favor, insira uma data válida.");
            return;
        }
        
        // Inicializar array de parcelas
        parcelasEdicao = [];

        for (let i = 0; i < qtd; i++) {
            const venc = new Date(vencimentoInicial);
            venc.setMonth(vencimentoInicial.getMonth() + i);
            
            parcelasEdicao.push({
                numero: i + 1,
                vencimento: formatarData(venc),
                valor: 0, 
                tipo_pagamento: tipoPagamento,
                status: 'aberto'
            });
        }

        distribuirValorParcelas(totalVenda, qtd);
        atualizarParcelasEditor();
        
        // Debug
        console.log("Parcelas geradas:", JSON.stringify(parcelasEdicao));
    });

    formaPagamentoSelect.addEventListener('change', toggleParcelasBox);

    formEditarVenda.addEventListener('submit', function(event) {
        if (formaPagamentoSelect.value === 'parcelado') {
            // Verifica se existem parcelas
            if (parcelasEdicao.length === 0) {
                event.preventDefault();
                alert("Por favor, gere as parcelas antes de salvar.");
                return;
            }
            
            // Verifica se o total das parcelas corresponde ao total da venda
            const totalVenda = calcularTotalDaVenda();
            const totalParcelas = parcelasEdicao.reduce((total, p) => total + parseFloat(p.valor || 0), 0);
            
            if (Math.abs(totalVenda - totalParcelas) > 0.01) { // Tolerância de 1 centavo
                if (confirm(`O total das parcelas (${totalParcelas.toFixed(2)}) não corresponde ao valor total da venda (${totalVenda.toFixed(2)}). Deseja ajustar automaticamente?`)) {
                    // Ajusta a última parcela para igualar o total
                    const diferenca = totalVenda - totalParcelas;
                    if (parcelasEdicao.length > 0) {
                        const ultimoIndex = parcelasEdicao.length - 1;
                        parcelasEdicao[ultimoIndex].valor = parseFloat(parcelasEdicao[ultimoIndex].valor || 0) + diferenca;
                    }
                } else {
                    event.preventDefault();
                    return;
                }
            }
            
            // Garante que todas as parcelas têm data de vencimento no formato correto
            for (let i = 0; i < parcelasEdicao.length; i++) {
                parcelasEdicao[i].vencimento = formatarData(parcelasEdicao[i].vencimento);
            }
        }
        
        // Log para debugar
        console.log("Enviando parcelas:", JSON.stringify(parcelasEdicao));
        
        // Quando o formulário for enviado, salvar as parcelas em JSON
        parcelasDataInput.value = JSON.stringify(parcelasEdicao);
    });

    // Inicialização
    toggleParcelasBox();
    atualizarParcelasEditor();
});
</script>
@endsection