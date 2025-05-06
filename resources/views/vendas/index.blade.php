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
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Itens</th>
                    <th>Total</th>
                    <th>Pagamento</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendas as $venda)
                    <tr>
                        <td>{{ $venda->id }}</td>
                        <td>{{ $venda->cliente->nome }}</td>
                        <td>{{ $venda->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <ul>
                                @foreach($venda->itens as $item)
                                    <li>{{ $item->produto->nome }} - {{ $item->quantidade }} x R${{ number_format($item->preco_final, 2, ',', '.') }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            R$ {{ number_format($venda->itens->sum('preco_final'), 2, ',', '.') }}
                        </td>
                        <td>
                            @if($venda->parcelas->count() > 0)
                                Parcelado ({{ $venda->parcelas->count() }}x)
                                <ul>
                                    @foreach($venda->parcelas as $parcela)
                                        <li>
                                            {{ \Carbon\Carbon::parse($parcela->vencimento)->format('d/m/Y') }} - 
                                            R$ {{ number_format($parcela->valor, 2, ',', '.') }} - 
                                            {{ ucfirst(str_replace('_', ' ', $parcela->tipo_pagamento)) }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                À Vista
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('vendas.create') }}" class="btn btn-primary">Nova Venda</a>
</div>
@endsection
