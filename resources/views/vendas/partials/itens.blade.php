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
