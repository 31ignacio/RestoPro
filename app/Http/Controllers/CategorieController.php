<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::withCount('produits')
            ->orderBy('ordre')
            ->paginate(12);

        $stats = [
            'total'    => Categorie::count(),
            'actives'  => Categorie::where('actif', true)->count(),
            'inactives'=> Categorie::where('actif', false)->count(),
            'produits' => Categorie::withCount('produits')->get()->sum('produits_count'),
        ];

        return view('categories.index', compact('categories', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'     => 'required|string|max:100|unique:categories,nom',
            'icone'   => 'nullable|string|max:50',
            'couleur' => 'nullable|string|max:7',
            'ordre'   => 'nullable|integer',
            'actif'   => 'nullable|boolean',
        ]);

        $data['actif'] = $data['actif'] ?? true;
        $cat = Categorie::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Catégorie créée avec succès.',
            'data'    => $cat->loadCount('produits'),
        ]);
    }

    public function show(Categorie $categorie)
    {
        return response()->json(
            $categorie->loadCount('produits')->load('produits:id,nom,categorie_id')
        );
    }

    public function update(Request $request, Categorie $categorie)
    {
        $data = $request->validate([
            'nom'     => 'required|string|max:100|unique:categories,nom,'.$categorie->id,
            'icone'   => 'nullable|string|max:50',
            'couleur' => 'nullable|string|max:7',
            'actif'   => 'nullable|boolean',
            'ordre'   => 'nullable|integer',
        ]);

        $categorie->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Catégorie mise à jour avec succès.',
            'data'    => $categorie->loadCount('produits'),
        ]);
    }

    public function destroy(Categorie $categorie)
    {
        $count = $categorie->produits()->count();

        if ($count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Impossible : cette catégorie contient {$count} produit(s).",
            ], 422);
        }

        $categorie->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catégorie supprimée avec succès.',
        ]);
    }

    public function toggleActif(Categorie $categorie)
    {
        $categorie->update(['actif' => !$categorie->actif]);

        return response()->json([
            'success' => true,
            'message' => $categorie->actif ? 'Catégorie activée.' : 'Catégorie désactivée.',
            'actif'   => $categorie->actif,
        ]);
    }

    public function reordonner(Request $request)
    {
        foreach ($request->ordre as $item) {
            Categorie::where('id', $item['id'])->update(['ordre' => $item['ordre']]);
        }

        return response()->json(['success' => true]);
    }
}