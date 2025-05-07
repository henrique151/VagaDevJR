@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Vendas Realizadas</h2>


    <form method="GET" action="{{ route('vendas.index') }}" class="mb-4">
    <div class="row">
        <div class="col-md-3">
            <input type="text" name="produto" class="form-control" placeholder="Nome do produto" value="{{ request('produto') }}">
        </div>
        <div class="col-md-3">
            <input type="text" name="cpf" class="form-control" placeholder="CPF do cliente" value="{{ request('cpf') }}">
        </div>
        <div class="col-md-3">
            <input type="text" name="rg" class="form-control" placeholder="RG do cliente" value="{{ request('rg') }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Limpar</a>
        </div>
    </div>
    </form>
    @if($vendas->isEmpty())
        <p>Nenhuma venda registrada.</p>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID da Venda</th>
                    <th>ID do Cliente</th>
                    <th>Cliente</th>
                    <th>CPF</th> 
                    <th>RG</th>
                    <th>Data</th>
                    <th>Itens</th>
                    <th>Valor Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendas as $venda)
                    <tr>
                        <td>{{ $venda->id }}</td>
                        <td>{{ $venda->cliente->id }}</td> 
                        <td>{{ $venda->cliente->nome }}</td>                 
                        <td>{{ $venda->cliente->cpf }}</td> 
                        <td>{{ $venda->cliente->rg }}</td> 
                        <td>{{ $venda->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <ul class="mb-0">
                                @foreach($venda->itens as $item)
                                    <li>
                                        {{ $item->produto->nome }} - 
                                        {{ $item->quantidade }} x 
                                        R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            R$ {{ number_format($venda->valor_total, 2, ',', '.') }}
                        </td>
                        <td>
                            <!-- Botão Editar -->
                            <a href="{{ route('vendas.edit', $venda->id) }}" class="btn btn-sm btn-warning">Editar</a>

                            <!-- Formulário Excluir -->
                            <form action="{{ route('vendas.destroy', $venda->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta venda?')">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('vendas.criar') }}" class="btn btn-primary">Nova Venda</a>
    <a href="{{ url('/') }}" class="btn btn-primary">Voltar</a>
</div>
@endsection
