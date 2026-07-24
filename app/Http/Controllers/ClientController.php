<?php
namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::withCount('commandes')
            ->withSum('commandes', 'total')
            ->latest()
            ->get();

        $stats = [
            'total'          => $clients->count(),
            'ca_total'       => $clients->sum('commandes_sum_total'),
            'nb_commandes'   => $clients->sum('commandes_count'),
            'fideles'        => $clients->where('commandes_count', '>', 3)->count(),
        ];

        return view('clients.index', compact('clients', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:150',
            'telephone' => 'nullable|string|max:20|unique:clients,telephone',
            'email'     => 'nullable|email|unique:clients,email',
            'adresse'   => 'nullable|string',
        ]);
        $client = Client::create($data);
        return response()->json(['success' => true, 'message' => 'Client créé.', 'data' => $client]);
    }

    public function show(Client $client)
    {
        return response()->json(
            $client->load(['commandes' => fn($q) => $q->latest()->take(10)])
                   ->loadCount('commandes')
                   ->loadSum('commandes', 'total')
        );
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:150',
            'telephone' => 'nullable|string|max:20|unique:clients,telephone,'.$client->id,
            'email'     => 'nullable|email|unique:clients,email,'.$client->id,
            'adresse'   => 'nullable|string',
        ]);
        $client->update($data);
        return response()->json(['success' => true, 'message' => 'Client mis à jour.', 'data' => $client]);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(['success' => true, 'message' => 'Client supprimé.']);
    }

    public function search(Request $request)
    {
        $clients = Client::where('nom', 'like', '%'.$request->q.'%')
            ->orWhere('telephone', 'like', '%'.$request->q.'%')
            ->limit(8)->get(['id', 'nom', 'telephone']);
        return response()->json($clients);
    }
}