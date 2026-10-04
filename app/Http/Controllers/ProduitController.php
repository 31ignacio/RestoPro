<?php

namespace App\Http\Controllers;

use App\Models\{Produit, Categorie};
use Illuminate\Http\Request;


class ProduitController extends Controller
{
    public function index(Request $request)
    {
        $query = Produit::with(['categorie', 'stock'])->orderBy('ordre');

        // Filtre recherche texte
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%{$s}%")
                ->orWhere('description', 'like', "%{$s}%");
            });
        }

        // Filtre catégorie
        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        // Filtre disponibilité
        if ($request->filled('disponible') && $request->disponible !== '') {
            $query->where('disponible', (bool) $request->disponible);
        }

        // Pagination — withQueryString() conserve les filtres dans les liens
        $produits = $query->paginate(12)->withQueryString();

        $categories = Categorie::where('actif', true)->orderBy('ordre')->get();

        // Stats globales (pas affectées par les filtres de la page)
        $stats = [
            'total'   => Produit::count(),
            'dispo'   => Produit::where('disponible', true)->count(),
            'indispo' => Produit::where('disponible', false)->count(),
            'cats'    => $categories->count(),
        ];

        return view('produits.index', compact('produits', 'categories', 'stats'));
    }

 

    public function show(Produit $produit)
    {
        return response()->json($produit->load('categorie', 'stock'));
    }

 // app/Http/Controllers/ProduitController.php

private function storePhoto($file, ?string $oldFilename = null): string
{
    if ($oldFilename && file_exists(public_path('produits/' . $oldFilename))) {
        @unlink(public_path('produits/' . $oldFilename));
    }

    $filename = 'prod_' . \Illuminate\Support\Str::random(40) . '.' . $file->extension();
    $file->move(public_path('produits'), $filename);

    return $filename;
}

public function store(Request $request)
{
    
    $data = $request->validate([
        'categorie_id' => 'required|exists:categories,id',
        'nom'          => 'required|string|max:150',
        'description'  => 'nullable|string',
        'prix'         => 'required|numeric|min:0',
        'disponible'   => 'boolean',
        'gerer_stock'  => 'boolean',
        'ordre'        => 'nullable|integer',
        'photo'        => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('photo')) {
        $data['photo'] = $this->storePhoto($request->file('photo'));
    }

    $produit = Produit::create($data);

    return response()->json([
        'success' => true,
        'message' => 'Produit créé avec succès.',
        'data'    => $produit->load('categorie'),
    ]);
}

public function update(Request $request, Produit $produit)
{
    $data = $request->validate([
        'categorie_id' => 'required|exists:categories,id',
        'nom'          => 'required|string|max:150',
        'description'  => 'nullable|string',
        'prix'         => 'required|numeric|min:0',
        'disponible'   => 'boolean',
        'gerer_stock'  => 'boolean',
        'ordre'        => 'nullable|integer',
        'photo'        => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('photo')) {
        $data['photo'] = $this->storePhoto($request->file('photo'), $produit->photo);
    }

    $produit->update($data);

    return response()->json([
        'success' => true,
        'message' => 'Produit mis à jour.',
        'data'    => $produit->load('categorie'),
    ]);
}

// Toggle isolé sur sa propre route — plus de heuristique fragile sur count($request->all())
public function toggleDisponible(Produit $produit)
{
    $produit->update(['disponible' => !$produit->disponible]);

    return response()->json([
        'success'    => true,
        'message'    => $produit->disponible ? 'Produit activé.' : 'Produit désactivé.',
        'disponible' => $produit->disponible,
    ]);
}

public function destroy(Produit $produit)
{
    if ($produit->photo && file_exists(public_path('produits/' . $produit->photo))) {
        @unlink(public_path('produits/' . $produit->photo));
    }
    $produit->delete();

    return response()->json(['success' => true, 'message' => 'Produit supprimé.']);
}
}
