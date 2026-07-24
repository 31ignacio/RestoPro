<?php
namespace App\Http\Controllers;

use App\Models\{Paiement, Commande, Depense, Produit};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->get('periode', 'mois');
        $debut   = match($periode) {
            'jour'    => today(),
            'semaine' => now()->startOfWeek(),
            'mois'    => now()->startOfMonth(),
            'annee'   => now()->startOfYear(),
            default   => now()->startOfMonth(),
        };
        $fin = now();

        // CA et paiements
        $ca_total    = Paiement::whereBetween('created_at', [$debut, $fin])->sum('montant_du');
        $nb_commandes= Commande::whereBetween('created_at', [$debut, $fin])
            ->where('statut','payee')->count();
        $ticket_moyen= $nb_commandes > 0 ? $ca_total / $nb_commandes : 0;

        // Dépenses
        $total_depenses = Depense::whereBetween('date_depense', [$debut->toDateString(), $fin->toDateString()])
            ->sum('montant');
        $benefice = $ca_total - $total_depenses;

        // Modes de paiement
        $modes = Paiement::whereBetween('created_at', [$debut, $fin])
            ->selectRaw('mode, SUM(montant_du) as total, COUNT(*) as nb')
            ->groupBy('mode')
            ->get();

        // Ventes par jour (30 derniers jours)
        $ventes_par_jour = Paiement::whereBetween('created_at', [now()->subDays(29), $fin])
            ->selectRaw('DATE(created_at) as date, SUM(montant_du) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top produits
        $top_produits = DB::table('commande_items')
            ->join('produits',  'produits.id',  '=', 'commande_items.produit_id')
            ->join('commandes', 'commandes.id', '=', 'commande_items.commande_id')
            ->whereBetween('commandes.created_at', [$debut, $fin])
            ->where('commandes.statut', 'payee')
            ->selectRaw('produits.nom, SUM(commande_items.quantite) as total_vendu, SUM(commande_items.sous_total) as ca')
            ->groupBy('produits.id','produits.nom')
            ->orderByDesc('total_vendu')
            ->limit(10)
            ->get();

        // Dépenses par catégorie
        $depenses_cat = Depense::whereBetween('date_depense', [$debut->toDateString(), $fin->toDateString()])
            ->selectRaw('categorie, SUM(montant) as total')
            ->groupBy('categorie')
            ->orderByDesc('total')
            ->get();

        // Commandes par heure (pic d'activité)
        $par_heure = Commande::whereBetween('created_at', [$debut, $fin])
            ->selectRaw('HOUR(created_at) as heure, COUNT(*) as nb')
            ->groupBy('heure')
            ->orderBy('heure')
            ->get();

        return view('rapports.index', compact(
            'periode','ca_total','nb_commandes','ticket_moyen',
            'total_depenses','benefice','modes','ventes_par_jour',
            'top_produits','depenses_cat','par_heure'
        ));
    }
}