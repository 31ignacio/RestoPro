<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TableRestaurant extends Model
{
    protected $table    = 'tables_restaurant';
    protected $fillable = ['numero','nom','capacite','statut','actif','uuid','motif_reservation'];
    protected $casts    = ['actif' => 'boolean'];

    // Générer UUID automatiquement à la création
    protected static function booted(): void
    {
        static::creating(function ($table) {
            if (empty($table->uuid)) {
                $table->uuid = Str::uuid();
            }
        });
    }

    public function commandes()      { return $this->hasMany(Commande::class, 'table_id'); }
    public function commandeActive() { return $this->hasOne(Commande::class, 'table_id')->whereNotIn('statut',['payee','annulee']); }
    public function isLibre(): bool  { return $this->statut === 'libre'; }

    public function getMenuUrlAttribute(): string
    {
        return url('/menu/table/' . $this->uuid);
    }
}