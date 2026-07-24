<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{

    protected $fillable = ['user_id','libelle','categorie','montant','date_depense','notes','justificatif'];
    protected $casts    = ['montant' => 'decimal:2', 'date_depense' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
}