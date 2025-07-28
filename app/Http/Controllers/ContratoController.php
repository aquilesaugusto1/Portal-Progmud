<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\EmpresaParceira;
use App\Models\User;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    public function index(Request $request)
    {
        $query = Contrato::with(['cliente', 'coordenador', 'techLead']);

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contratos = $query->latest()->paginate(10)->withQueryString();
        $clientes = EmpresaParceira::where('status', 'Ativo')->get();

        return view('contratos.index', compact('contratos', 'clientes'));
    }

    public function create()
    {
        $clientes = EmpresaParceira::where('status', 'Ativo')->get();
        $coordenadores = User::whereIn('funcao', ['coordenador_operacoes', 'coordenador_tecnico'])->where('status', 'Ativo')->get();
        $techLeads = User::where('funcao', 'techlead')->where('status', 'Ativo')->get();

        return view('contratos.create', compact('clientes', 'coordenadores', 'techLeads'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate($this->getValidationRules());
        $preparedData = $this->prepareData($request, $validatedData);

        Contrato::create($preparedData);

        return redirect()->route('contratos.index')->with('success', 'Contrato criado com sucesso.');
    }

    public function show(Contrato $contrato)
    {
        return view('contratos.show', compact('contrato'));
    }

    public function edit(Contrato $contrato)
    {
        $clientes = EmpresaParceira::where('status', 'Ativo')->get();
        $coordenadores = User::whereIn('funcao', ['coordenador_operacoes', 'coordenador_tecnico'])->where('status', 'Ativo')->get();
        $techLeads = User::where('funcao', 'techlead')->where('status', 'Ativo')->get();

        return view('contratos.edit', compact('contrato', 'clientes', 'coordenadores', 'techLeads'));
    }

    public function update(Request $request, Contrato $contrato)
    {
        $validatedData = $request->validate($this->getValidationRules($contrato->id));
        $preparedData = $this->prepareData($request, $validatedData);

        $contrato->update($preparedData);

        return redirect()->route('contratos.index')->with('success', 'Contrato atualizado com sucesso.');
    }

    public function toggleStatus(Contrato $contrato)
    {
        $novoStatus = $contrato->status === 'Ativo' ? 'Inativo' : 'Ativo';
        $contrato->update(['status' => $novoStatus]);
        $mensagem = $novoStatus === 'Ativo' ? 'Contrato ativado com sucesso.' : 'Contrato inativado com sucesso.';
        return back()->with('success', $mensagem);
    }

    private function getValidationRules($id = null)
    {
        return [
            'cliente_id' => ['required', 'exists:empresas_parceiras,id'],
            'numero_contrato' => ['nullable', 'string', 'max:255', 'unique:contratos,numero_contrato,' . $id],
            'tipo_contrato' => ['required', 'string'],
            'produtos' => ['required', 'array'],
            'produtos.*' => ['string'],
            'especifique_outro' => ['nullable', 'string', 'max:255', 'required_if:produtos.*,Outro'],
            'coordenador_id' => ['nullable', 'exists:users,id'],
            'tech_lead_id' => ['nullable', 'exists:users,id', 'required_if:tipo_contrato,ACT+'],
            'status' => ['required', 'string'],
            'data_inicio' => ['required', 'date'],
            'data_termino' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'contato_principal' => ['nullable', 'string', 'max:255'],
            'baseline_horas_mes' => ['nullable', 'numeric', 'min:0'],
            'permite_antecipar_baseline' => ['nullable', 'boolean'],
        ];
    }

    private function prepareData(Request $request, array $validatedData)
    {
        $validatedData['permite_antecipar_baseline'] = $request->has('permite_antecipar_baseline');

        if (!in_array('Outro', $validatedData['produtos'])) {
            $validatedData['especifique_outro'] = null;
        }

        if ($validatedData['tipo_contrato'] !== 'ACT+') {
            $validatedData['tech_lead_id'] = null;
        }

        return $validatedData;
    }
}