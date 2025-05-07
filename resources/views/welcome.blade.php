@extends('layouts.app')

@section('content')
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">DC Tecnologia</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('usuarios.criar') }}">Cadastrar Cliente</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('produtos.criar') }}">Cadastrar Produtos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('vendas.criar') }}">Cadastrar Vendas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('vendas.index') }}">Listar Vendas</a>
        </li>
        </li>
      </ul>
    </div>
  </div>
</nav>
@endsection
