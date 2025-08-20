<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ApontamentoController;
use App\Http\Controllers\AprovacaoController;
use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\EmpresaParceiraController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\SugestaoController;
use App\Http\Controllers\TermoAceiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/termo-de-aceite', [TermoAceiteController::class, 'show'])->name('termo.aceite');
    Route::post('/termo-de-aceite', [TermoAceiteController::class, 'accept'])->name('termo.accept');
});

Route::middleware(['auth', 'verified', \App\Http\Middleware\VerificarTermoAceite::class])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/apontamentos', [ApontamentoController::class, 'index'])->name('apontamentos.index');
    Route::get('/apontamentos/create', [ApontamentoController::class, 'create'])->name('apontamentos.create');
    Route::post('/apontamentos', [ApontamentoController::class, 'store'])->name('apontamentos.store');
    Route::delete('/apontamentos/{apontamento}', [ApontamentoController::class, 'destroy'])->name('apontamentos.destroy');
    
    // CORREÇÃO: Renomeando a rota da API para o nome correto que o calendário espera.
    Route::get('/api/apontamentos/events', [ApontamentoController::class, 'events'])->name('api.apontamentos.events');
    Route::get('/api/agendas/{agenda}/details', [ApontamentoController::class, 'getAgendaDetails'])->name('api.agendas.details');

    Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
    Route::post('/relatorios/gerar', [RelatorioController::class, 'gerar'])->name('relatorios.gerar');
    Route::get('/relatorios/detalhes-apontamentos', [RelatorioController::class, 'detalhesApontamentos'])->name('relatorios.detalhesApontamentos');
    Route::get('/relatorios/{tipo}', [RelatorioController::class, 'show'])->name('relatorios.show');

    Route::get('/api/contratos/{contratoId}/consultores', [AgendaController::class, 'getConsultoresPorContrato'])->name('api.contratos.consultores');
    Route::resource('agendas', AgendaController::class);
    Route::resource('sugestoes', SugestaoController::class)->only(['index', 'create', 'store', 'update'])->parameters(['sugestoes' => 'sugestao']);

    Route::resource('empresas', EmpresaParceiraController::class)->except(['destroy']);
    Route::resource('contratos', ContratoController::class)->except(['destroy']);
    Route::resource('colaboradores', ColaboradorController::class)->except(['destroy'])->parameters(['colaboradores' => 'colaborador']);

    Route::middleware('role:admin,coordenador_operacoes,coordenador_tecnico,techlead')->group(function () {
        Route::get('/enviar-agendas', [EmailController::class, 'create'])->name('email.agendas.create');
        Route::post('/enviar-agendas', [EmailController::class, 'send'])->name('email.agendas.send');

        Route::get('/aprovacoes', [AprovacaoController::class, 'index'])->name('aprovacoes.index');
        Route::post('/aprovacoes/{apontamento}/aprovar', [AprovacaoController::class, 'aprovar'])->name('aprovacoes.aprovar');
        Route::post('/aprovacoes/{apontamento}/rejeitar', [AprovacaoController::class, 'rejeitar'])->name('aprovacoes.rejeitar');
    });

    Route::middleware('role:admin,coordenador_operacoes,coordenador_tecnico')->group(function () {
        Route::patch('contratos/{contrato}/toggle-status', [ContratoController::class, 'toggleStatus'])->name('contratos.toggleStatus');
    });

    Route::middleware('role:admin')->group(function () {
        Route::patch('colaboradores/{colaborador}/toggle-status', [ColaboradorController::class, 'toggleStatus'])->name('colaboradores.toggleStatus');
        Route::patch('empresas/{empresa}/toggle-status', [EmpresaParceiraController::class, 'toggleStatus'])->name('empresas.toggleStatus');
    });
});

require __DIR__.'/auth.php';
