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

    @include('vendas.partials.cliente')
    @include('vendas.partials.itens')
    @include('vendas.partials.pagamento')

    <button type="submit" class="btn btn-success" id="btn-salvar-venda">Salvar Venda</button>
    <a href="{{ url('/') }}" class="btn btn-primary">Voltar</a>
</form>

</div>

<!-- Scripts -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/vendas.js') }}"></script>

@endsection 