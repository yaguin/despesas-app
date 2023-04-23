<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseCollection;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Notifications\RegisteredExpense;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Expense::query()
            ->paginate($request->get('limit', 10));

        return new ExpenseCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        try {
            DB::beginTransaction();
            $expense = new Expense();
            $expense->descricao = $request->get('descricao');
            $expense->data = $request->get('data');
            $expense->valor = $request->get('valor');
            $expense->id_usuario = $request->user()->id;
            $expense->save();

            auth()->user()->notify(new RegisteredExpense());
            DB::commit();
            return response()->json([
                'data' => $expense,
                'message' => 'Despesa cadastrada com sucesso!'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof QueryException) {
                $message = 'Erro ao cadastrar despesa.';
            }
            return response()->json([
                'message' => $message ?? $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        $this->authorize('view', $expense);
        return new ExpenseResource($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        try {
            $expense->update($request->all());
            return new ExpenseResource($expense);
        } catch (\Exception $e) {
            if ($e instanceof QueryException) {
                $message = 'Erro ao atualizar sua despesa.';
            }
            return response()->json([
                'message' => $message ?? $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json([
            'message' => 'Despesa excluída com sucesso!'
        ]);
    }
}
