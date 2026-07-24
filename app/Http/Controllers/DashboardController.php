<?php
namespace App\Http\Controllers;
use App\Models\{Commande, TableRestaurant, Paiement, Depense, Produit};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {

    public function index() {
        $today = today();

        $stats = [
            'ca_jour'          => Paiement::whereDate('created_at', $today)->sum('montant_du'),
            'commandes_jour'   => Commande::whereDate('created_at', $today)->count(),
            'tables_occupees'  => TableRestaurant::whereIn('statut', ['occupee'])->count(),
            'tables_total'     => TableRestaurant::where('actif', true)->count(),
            'ticket_moyen'     => Paiement::whereDate('created_at', $today)->avg('montant_du') ?? 0,
            'commandes_actives'=> Commande::whereNotIn('statut', ['payee','annulee'])->with(['table','items'])->latest()->take(10)->get(),
            'ventes_semaine'   => $this->ventesSemaine(),
            'top_produits'     => $this->topProduits(),
        ];

        return view('dashboard', compact('stats'));
    }

    private function ventesSemaine(): array {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'jour'   => $date->locale('fr')->isoFormat('ddd'),
                'total'  => Paiement::whereDate('created_at', $date)->sum('montant_du'),
                'active' => $i === 0,
            ];
        }
        return $data;
    }

    private function topProduits(): \Illuminate\Support\Collection {
        return DB::table('commande_items')
            ->join('produits', 'produits.id', '=', 'commande_items.produit_id')
            ->join('commandes', 'commandes.id', '=', 'commande_items.commande_id')
            ->whereDate('commandes.created_at', today())
            ->select('produits.nom', DB::raw('SUM(commande_items.quantite) as total_vendu'))
            ->groupBy('produits.id', 'produits.nom')
            ->orderByDesc('total_vendu')
            ->limit(5)
            ->get();
    }
}