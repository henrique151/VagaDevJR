@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Vendas Realizadas</h2>

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
