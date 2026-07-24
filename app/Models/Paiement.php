<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = ['commande_id','user_id','mode','montant_recu','montant_du','monnaie_rendue','reference'];
    protected $casts    = ['montant_recu' => 'decimal:2', 'montant_du' => 'decimal:2', 'monnaie_rendue' => 'decimal:2'];

    public function commande() { return $this->belongsTo(Commande::class); }
    public function caissier() { return $this->belongsTo(User::class, 'user_id'); }
}