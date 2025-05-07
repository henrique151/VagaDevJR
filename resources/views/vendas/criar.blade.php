@extends('layouts.app')

@section('content')

<div class="container">
    <h2>Cadastro de Venda</h2>

    {{-- Mensagens de sucesso --}}
    @if(session('sucesso'))
        <div class="alert alert-success">{{ session('sucesso') }}</div>
    @endif

    {{-- *** BLOCO PARA EXIBIR ERROS DE VALIDAÇÃO (MANTENHA VISÍVEL) *** --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <h5>Erros de Validação:</h5>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    {{-- *** FIM DO BLOCO DE ERROS *** --}}


    {{-- Adicione um ID ao formulário para facilitar a seleção no JS --}}
    <form action="{{ route('vendas.store') }}" method="POST" id="form-venda-criar">
        @csrf

        {{-- Inputs hidden que serão preenchidos pelo JavaScript --}}
        <input type="hidden" name="itens_data" id="itens-json"> {{-- Nome corrigido --}}
        <input type="hidden" name="parcelas_data" id="inputParcelas"> {{-- Nome corrigido --}}
        <input type="hidden" name="tipo_pagamento" id="inputTipoPagamento"> 
       
        @include('vendas.partials.cliente')
        @include('vendas.partials.itens') 
        @include('vendas.partials.pagamento') 
        <button type="submit" class="btn btn-success" id="btn-salvar-venda">Salvar Venda</button>
        <a href="{{ url('/') }}" class="btn btn-primary">Voltar</a>
    </form>

</div>

{{-- Carregue jQuery e Select2 antes do seu script vendas.js --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- Seu script personalizado --}}
<script src="{{ asset('js/vendas.js') }}"></script>

@endsection