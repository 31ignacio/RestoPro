<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{

    protected $fillable = ['produit_id','quantite','unite','seuil_alerte'];
    protected $casts    = ['quantite' => 'decimal:3', 'seuil_alerte' => 'decimal:3'];

    public function produit()     { return $this->belongsTo(Produit::class); }
    public function mouvements()  { return $this->hasMany(MouvementStock::class); }
    public function isEnAlerte(): bool { return $this->quantite <= $this->seuil_alerte; }
}