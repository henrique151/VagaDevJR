@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Cadastrar Produto</h2>

    @if(session('sucesso'))
        <div class="alert alert-success">{{ session('sucesso') }}</div>
    @endif

    <form action="{{ route('produtos.salvar') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Produto</label>
            <input type="text" class="form-control @error('nome') is-invalid @enderror" name="nome" value="{{ old('nome') }}" required>
            @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="preco" class="form-label">Preço</label>
            <input type="number" step="0.01" class="form-control @error('preco') is-invalid @enderror" name="preco" value="{{ old('preco') }}" required>
            @error('preco') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="quantidade" class="form-label">Quantidade</label>
            <input type="number" class="form-control @error('quantidade') is-invalid @enderror" name="quantidade" value="{{ old('quantidade', 1) }}" required>
            @error('quantidade') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-success">Cadastrar Produto</button>
        <a href="{{ url('/') }}" class="btn btn-success">Voltar</a>
    </form>
</div>
@endsection
