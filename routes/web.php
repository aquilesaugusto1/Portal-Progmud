<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaParceiraController;
use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ApontamentoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\AprovacaoController;
use App\Http\Controllers\SugestaoController;
use App\Http\Controllers\TermoAceiteController;
use App\Http\Controllers\ContratoController;

Route::get('/', function () {
    return view('welcome');
});

// Grupo 1: Rotas que só exigem autenticação básica (login)
Route::middleware(['auth'])->group(function () {
    Route::get('/termo-de-aceite', [TermoAceiteController::class, 'show'])->name('termo.aceite');
    Route::post('/termo-de-aceite', [TermoAceiteController::class, 'accept'])->name('termo.accept');
});

// Grupo 2: Rotas principais da aplicação, que exigem login, email verificado E termo de aceite
Route::middleware(['auth', 'verified', \App\Http\Middleware\VerificarTermoAceite::class])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/apontamentos', [ApontamentoController::class, 'index'])->name('apontamentos.index');
    Route::post('/apontamentos', [ApontamentoController::class, 'store'])->name('apontamentos.store');
    Route::delete('/apontamentos/{apontamento}', [ApontamentoController::class, 'destroy'])->name('apontamentos.destroy');
    Route::get('/api/agendas', [ApontamentoController::class, 'events'])->name('api.agendas');

    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::post('/relatorios', [RelatorioController::class, 'gerar'])->name('relatorios.gerar');

    // Rota da API para buscar consultores de um contrato
    Route::get('/api/contratos/{contrato}/consultores', [AgendaController::class, 'getConsultoresPorContrato'])->name('api.contratos.consultores');

    Route::resource('agendas', AgendaController::class);

    // Subgrupo para rotas de Admin, Coordenadores e TechLead
    Route::middleware('role:admin,coordenador_operacoes,coordenador_tecnico,techlead')->group(function () {
        Route::get('/enviar-agendas', [EmailController::class, 'create'])->name('email.agendas.create');
        Route::post('/enviar-agendas', [EmailController::class, 'send'])->name('email.agendas.send');

        Route::get('/aprovacoes', [AprovacaoController::class, 'index'])->name('aprovacoes.index');
        Route::post('/aprovacoes/{apontamento}/aprovar', [AprovacaoController::class, 'aprovar'])->name('aprovacoes.aprovar');
        Route::post('/aprovacoes/{apontamento}/rejeitar', [AprovacaoController::class, 'rejeitar'])->name('aprovacoes.rejeitar');

        Route::resource('sugestoes', SugestaoController::class)->except(['show', 'edit', 'update', 'destroy']);
    });

    // Subgrupo apenas para rotas de Admin e Coordenadores
     Route::middleware('role:admin,coordenador_operacoes,coordenador_tecnico')->group(function () {
        Route::patch('contratos/{contrato}/toggle-status', [ContratoController::class, 'toggleStatus'])->name('contratos.toggleStatus');
        Route::resource('contratos', ContratoController::class)->except(['destroy']);
    });

    // Subgrupo apenas para rotas de Admin
    Route::middleware('role:admin')->group(function () {
        Route::patch('colaboradores/{colaborador}/toggle-status', [ColaboradorController::class, 'toggleStatus'])->name('colaboradores.toggleStatus');
        Route::resource('colaboradores', ColaboradorController::class)->except(['destroy'])->parameters(['colaboradores' => 'colaborador']);
        
        Route::patch('empresas/{empresa}/toggle-status', [EmpresaParceiraController::class, 'toggleStatus'])->name('empresas.toggleStatus');
        Route::resource('empresas', EmpresaParceiraController::class)->except(['destroy']);
    });
});

require __DIR__.'/auth.php';
