<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{

    protected $fillable = ['stock_id','user_id','type','quantite','quantite_avant','quantite_apres','motif','commande_id'];
    protected $casts    = ['quantite' => 'decimal:3'];

    public function stock()    { return $this->belongsTo(Stock::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function commande() { return $this->belongsTo(Commande::class); }
}