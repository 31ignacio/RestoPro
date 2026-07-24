<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ParametreController extends Controller
{
    public function index()
    {
        $params = Parametre::pluck('valeur', 'cle');
        return view('admin.parametres.index', compact('params'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'restaurant_nom'     => 'required|string|max:150',
            'restaurant_adresse' => 'nullable|string|max:255',
            'restaurant_tel'     => 'nullable|string|max:30',
            'monnaie'            => 'required|string|max:10',
            'monnaie_symbole'    => 'required|string|max:5',
            'ticket_message'     => 'nullable|string|max:200',
            'tva_active'         => 'boolean',
            'tva_taux'           => 'nullable|numeric|min:0|max:100',
        ]);

        $champs = [
            'restaurant_nom','restaurant_adresse','restaurant_tel',
            'monnaie','monnaie_symbole','ticket_message',
            'tva_active','tva_taux',
        ];

        foreach ($champs as $cle) {
            Parametre::set($cle, $request->get($cle, ''));
        }

        // Logo
        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|max:1024']);
            $path = $request->file('logo')->store('config', 'public');
            Parametre::set('logo', $path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Paramètres enregistrés avec succès.',
        ]);
    }
}