<div class="form-group">
    <label for="cliente_id">Cliente</label>
    <select name="cliente_id" id="cliente_id" class="form-control">
        <option value="">Selecione um Cliente</option>
        @foreach($clientes as $cliente)
            <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
        @endforeach
    </select>
</div>
