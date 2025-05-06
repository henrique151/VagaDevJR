<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VendaController;

Route::get('/', function () {
    return view('welcome');
});

// Rota de Cadastro de Usuario
Route::get('/usuarios/cadastrar', [UsuarioController::class, 'criar'])->name('usuarios.criar');
Route::post('/usuarios', [UsuarioController::class, 'salvar'])->name('usuarios.salvar');

// Rota de Cadastro de Produto
Route::get('/produtos/cadastrar', [ProdutoController::class, 'criar'])->name('produtos.criar');
Route::post('/produtos', [ProdutoController::class, 'salvar'])->name('produtos.salvar');

// Rota de Cadastro de Venda
Route::get('/vendas/criar', [VendaController::class, 'criar'])->name('vendas.criar');
Route::post('/vendas/salvar', [VendaController::class, 'salvar'])->name('vendas.salvar');
Route::get('/vendas', [VendaController::class, 'index'])->name('vendas.index');
