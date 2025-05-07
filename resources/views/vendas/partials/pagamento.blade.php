<input type="hidden" name="parcelas" id="inputParcelas">
<h3 class="mt-5">Pagamento</h3>
<div class="row mt-3">
    <div class="col-md-4">
        <label for="tipo_pagamento_detalhado" class="form-label">Tipo de Pagamento</label>
        <select name="tipo_pagamento" id="tipo_pagamento_detalhado" class="form-select">
            <option value="">Selecione o tipo</option>
            <option value="cartao_credito">Cartão de Crédito</option>
            <option value="cartao_debito">Cartão de Débito</option>
            <option value="boleto">Boleto</option>
            <option value="transferencia">Transferência</option>
            <option value="dinheiro">Dinheiro</option>
        </select>
    </div>
</div>

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

