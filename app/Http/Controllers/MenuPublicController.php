<?php
namespace App\Http\Controllers;

use App\Models\{TableRestaurant, Categorie, Produit, Commande, CommandeItem, Parametre, Client};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuPublicController extends Controller
{
    public function index(string $uuid)
    {
        $table = TableRestaurant::where('uuid', $uuid)
            ->where('actif', true)
            ->firstOrFail();

        // ✅ Commandes actives sur cette table
        $commandesActives = Commande::where('table_id', $table->id)
            ->whereNotIn('statut', ['payee', 'annulee'])
            ->with(['items.produit'])
            ->latest()
            ->get();

        // ✅ Si commandes en cours → rediriger vers suivi multi-commandes
        if ($commandesActives->count() > 0) {
            return redirect()->route('menu.suivi_table', $uuid);
        }

        $categories = Categorie::with(['produits' => function ($q) {
            $q->where('disponible', true)->orderBy('ordre');
        }])
        ->where('actif', true)
        ->orderBy('ordre')
        ->get()
        ->filter(fn($c) => $c->produits->isNotEmpty())
        ->values();

        $restaurant = $this->getRestaurantInfo();
        $commandeActive = null;

        return view('menu.index', compact('table', 'categories', 'restaurant', 'commandeActive'));
    }
    public function suiviTable(string $uuid)
    {
        $table = TableRestaurant::where('uuid', $uuid)
            ->where('actif', true)
            ->firstOrFail();

        $commandesActives = Commande::where('table_id', $table->id)
            ->whereNotIn('statut', ['payee', 'annulee'])
            ->with(['items.produit'])
            ->latest()
            ->get();

        if ($commandesActives->count() === 0) {
            return redirect()->route('menu.index', $uuid);
        }

        // ✅ Charger tous les produits pour la modification
        $categories = Categorie::with(['produits' => function ($q) {
            $q->where('disponible', true)->orderBy('ordre');
        }])
        ->where('actif', true)
        ->orderBy('ordre')
        ->get()
        ->filter(fn($c) => $c->produits->isNotEmpty())
        ->values();

        $restaurant = $this->getRestaurantInfo();

        return view('menu.suivi_table', compact(
            'table', 'commandesActives', 'restaurant', 'categories'
        ));
    }

    public function commander(Request $request)
    {
        $request->validate([
            'table_uuid'         => 'required|exists:tables_restaurant,uuid',
            'client_nom'         => 'required|string|max:100',
            'client_tel'         => 'nullable|string|max:20',
            'items'              => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantite'   => 'required|numeric|min:0',
            'items.*.notes'      => 'nullable|string|max:200',
            'notes'              => 'nullable|string|max:500',
        ]);

        $table = TableRestaurant::where('uuid', $request->table_uuid)->firstOrFail();

        DB::beginTransaction();
        try {
            // Client
            $client = null;
            if ($request->client_tel) {
                $client = Client::firstOrCreate(
                    ['telephone' => $request->client_tel],
                    ['nom' => $request->client_nom]
                );
                $client->update(['nom' => $request->client_nom]);
            } else {
                $client = Client::create(['nom' => $request->client_nom]);
            }

            $serveur = \App\Models\User::whereHas('role', fn($q) =>
                $q->whereIn('nom', ['admin', 'serveur'])
            )->first();

            if (!$serveur) {
                return response()->json(['success' => false, 'message' => 'Aucun serveur disponible.'], 500);
            }

            $commande = Commande::create([
                'numero'    => Commande::genererNumero(),
                'table_id'  => $table->id,
                'client_id' => $client->id,
                'user_id'   => $serveur->id,
                'type'      => 'sur_place',
                'statut'    => 'en_attente',
                'notes'     => $request->notes,
            ]);

            $sousTotal = 0;
            foreach ($request->items as $item) {
                $produit    = Produit::findOrFail($item['produit_id']);
                $ligne      = $item['quantite'] * $produit->prix;
                $sousTotal += $ligne;

                CommandeItem::create([
                    'commande_id'   => $commande->id,
                    'produit_id'    => $produit->id,
                    'quantite'      => $item['quantite'],
                    'prix_unitaire' => $produit->prix,
                    'sous_total'    => $ligne,
                    'notes'         => $item['notes'] ?? null,
                ]);
            }

            $commande->update([
                'sous_total' => $sousTotal,
                'total'      => $sousTotal,
                'remise'     => 0,
            ]);

            $table->update(['statut' => 'occupee']);

            \App\Models\Notification::notifier(
                '🛎 Commande client — Table ' . $table->numero,
                $commande->numero . ' · ' . $request->client_nom . ' · ' . count($request->items) . ' article(s)',
                ['cuisinier', 'serveur', 'admin'],
                'cuisine',
                '/cuisine'
            );

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Commande envoyée !',
                'numero'     => $commande->numero,
                'total'      => $commande->total,
                'suivi_url'  => route('menu.suivi_table', $table->uuid),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ✅ Modifier une commande (seulement si en_attente)
    // public function modifierCommande(Request $request, string $numero)
    // {
    //     $commande = Commande::where('numero', $numero)
    //         ->with('items.produit')
    //         ->firstOrFail();

    //     // ✅ Autoriser modification si en_attente OU en_cuisson
    //     if (!in_array($commande->statut, ['en_attente', 'en_cuisson'])) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Cette commande ne peut plus être modifiée (statut : '.$commande->statut.').',
    //             'bloquee' => true,
    //         ], 422);
    //     }

    //     $request->validate([
    //         'mode'               => 'nullable|in:modifier,ajouter',
    //         'items'              => 'required|array|min:1',
    //         'items.*.produit_id' => 'required|exists:produits,id',
    //         'items.*.quantite'   => 'required|numeric|min:0',
    //         'items.*.notes'      => 'nullable|string|max:200',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $mode = $request->input('mode', 'modifier');

    //         if ($mode === 'modifier') {
    //             $commande->items()->delete();
    //         }

    //         foreach ($request->items as $item) {
    //             $produit    = Produit::findOrFail($item['produit_id']);
    //             $ligne      = $item['quantite'] * $produit->prix;

    //             CommandeItem::create([
    //                 'commande_id'   => $commande->id,
    //                 'produit_id'    => $produit->id,
    //                 'quantite'      => $item['quantite'],
    //                 'prix_unitaire' => $produit->prix,
    //                 'sous_total'    => $ligne,
    //                 'notes'         => $item['notes'] ?? null,
    //                 'statut'        => 'en_attente',
    //             ]);
    //         }

    //         if ($mode === 'ajouter' && $commande->statut === 'en_cuisson') {
    //             $commande->update([
    //                 'statut'             => 'en_attente',
    //                 'prise_en_charge_at' => null,
    //                 'prete_at'           => null,
    //             ]);
    //         }

    //         $commande->load('items');
    //         $commande->calculerTotal();

    //         $commande->update([
    //             'notes'      => $request->notes ?? $commande->notes,
    //         ]);

    //         \App\Models\Notification::notifier(
    //             $mode === 'ajouter' ? 'Ajout sur commande client' : 'Modification de commande client',
    //             $commande->numero.' · '.$commande->items->count().' article(s) au total',
    //             ['cuisinier', 'serveur', 'admin'],
    //             'cuisine',
    //             '/cuisine'
    //         );

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => $mode === 'ajouter'
    //                 ? 'Articles ajoutés et renvoyés en cuisine.'
    //                 : 'Commande modifiée avec succès.',
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function modifierCommande(Request $request, string $numero)
    {
        $commande = Commande::where('numero', $numero)->firstOrFail();

        $data = $request->validate([
            'mode'                => 'required|in:modifier,ajouter',
            'items'               => 'required|array|min:1',
            'items.*.produit_id'  => 'required|exists:produits,id',
            'items.*.quantite'    => 'required|numeric|min:0.5',
            'items.*.notes'       => 'nullable|string|max:255',
        ]);

        if ($data['mode'] === 'modifier') {
            // ── ÉDITION : uniquement tant que la commande est modifiable ──
            if (!in_array($commande->statut, ['en_attente', 'en_cuisson'], true)) {
                return response()->json([
                    'success' => false,
                    'bloquee' => true,
                    'message' => 'Cette commande ne peut plus être modifiée.',
                ], 409);
            }

            $statutCourant = $commande->statut;
            $payload = collect($data['items'])->keyBy('produit_id');

            // On ne touche QUE les lignes dans le statut courant de la commande
            // (par sécurité, au cas où des lignes à d'autres statuts existeraient)
            $lignesEditables = $commande->items()->where('statut', $statutCourant)->get();

            // Suppression des lignes retirées par l'utilisateur
            foreach ($lignesEditables as $ligne) {
                if (!$payload->has($ligne->produit_id)) {
                    $ligne->delete();
                }
            }

            // Mise à jour / création
            foreach ($payload as $produitId => $item) {
                $ligne = $commande->items()
                    ->where('produit_id', $produitId)
                    ->where('statut', $statutCourant)
                    ->first();

                $produit = Produit::findOrFail($produitId);

                if ($ligne) {
                    $ligne->update([
                        'quantite'      => $item['quantite'],
                        'prix_unitaire' => $produit->prix,
                        'sous_total'    => $produit->prix * $item['quantite'],
                        'notes'         => $item['notes'] ?? null,
                    ]);
                } else {
                    $commande->items()->create([
                        'produit_id'    => $produit->id,
                        'quantite'      => $item['quantite'],
                        'prix_unitaire' => $produit->prix,
                        'sous_total'    => $produit->prix * $item['quantite'],
                        'notes'         => $item['notes'] ?? null,
                        'statut'        => $statutCourant,
                    ]);
                }
            }

        } else {
            // ── AJOUT DE COMPLÉMENT : uniquement une fois la commande "prête" ──
            if ($commande->statut !== 'prete') {
                return response()->json([
                    'success' => false,
                    'bloquee' => true,
                    'message' => 'L\'ajout de plats est disponible une fois la commande prête.',
                ], 409);
            }

            // On NE touche PAS aux articles déjà là : ils gardent leur statut "prete"
            foreach ($data['items'] as $item) {
                $produit = Produit::findOrFail($item['produit_id']);
                $commande->items()->create([
                    'produit_id'    => $produit->id,
                    'quantite'      => $item['quantite'],
                    'prix_unitaire' => $produit->prix,
                    'sous_total'    => $produit->prix * $item['quantite'],
                    'notes'         => $item['notes'] ?? null,
                    'statut'        => 'en_attente',
                ]);
            }

            // Renvoi automatique en cuisine
            $commande->update([
                'statut'             => 'en_attente',
                'prise_en_charge_at' => null,
                'prete_at'           => null,
            ]);
        }

        $commande->refresh();
        $commande->update([
            'total' => $commande->items->sum('sous_total'),
        ]);

        return response()->json([
            'success' => true,
            'message' => $data['mode'] === 'ajouter'
                ? 'Complément envoyé en cuisine.'
                : 'Commande modifiée.',
        ]);
    }
    // ✅ Polling statut des commandes d'une table
    public function statutCommandes(string $uuid)
    {
        $table = TableRestaurant::where('uuid', $uuid)->firstOrFail();

        $commandes = Commande::where('table_id', $table->id)
            ->whereNotIn('statut', ['payee', 'annulee'])
            ->with(['items.produit'])
            ->latest()
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'numero'     => $c->numero,
                'statut'     => $c->statut,
                // ✅ Recalculer depuis les items pour être sûr
                'total'      => $c->items->sum('sous_total'),
                'sous_total' => $c->items->sum('sous_total'),
                'modifiable' => in_array($c->statut, ['en_attente', 'en_cuisson']),
                'minutes'    => $c->created_at->diffInMinutes(now()),
                'items'      => $c->items->map(fn($i) => [
                    'produit_id'    => $i->produit_id,
                    'nom'           => $i->produit->nom,
                    'prix_unitaire' => (float) $i->prix_unitaire,
                    'quantite'      => (float) $i->quantite,
                    'sous_total'    => (float) $i->sous_total,
                    'statut'        => $i->statut,
                ]),
            ]);

        return response()->json([
            'commandes' => $commandes,
            'count'     => $commandes->count(),
        ]);
    }

    public function suivi(string $numero)
    {
        $commande = Commande::where('numero', $numero)
            ->with(['items.produit', 'table'])
            ->firstOrFail();

        $restaurant = $this->getRestaurantInfo();

        return view('menu.suivi', compact('commande', 'restaurant'));
    }

    private function getRestaurantInfo(): array
    {
        return [
            'nom'     => Parametre::get('restaurant_nom', 'RestoPro'),
            'adresse' => Parametre::get('restaurant_adresse', ''),
            'tel'     => Parametre::get('restaurant_tel', ''),
            'monnaie' => Parametre::get('monnaie_symbole', 'F'),
        ];
    }
}
