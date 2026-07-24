<?php
namespace App\Http\Controllers;

use App\Models\TableRestaurant;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = TableRestaurant::with('commandeActive.items')->orderBy('numero')->get();
        return view('tables.index', compact('tables'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero'   => 'required|string|max:10|unique:tables_restaurant,numero',
            'nom'      => 'nullable|string|max:100',
            'capacite' => 'required|integer|min:1|max:50',
        ]);

        $table = TableRestaurant::create($data);
        return response()->json(['success' => true, 'message' => 'Table créée.', 'data' => $table]);
    }

    public function show(TableRestaurant $table)
    {
        return response()->json($table->load('commandeActive.items.produit'));
    }

    public function update(Request $request, TableRestaurant $table)
    {
        $data = $request->validate([
            'numero'   => 'required|string|max:10|unique:tables_restaurant,numero,'.$table->id,
            'nom'      => 'nullable|string|max:100',
            'capacite' => 'required|integer|min:1|max:50',
            'actif'    => 'boolean',
        ]);

        $table->update($data);
        return response()->json(['success' => true, 'message' => 'Table mise à jour.', 'data' => $table]);
    }

     public function changerStatut(Request $request, TableRestaurant $table)
{
    $data = $request->validate([
        'statut' => 'required|in:libre,occupee,reservee',
        'motif'  => 'nullable|string|max:255',
    ]);

    $table->update([
        'statut'            => $data['statut'],
        'motif_reservation' => $data['statut'] === 'reservee' ? ($data['motif'] ?? null) : null,
    ]);

    return response()->json([
        'success' => true,
        'message' => $data['statut'] === 'reservee' ? 'Table réservée.' : 'Table libérée.',
        'data'    => $table,
    ]);
}


    public function destroy(TableRestaurant $table)
    {
        if ($table->commandeActive) {
            return response()->json(['success' => false, 'message' => 'Table occupée, impossible de supprimer.'], 422);
        }
        $table->delete();
        return response()->json(['success' => true, 'message' => 'Table supprimée.']);
    }
}