<?php
namespace App\Http\Controllers;

use App\Models\{Stock, MouvementStock, Produit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::with(['produit.categorie'])
            ->orderByRaw('quantite <= seuil_alerte DESC')
            ->orderBy('quantite')
            ->get();

        $alertes = $stocks->filter(fn($s) => $s->isEnAlerte())->count();

        return view('stocks.index', compact('stocks', 'alertes'));
    }

    public function show(Stock $stock)
    {
        $stock->load('produit');
        $mouvements = MouvementStock::with(['user', 'commande'])
            ->where('stock_id', $stock->id)
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'stock'      => $stock,
            'produit'    => $stock->produit,
            'mouvements' => $mouvements,
        ]);
    }

    public function entree(Request $request)
    {
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'quantite'   => 'required|numeric|min:0.001',
            'motif'      => 'nullable|string|max:200',
        ]);

        DB::beginTransaction();
        try {
            $stock = Stock::firstOrCreate(
                ['produit_id' => $request->produit_id],
                ['quantite' => 0, 'unite' => $request->unite ?? 'unité', 'seuil_alerte' => 5]
            );

            $avant = $stock->quantite;
            $stock->increment('quantite', $request->quantite);
            $stock->refresh();

            MouvementStock::create([
                'stock_id'       => $stock->id,
                'user_id'        => auth()->id(),
                'type'           => 'entree',
                'quantite'       => $request->quantite,
                'quantite_avant' => $avant,
                'quantite_apres' => $stock->quantite,
                'motif'          => $request->motif ?? 'Entrée manuelle',
            ]);

            DB::commit();
            $stock->refresh();
            if ($stock->isEnAlerte()) {
                \App\Models\Notification::notifier(
                    'Stock faible',
                    $stock->produit->nom.' : '.$stock->quantite.' '.$stock->unite.' restant(s)',
                    ['admin', 'caissier'],
                    'stock',
                    '/stocks'
                );
            }
            return response()->json([
                'success'  => true,
                'message'  => 'Entrée stock enregistrée.',
                'quantite' => $stock->quantite,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function sortie(Request $request)
    {
        $request->validate([
            'produit_id' => 'required|exists:produits,id',
            'quantite'   => 'required|numeric|min:0.001',
            'motif'      => 'nullable|string|max:200',
        ]);

        DB::beginTransaction();
        try {
            $stock = Stock::where('produit_id', $request->produit_id)->firstOrFail();

            if ($stock->quantite < $request->quantite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuffisant. Disponible : '.$stock->quantite.' '.$stock->unite,
                ], 422);
            }

            $avant = $stock->quantite;
            $stock->decrement('quantite', $request->quantite);
            $stock->refresh();

            MouvementStock::create([
                'stock_id'       => $stock->id,
                'user_id'        => auth()->id(),
                'type'           => 'sortie',
                'quantite'       => $request->quantite,
                'quantite_avant' => $avant,
                'quantite_apres' => $stock->quantite,
                'motif'          => $request->motif ?? 'Sortie manuelle',
            ]);

            DB::commit();

            $stock->refresh();
            if ($stock->isEnAlerte()) {
                \App\Models\Notification::notifier(
                    'Stock faible',
                    $stock->produit->nom.' : '.$stock->quantite.' '.$stock->unite.' restant(s)',
                    ['admin', 'caissier'],
                    'stock',
                    '/stocks'
                );
            }
            return response()->json([
                'success'  => true,
                'message'  => 'Sortie stock enregistrée.',
                'quantite' => $stock->quantite,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function ajusterSeuil(Request $request, Stock $stock)
    {
        $request->validate([
            'seuil_alerte' => 'required|numeric|min:0',
            'unite'        => 'required|string|max:20',
        ]);

        $stock->update([
            'seuil_alerte' => $request->seuil_alerte,
            'unite'        => $request->unite,
        ]);

        return response()->json(['success' => true, 'message' => 'Seuil mis à jour.']);
    }
}