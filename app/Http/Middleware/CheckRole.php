<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            abort(403, 'Acesso Não Autorizado.');
        }

        $userFuncao = auth()->user()->funcao;

        $techLeadLevelRoles = [
            'administrativo',
            'coordenador_operacoes',
            'coordenador_tecnico',
            'comercial',
        ];

        if (in_array($userFuncao, $techLeadLevelRoles)) {
            $userFuncao = 'techlead';
        }

        if (!in_array($userFuncao, $roles)) {
            abort(403, 'Acesso Não Autorizado.');
        }

        return $next($request);
    }
}