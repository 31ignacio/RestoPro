<?php
namespace App\Http\Controllers;

use App\Models\{Commande, CommandeItem, TableRestaurant, Client, Produit, Notification};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    public function index(Request $request)
    {
        $query = Commande::with(['table', 'serveur', 'client', 'items'])
            ->orderByRaw("FIELD(statut,'en_attente','en_cuisson','prete','servie','payee','annulee')")
            ->latest();

        // ✅ Par défaut : uniquement aujourd'hui
        $debut = $request->get('debut', today()->format('Y-m-d'));
        $fin   = $request->get('fin',   today()->format('Y-m-d'));

        $query->whereDate('created_at', '>=', $debut)
            ->whereDate('created_at', '<=', $fin);

        // Filtre statut
        if ($request->filled('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }

        // Filtre recherche
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('numero', 'like', "%{$s}%")
                ->orWhereHas('table', fn($t) => $t->where('numero', 'like', "%{$s}%"))
                ->orWhereHas('serveur', fn($u) => $u->where('name', 'like', "%{$s}%"));
            });
        }

        $commandes = $query->paginate(15)->withQueryString();

        return view('commandes.index', compact('commandes'));
    }

    public function create()
    {
        $tables   = TableRestaurant::where('actif', true)->orderBy('numero')->get();
        $produits = Produit::with('categorie')
            ->where('disponible', true)
            ->orderBy('ordre')
            ->get()
            ->groupBy('categorie.nom');
        $clients  = Client::orderBy('nom')->get(['id', 'nom', 'telephone']);

        return view('commandes.create', compact('tables', 'produits', 'clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'     => 'required|in:sur_place,emporter,livraison',
            'table_id' => 'nullable|exists:tables_restaurant,id',
            'livreur_id' => 'nullable|exists:users,id',
            'frais_livraison' => 'nullable|numeric|min:0',
            'items'    => 'required|array|min:1',
            'items.*.produit_id' => 'required|exists:produits,id',
            'items.*.quantite'   => 'required|numeric|min:0.5',
            'items.*.notes'      => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $commande = Commande::create([
                'numero'        => Commande::genererNumero(),
                'table_id'      => $request->table_id,
                'client_id'     => $request->client_id,
                'user_id'       => auth()->id(),
                'cuisinier_id'  => $request->cuisinier_id ?: null,
                'livreur_id'    => $request->livreur_id ?: null,
                'frais_livraison' => $request->frais_livraison ?: 0,
                'type'          => $request->type,
                'statut'        => 'en_attente',
                'vague_actuelle'  => 1,
                'notes'         => $request->notes,
            ]);

            $sousTotal = 0;
            foreach ($request->items as $item) {
                $produit = Produit::findOrFail($item['produit_id']);
                $ligne   = $item['quantite'] * $produit->prix;
                $sousTotal += $ligne;

                CommandeItem::create([
                    'commande_id'   => $commande->id,
                    'produit_id'    => $produit->id,
                    'quantite'      => $item['quantite'],
                    'prix_unitaire' => $produit->prix,
                    'sous_total'    => $ligne,
                    'notes'         => $item['notes'] ?? null,
                    'statut'        => 'en_attente',
                    'vague'         => 1,
                ]);
            }

            $commande->update([
                'sous_total' => $sousTotal,
                'total'      => $sousTotal + ($request->frais_livraison ?: 0) - ($request->remise ?? 0),
                'remise'     => $request->remise ?? 0,
            ]);

            // Occuper la table
            if ($request->table_id) {
                TableRestaurant::find($request->table_id)
                    ->update(['statut' => 'occupee']);
            }

            DB::commit();

            // Notifier la cuisine
            \App\Models\Notification::notifier(
                'Nouvelle commande',
                'Commande '.$commande->numero.' — '.$commande->items->count().' article(s)',
                ['cuisinier', 'admin'],
                'cuisine',
                '/cuisine'
            );

            return response()->json([
                'success' => true,
                'message' => 'Commande '.$commande->numero.' créée.',
                'commande_id' => $commande->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Commande $commande)
    {
        $commande->load(['table', 'serveur', 'client', 'items.produit', 'paiement']);

        if (request()->expectsJson() || request()->wantsJson() || request()->ajax()) {
            return response()->json($commande);
        }

        // Charger les produits pour la vue show (ajout d'articles)
        $produits = \App\Models\Produit::with('categorie')
            ->where('disponible', true)
            ->orderBy('ordre')
            ->get();

        return view('commandes.show', compact('commande', 'produits'));
    }

    // public function update(Request $request, Commande $commande)
    // {
    //     // Ajout d'articles à une commande existante
    //     if ($request->has('items')) {
    //         DB::beginTransaction();
    //         try {
    //             foreach ($request->items as $item) {
    //                 $produit = Produit::findOrFail($item['produit_id']);
    //                 $existing = $commande->items()
    //                     ->where('produit_id', $produit->id)->first();

    //                 if ($existing) {
    //                     $existing->update([
    //                         'quantite'  => $existing->quantite + $item['quantite'],
    //                         'sous_total'=> ($existing->quantite + $item['quantite']) * $existing->prix_unitaire,
    //                     ]);
    //                 } else {
    //                     CommandeItem::create([
    //                         'commande_id'   => $commande->id,
    //                         'produit_id'    => $produit->id,
    //                         'quantite'      => $item['quantite'],
    //                         'prix_unitaire' => $produit->prix,
    //                         'sous_total'    => $item['quantite'] * $produit->prix,
    //                         'notes'         => $item['notes'] ?? null,
    //                     ]);
    //                 }
    //             }
    //             $commande->calculerTotal();
    //             DB::commit();
    //             $this->notifierModificationCuisine($commande);
    //             return response()->json(['success' => true, 'message' => 'Articles ajoutés.']);
    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //         }
    //     }

    //     // Changement de statut
    //     if ($request->has('statut')) {
    //         $user = auth()->user();
    //         if ($user && $user->hasRole('cuisinier') && ! $commande->canBeManagedBy($user) && ! $user->hasRole('admin')) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Cette commande ne vous est pas attribuée.',
    //             ], 403);
    //         }

    //         $commande->update(['statut' => $request->statut]);

    //         // Libérer la table si payée ou annulée
    //         if (in_array($request->statut, ['payee', 'annulee']) && $commande->table_id) {
    //             $autresCommandes = Commande::where('table_id', $commande->table_id)
    //                 ->whereNotIn('statut', ['payee', 'annulee'])
    //                 ->where('id', '!=', $commande->id)
    //                 ->count();

    //             if ($autresCommandes === 0) {
    //                 TableRestaurant::find($commande->table_id)
    //                     ->update(['statut' => 'libre']);
    //             }
    //         }

    //         return response()->json(['success' => true, 'message' => 'Statut mis à jour.']);
    //     }

    //     return response()->json(['success' => false, 'message' => 'Aucune action.'], 400);
    // }

    public function destroy(Request $request, Commande $commande)
    {
        // ✅ Annulation possible UNIQUEMENT si la commande n'a encore jamais
        // été envoyée en cuisine — même si elle est repassée à "en_attente"
        // suite à l'ajout d'un complément après avoir été "prête".
        if ($commande->statut !== 'en_attente' || $commande->deja_envoyee_cuisine) {
            return response()->json([
                'success' => false,
                'message' => $commande->deja_envoyee_cuisine
                    ? 'Cette commande a déjà été transmise en cuisine et ne peut plus être annulée.'
                    : 'Cette commande ne peut plus être annulée (statut : '.$commande->statut.').',
            ], 422);
        }

        $validated = $request->validate([
            'motif_annulation' => 'nullable|string|max:1000',
        ]);

        $commande->update([
            'statut'           => 'annulee',
            'motif_annulation' => $validated['motif_annulation'] ?: null,
        ]);

        if ($commande->table_id) {
            TableRestaurant::find($commande->table_id)->update(['statut' => 'libre']);
        }

        return response()->json(['success' => true, 'message' => 'Commande annulée.']);
    }
    // Supprimer un article
   public function removeItem(Commande $commande, CommandeItem $item)
    {
        // Un article n'est supprimable QUE tant qu'il n'a jamais quitté
        // 'en_attente' — dès qu'il a été envoyé en cuisine (statut synchronisé
        // vers en_cuisson au moment de l'envoi), il est figé définitivement,
        // que ce soit un article de la commande initiale ou d'un complément.
        if ($item->statut !== 'en_attente') {
            return response()->json([
                'success' => false,
                'bloquee' => true,
                'message' => 'Cet article a déjà été transmis en cuisine et ne peut plus être retiré.',
            ], 422);
        }

        // Sécurité supplémentaire : la commande elle-même doit être en_attente
        // (sinon incohérence — normalement déjà garanti par le check ci-dessus)
        if ($commande->statut !== 'en_attente') {
            return response()->json([
                'success' => false,
                'bloquee' => true,
                'message' => 'Cette commande ne peut plus être modifiée à ce stade.',
            ], 422);
        }

        $item->delete();
        $commande->calculerTotal();
        $this->notifierModificationCuisine($commande);
        return response()->json(['success' => true, 'message' => 'Article retiré.', 'total' => $commande->total]);
    }

    private function notifierModificationCuisine(Commande $commande): void
    {
        if (! in_array($commande->statut, ['en_attente', 'en_cuisson'], true)) {
            return;
        }

        if (! $commande->cuisinier_id) {
            return;
        }

        Notification::create([
            'type' => 'cuisine',
            'titre' => 'Modification de commande',
            'message' => 'La commande '.$commande->numero.' a été modifiée. Vérifiez les changements rapidement.',
            'icone' => 'fire',
            'couleur' => 'warning',
            'lien' => '/cuisine',
            'roles' => ['cuisinier'],
            'user_id' => $commande->cuisinier_id,
            'lue' => false,
        ]);
    }

    // Transitions valides : clé = statut actuel, valeur = statuts autorisés à suivre
    private const TRANSITIONS_AUTORISEES = [
        'en_attente' => ['en_cuisson', 'annulee'],
        'en_cuisson' => ['prete'],              // ❌ plus d'annulation possible ici
        'prete'      => ['servie'],             // ❌ plus d'annulation possible ici (le retour à en_attente se fait via l'ajout d'articles, pas via ce endpoint)
        'servie'     => ['payee'],
        'payee'      => [],
        'annulee'    => [],
    ];

    public function update(Request $request, Commande $commande)
    {
        // ── AJOUT D'ARTICLES ──────────────────────────────────
        if ($request->has('items')) {

            $statutActuel = $commande->statut;

            // Cas bloqué : commande en cuisson, servie, payée ou annulée
            if (!in_array($statutActuel, ['en_attente', 'prete'], true)) {
                return response()->json([
                    'success' => false,
                    'bloquee' => true,
                    'message' => 'Cette commande ne peut plus être modifiée à ce stade.',
                ], 422);
            }

            $request->validate([
                'items'               => 'required|array|min:1',
                'items.*.produit_id'  => 'required|exists:produits,id',
                'items.*.quantite'    => 'required|numeric|min:0.5',
                'items.*.notes'       => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();
            try {
                if ($statutActuel === 'en_attente') {
                    // ── Cas 1 : la commande n'a pas encore été envoyée en cuisine.
                    // Modification "normale" : on fusionne avec les lignes existantes
                    // de la vague en cours.
                    foreach ($request->items as $item) {
                        $produit = Produit::findOrFail($item['produit_id']);
                        $existing = $commande->items()
                            ->where('produit_id', $produit->id)
                            ->where('vague', $commande->vague_actuelle)
                            ->first();

                        if ($existing) {
                            $nouvelleQte = $existing->quantite + $item['quantite'];
                            $existing->update([
                                'quantite'   => $nouvelleQte,
                                'sous_total' => $nouvelleQte * $existing->prix_unitaire,
                            ]);
                        } else {
                            CommandeItem::create([
                                'commande_id'   => $commande->id,
                                'produit_id'    => $produit->id,
                                'quantite'      => $item['quantite'],
                                'prix_unitaire' => $produit->prix,
                                'sous_total'    => $item['quantite'] * $produit->prix,
                                'notes'         => $item['notes'] ?? null,
                                'statut'        => 'en_attente',
                                'vague'         => $commande->vague_actuelle,
                            ]);
                        }
                    }

                    $message = 'Articles ajoutés.';

                } else {
                    // ── Cas 2 : commande "prête" → NOUVELLE VAGUE.
                    // Les articles déjà présents (vague précédente, statut 'prete')
                    // restent figés : on ne les touche jamais ici.
                    $nouvelleVague = $commande->vague_actuelle + 1;

                    foreach ($request->items as $item) {
                        $produit = Produit::findOrFail($item['produit_id']);
                        CommandeItem::create([
                            'commande_id'   => $commande->id,
                            'produit_id'    => $produit->id,
                            'quantite'      => $item['quantite'],
                            'prix_unitaire' => $produit->prix,
                            'sous_total'    => $item['quantite'] * $produit->prix,
                            'notes'         => $item['notes'] ?? null,
                            'statut'        => 'en_attente',
                            'vague'         => $nouvelleVague,
                        ]);
                    }

                    // Nouveau cycle cuisine, uniquement pour cette vague.
                    // Les anciens articles gardent leur statut 'prete'.
                    $commande->update([
                        'statut'             => 'en_attente',
                        'vague_actuelle'     => $nouvelleVague,
                        'prise_en_charge_at' => null,
                        'prete_at'           => null,
                    ]);

                    $message = 'Complément envoyé en cuisine (vague '.$nouvelleVague.').';
                }

                $commande->calculerTotal();
                DB::commit();
                $this->notifierModificationCuisine($commande);

                return response()->json(['success' => true, 'message' => $message]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        // ── CHANGEMENT DE STATUT ──────────────────────────────
        if ($request->has('statut')) {
            $request->validate([
                'statut' => 'required|in:en_attente,en_cuisson,prete,servie,payee,annulee',
            ]);

            $user = auth()->user();
            $nouveauStatut = $request->statut;
            $statutActuel  = $commande->statut;

            $estCuisineOuAdmin = $user && ($user->hasRole('cuisinier') || $user->hasRole('admin'));

            if (!$estCuisineOuAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seule la cuisine peut faire évoluer cette commande.',
                ], 403);
            }

            if ($user->hasRole('cuisinier') && !$user->hasRole('admin') && !$commande->canBeManagedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette commande ne vous est pas attribuée.',
                ], 403);
            }

            $transitionsPossibles = self::TRANSITIONS_AUTORISEES[$statutActuel] ?? [];
            if (!in_array($nouveauStatut, $transitionsPossibles, true)) {
                return response()->json([
                    'success' => false,
                    'message' => "Transition invalide : impossible de passer de « {$statutActuel} » à « {$nouveauStatut} ».",
                ], 422);
            }

           $commande->update(['statut' => $nouveauStatut]);

// ✅ Dès la première fois que la commande part en cuisine, elle est
// définitivement marquée comme "transmise" — même si elle revient
// ensuite à en_attente via un complément après avoir été "prête".
if ($nouveauStatut === 'en_cuisson' && !$commande->deja_envoyee_cuisine) {
    $commande->update(['deja_envoyee_cuisine' => true]);
}

// Synchronisation du statut des articles de la vague en cours uniquement
if (in_array($nouveauStatut, ['en_cuisson', 'prete'], true)) {
    $commande->items()
        ->where('vague', $commande->vague_actuelle)
        ->update(['statut' => $nouveauStatut]);
}
            return response()->json(['success' => true, 'message' => 'Statut mis à jour.']);
        }

        return response()->json(['success' => false, 'message' => 'Aucune action.'], 400);
    }

}