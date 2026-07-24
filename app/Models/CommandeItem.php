<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandeItem extends Model
{
    protected $fillable = ['commande_id','produit_id','quantite','prix_unitaire','sous_total','notes','statut'];
    protected $casts    = ['prix_unitaire' => 'decimal:2', 'sous_total' => 'decimal:2'];

    public function commande() { return $this->belongsTo(Commande::class); }
    public function produit()  { return $this->belongsTo(Produit::class); }
}