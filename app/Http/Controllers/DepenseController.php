<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use Illuminate\Http\Request;

class DepenseController extends Controller
{
    /** Couleurs et labels par catégorie — utilisés dans la vue via static */
    const CAT_CONFIG = [
        'fournisseur' => ['icon' => '🛒', 'label' => 'Fournisseur', 'color' => '#6d28d9', 'bg' => '#ede9fe'],
        'salaire'     => ['icon' => '💼', 'label' => 'Salaire',     'color' => '#a16207', 'bg' => '#fef9c3'],
        'loyer'       => ['icon' => '🏠', 'label' => 'Loyer',       'color' => '#b91c1c', 'bg' => '#fee2e2'],
        'energie'     => ['icon' => '⚡', 'label' => 'Énergie',     'color' => '#c2410c', 'bg' => '#ffedd5'],
        'equipement'  => ['icon' => '🔧', 'label' => 'Équipement',  'color' => '#7c3aed', 'bg' => '#f3e8ff'],
        'entretien'   => ['icon' => '🧹', 'label' => 'Entretien',   'color' => '#0e7490', 'bg' => '#cffafe'],
        'transport'   => ['icon' => '🚗', 'label' => 'Transport',   'color' => '#15803d', 'bg' => '#dcfce7'],
        'autre'       => ['icon' => '📦', 'label' => 'Autre',       'color' => '#374151', 'bg' => '#f3f4f6'],
    ];

    /** Retourne la config d'une catégorie (fallback : autre) */
    public static function catMeta(string $cat): array
    {
        return self::CAT_CONFIG[$cat] ?? self::CAT_CONFIG['autre'];
    }

    public function index()
    {
        $depenses = Depense::with('user')
            ->latest('date_depense')
            ->paginate(20);

        $stats = [
            'total_jour'    => Depense::whereDate('date_depense', today())->sum('montant'),
            'total_mois'    => Depense::whereMonth('date_depense', now()->month)
                                ->whereYear('date_depense', now()->year)->sum('montant'),
            'par_categorie' => Depense::whereMonth('date_depense', now()->month)
                                ->whereYear('date_depense', now()->year)
                                ->selectRaw('categorie, SUM(montant) as total')
                                ->groupBy('categorie')
                                ->orderByDesc('total')
                                ->get(),
        ];

        return view('depenses.index', compact('depenses', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'libelle'      => 'required|string|max:200',
            'categorie'    => 'required|string|in:' . implode(',', array_keys(self::CAT_CONFIG)),
            'montant'      => 'required|numeric|min:0',
            'date_depense' => 'required|date',
            'notes'        => 'nullable|string|max:2000',
        ]);

        $data['user_id'] = auth()->id();
        $depense = Depense::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Dépense enregistrée.',
            'data'    => $depense->load('user'),
        ]);
    }

    public function show(Depense $depense)
    {
        return response()->json($depense->load('user'));
    }

    public function update(Request $request, Depense $depense)
    {
        $data = $request->validate([
            'libelle'      => 'required|string|max:200',
            'categorie'    => 'required|string|in:' . implode(',', array_keys(self::CAT_CONFIG)),
            'montant'      => 'required|numeric|min:0',
            'date_depense' => 'required|date',
            'notes'        => 'nullable|string|max:2000',
        ]);

        $depense->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Dépense mise à jour.',
            'data'    => $depense->fresh()->load('user'),
        ]);
    }

    public function destroy(Depense $depense)
    {
        $depense->delete();
        return response()->json(['success' => true, 'message' => 'Dépense supprimée.']);
    }
}