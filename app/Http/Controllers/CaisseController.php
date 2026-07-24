<?php
namespace App\Http\Controllers;

use App\Models\{Commande, Paiement, Stock, MouvementStock, Parametre};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaisseController extends Controller
{
    public function index()
    {
        $commandes_pretes = Commande::with(['table', 'items.produit', 'client'])
            ->where('statut', 'prete')
            ->oldest()
            ->get();

        $stats_jour = [
            'ca'        => Paiement::whereDate('created_at', today())->sum('montant_du'),
            'nb_encais' => Paiement::whereDate('created_at', today())->count(),
            'especes'   => Paiement::whereDate('created_at', today())->where('mode','especes')->sum('montant_du'),
            'mobile'    => Paiement::whereDate('created_at', today())->where('mode','mobile_money')->sum('montant_du'),
            'carte'     => Paiement::whereDate('created_at', today())->where('mode','carte')->sum('montant_du'),
        ];

        $monnaie = Parametre::get('monnaie_symbole', 'F');

        return view('caisse.index', compact('commandes_pretes', 'stats_jour', 'monnaie'));
    }

    public function historique(Request $request)
    {
        $debut = $request->get('debut', today()->format('Y-m-d'));
        $fin   = $request->get('fin',   today()->format('Y-m-d'));

        $query = Paiement::with([
            'commande.table',
            'commande.items.produit',
            'commande.client',
            'caissier'
        ])
        ->whereDate('created_at', '>=', $debut)
        ->whereDate('created_at', '<=', $fin)
        ->latest();

        if ($request->filled('mode')) {
            $query->where('mode', $request->mode);
        }

        // ✅ Retourner JSON si demandé via AJAX
        if ($request->expectsJson() || $request->has('json')) {
            $paiements = $query->get();
            return response()->json([
                'paiements' => $paiements,
                'stats'     => [
                    'total' => $paiements->sum('montant_du'),
                    'count' => $paiements->count(),
                ],
            ]);
        }

        $paiements  = $query->paginate(20)->withQueryString();
        $monnaie    = Parametre::get('monnaie_symbole', 'F');
        $restaurant = [
            'nom'     => Parametre::get('restaurant_nom', 'RestoPro'),
            'adresse' => Parametre::get('restaurant_adresse', ''),
            'tel'     => Parametre::get('restaurant_tel', ''),
            'message' => Parametre::get('ticket_message', 'Merci de votre visite !'),
        ];

        return view('caisse.historique', compact('paiements', 'monnaie', 'restaurant', 'debut', 'fin'));
    }

    public function detailPaiement(Paiement $paiement)
    {
        $paiement->load(['commande.table', 'commande.items.produit', 'commande.client', 'caissier']);
        return response()->json($paiement);
    }

    public function commandesPretes()
    {
        $commandes = Commande::with(['table','items'])
            ->where('statut','prete')
            ->oldest()
            ->get()
            ->map(fn($c) => [
                'id'       => $c->id,
                'numero'   => $c->numero,
                'table'    => $c->table?->numero,
                'total'    => $c->total,
                'nb_items' => $c->items->count(),
                'prete_at' => $c->prete_at?->diffForHumans(),
            ]);

        return response()->json($commandes);
    }

   public function encaisser(Request $request, Commande $commande)
{
    $data = $request->validate([
        'mode'         => 'required|in:especes,mobile_money,carte,mixte',
        'montant_recu' => 'required|numeric|min:0',
        'reference'    => 'nullable|string|max:100',
    ]);

    if (in_array($commande->statut, ['payee', 'annulee'], true)) {
        return response()->json([
            'success' => false,
            'message' => $commande->statut === 'payee'
                ? 'Déjà payée.'
                : 'Cette commande a été annulée.',
        ], 422);
    }

    DB::beginTransaction();
    try {
        // Verrouille la ligne le temps de la transaction pour éviter
        // un double encaissement (double-clic, double requête concurrente)
        $commande = Commande::where('id', $commande->id)->lockForUpdate()->firstOrFail();

        if (in_array($commande->statut, ['payee', 'annulee'], true)) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Déjà payée.'], 422);
        }

        // Recharge les items à jour AVANT de calculer le total :
        // des plats ont pu être ajoutés après le passage à "prête"
        // (statut commande repassé à en_attente), donc on ne fait
        // JAMAIS confiance à un champ 'sous_total' potentiellement périmé.
        $commande->load('items.produit');

        $sousTotal = $commande->items->sum('sous_total');
        $montantRestaurant = max(0, $sousTotal - ($commande->remise ?? 0));

        if ($data['montant_recu'] < $montantRestaurant) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Montant reçu insuffisant.',
            ], 422);
        }

        $monnaieRendue = max(0, $data['montant_recu'] - $montantRestaurant);

        // Info pour le caissier : si des plats ajoutés après coup
        // n'ont pas encore été préparés/marqués prêts, il vaut mieux
        // le signaler plutôt que d'encaisser en silence.
        $itemsNonPrets = $commande->items
            ->where('statut', '!=', 'prete')
            ->pluck('produit.nom')
            ->values();

        $paiement = Paiement::create([
            'commande_id'    => $commande->id,
            'user_id'        => auth()->id(),
            'mode'           => $data['mode'],
            'montant_recu'   => $data['montant_recu'],
            'montant_du'     => $montantRestaurant,
            'monnaie_rendue' => $monnaieRendue,
            'reference'      => $data['reference'] ?? null,
        ]);

        $commande->update([
            'statut'     => 'payee',
            'total'      => $sousTotal,
        ]);

        if ($commande->table_id) {
            $autresActives = Commande::where('table_id', $commande->table_id)
                ->whereNotIn('statut', ['payee', 'annulee'])
                ->where('id', '!=', $commande->id)
                ->count();
            if ($autresActives === 0) {
                $commande->table->update(['statut' => 'libre']);
            }
        }

        // Déduction du stock — sur TOUS les items, quel que soit leur
        // statut individuel (prete / en_attente / en_cuisson), car au
        // moment de l'encaissement l'ensemble de la commande est
        // considéré comme consommé.
        foreach ($commande->items as $item) {
            $stock = $item->produit->stock;
            if ($stock && $item->produit->gerer_stock) {
                $avant = $stock->quantite;
                $stock->decrement('quantite', $item->quantite);
                $stock->refresh();
                MouvementStock::create([
                    'stock_id'       => $stock->id,
                    'user_id'        => auth()->id(),
                    'type'           => 'sortie',
                    'quantite'       => $item->quantite,
                    'quantite_avant' => $avant,
                    'quantite_apres' => $stock->quantite,
                    'motif'          => 'Vente — '.$commande->numero,
                    'commande_id'    => $commande->id,
                ]);
            }
        }

        $commande->load(['items.produit', 'table', 'client']);

        DB::commit();

        return response()->json([
            'success'             => true,
            'message'             => $itemsNonPrets->isNotEmpty()
                ? 'Paiement enregistré. Attention, certains plats ne sont pas encore marqués prêts.'
                : 'Paiement enregistré.',
            'paiement_id'         => $paiement->id,
            'commande_num'        => $commande->numero,
            'total'               => $commande->total,
            'montant_restaurant'  => $montantRestaurant,
            'monnaie_rendue'      => $monnaieRendue,
            'mode'                => $data['mode'],
            'table'               => $commande->table?->numero,
            'client'              => $commande->client?->nom,
            'items_non_prets'     => $itemsNonPrets, // liste des noms, vide si tout est prêt
            'items'               => $commande->items->map(fn($i) => [
                'nom'        => $i->produit->nom,
                'quantite'   => $i->quantite,
                'prix'       => $i->prix_unitaire,
                'sous_total' => $i->sous_total,
                'statut'     => $i->statut,
            ]),
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
}