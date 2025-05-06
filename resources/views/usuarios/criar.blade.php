@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Cadastrar Cliente</h2>

    @if(session('sucesso'))
        <div class="alert alert-success">{{ session('sucesso') }}</div>
    @endif

    <form action="{{ route('usuarios.salvar') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" class="form-control @error('nome') is-invalid @enderror" name="nome" value="{{ old('nome') }}" required>
            @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="rg" class="form-label">RG</label>
            <input type="text" class="form-control @error('rg') is-invalid @enderror" name="rg" value="{{ old('rg') }}" required>
            @error('rg') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="cpf" class="form-label">CPF</label>
            <input type="text" class="form-control @error('cpf') is-invalid @enderror" name="cpf" value="{{ old('cpf') }}" required>
            @error('cpf') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Cadastrar</button>
        <a href="{{ url('/') }}" class="btn btn-primary">Voltar</a>
    </form>
</div>
@endsection
