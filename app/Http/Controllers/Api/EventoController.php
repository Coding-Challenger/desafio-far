<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Eventos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPUnit\Event\Event;

class EventoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Eventos
            ::when($request->has('data_inicio'), function ($q) use ($request) {
                $q->whereDate('data_inicio', $request->data_inicio);
            })
            ->when($request->has('data_prazo'), function ($q) use ($request) {
                $q->whereDate('data_prazo', $request->data_prazo);
            })
            ->when($request->has('data_conclusao'), function ($q) use ($request) {
                $q->whereDate('data_conclusao', $request->data_conclusao);
            })
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data_inicio = $request->data_inicio;
        $data_prazo = $request->data_prazo;

        if (Carbon::parse($data_inicio)->isWeekend() || Carbon::parse($data_prazo)->isWeekend()) {
            return response()->json(['message' => 'Evento não cadastrado, data somente dísponivel em dias de semana!'], 202);
        }

        $eventos_existentes = Eventos
                ::where('user_id', auth()->user()->id)
                ->where(function ($q) use ($data_inicio, $data_prazo){
                    $q->whereBetween('data_inicio', [$data_inicio, $data_prazo])
                    ->orWhereBetween('data_prazo', [$data_inicio, $data_prazo]);
                })
                ->count() > 0;

        if ($eventos_existentes) {
            return response()->json(['message' => 'Evento não cadastrado, existem outros eventos nas datas requeridas!'], 202);
        }

        Eventos::create($request->all());

        return response()->json(['message' => 'Evento cadastrado com sucesso!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Eventos $evento)
    {
        try {
            return $evento;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Eventos $eventos)
    {
        try {
            $evento = Eventos::find($request->evento);
            $evento->update([
                'status' => 'concluído',
                'data_conclusao' => today(),
                'titulo' => $request->titulo,
                'descricao' => $request->descricao
            ]);
            return response()->json(['message' => ' O evento foi atualizado!']);
        } catch (\Exception $e) {
            return response()->json(['message' => ' O evento não pode ser atualizado!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Eventos $eventos)
    {
        try {
            $eventos->delete();
            return response()->json(['message' => 'O evento foi removido!'], 201);
        } catch (\Exception $e) {
            Log::error('Trying to delete nonexisting event...');
            return response()->json(['message' => 'O nenhum evento foi removido!']);
        }
    }
}
