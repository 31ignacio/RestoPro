<?php
namespace App\Http\Controllers;

use App\Models\Commande;
use Illuminate\Http\Request;

class CuisineController extends Controller
{
    public function index()
    {
        $commandes = Commande::with(['table', 'items.produit'])
            ->whereIn('statut', ['en_attente', 'en_cuisson'])
            ->orderByRaw("FIELD(statut,'en_attente','en_cuisson')")
            ->oldest()
            ->get();

        return view('cuisine.index', compact('commandes'));
    }

    public function prendre(Request $request, Commande $commande)
    {
        $user = auth()->user();

        if (! $commande->canBeManagedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande vous n’est pas attribuée.',
            ], 403);
        }

        if ($commande->statut !== 'en_attente') {
            return response()->json([
                'success' => false,
                'message' => 'Commande déjà prise en charge.',
            ], 422);
        }

        $commande->update([
            'statut'             => 'en_cuisson',
            'prise_en_charge_at' => now(),
        ]);
        $commande->items()->where('statut', 'en_attente')->update(['statut' => 'en_cuisson']);

        return response()->json([
            'success' => true,
            'message' => 'Commande prise en charge.',
        ]);
    }

    public function prete(Request $request, Commande $commande)
    {
        $user = auth()->user();

        if (! $commande->canBeManagedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande vous n’est pas attribuée.',
            ], 403);
        }

        if ($commande->statut !== 'en_cuisson') {
            return response()->json([
                'success' => false,
                'message' => 'La commande n\'est pas en cuisson.',
            ], 422);
        }

        $commande->update([
            'statut'   => 'prete',
            'prete_at' => now(),
        ]);
        $commande->items()->whereIn('statut', ['en_attente', 'en_cuisson'])->update(['statut' => 'prete']);

        // ✅ Notification sonore pour les serveurs
        \App\Models\Notification::notifier(
            '🍽 Commande prête — ' . $commande->numero,
            'Table ' . ($commande->table?->numero ?? 'Emporter') .
            ' — ' . $commande->items->count() . ' article(s) à servir',
            ['serveur', 'admin', 'caissier'],
            'caisse',
            '/commandes'
        );

        return response()->json([
            'success' => true,
            'message' => 'Commande marquée prête.',
        ]);
    }

    // Polling AJAX — retourne toutes les commandes actives
    public function poll()
    {
        $commandes = Commande::with(['table', 'items.produit'])
            ->whereIn('statut', ['en_attente', 'en_cuisson'])
            ->oldest()
            ->get()
            ->map(function ($cmd) {
                return [
                    'id'                 => $cmd->id,
                    'numero'             => $cmd->numero,
                    'table'              => $cmd->table?->numero,
                    'type'               => $cmd->type,
                    'statut'             => $cmd->statut,
                    'notes'              => $cmd->notes,
                    'minutes'            => $cmd->created_at->diffInMinutes(now()),
                    'prise_en_charge_at' => $cmd->prise_en_charge_at?->diffInMinutes(now()),
                    'cuisinier'          => $cmd->cuisinier?->name,
                    'can_manage'         => $cmd->canBeManagedBy(auth()->user()),
                    'items'              => $cmd->items->map(fn($i) => [
                        'nom'      => $i->produit->nom,
                        'quantite' => $i->quantite,
                        'notes'    => $i->notes,
                        'statut'   => $i->statut,
                    ]),
                ];
            });

        return response()->json($commandes);
    }
}
