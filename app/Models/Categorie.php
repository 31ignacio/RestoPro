<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $fillable = ['nom','icone','couleur','actif','ordre'];
    protected $casts    = ['actif' => 'boolean'];
    public function produits() { return $this->hasMany(Produit::class); }
}