<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $fillable = ['numero','table_id','client_id','user_id','cuisinier_id','livreur_id','frais_livraison','type','statut','sous_total','remise','total','notes','prise_en_charge_at','prete_at','motif_annulation'];
    protected $casts    = ['prise_en_charge_at' => 'datetime', 'prete_at' => 'datetime', 'frais_livraison' => 'decimal:2'];

    public function table()   { return $this->belongsTo(TableRestaurant::class, 'table_id'); }
    public function client()  { return $this->belongsTo(Client::class); }
    public function serveur() { return $this->belongsTo(User::class, 'user_id'); }
    public function cuisinier() { return $this->belongsTo(User::class, 'cuisinier_id'); }
    public function livreur() { return $this->belongsTo(User::class, 'livreur_id'); }
    public function items()   { return $this->hasMany(CommandeItem::class); }
    public function paiement(){ return $this->hasOne(Paiement::class); }

    public function canBeManagedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('cuisinier')) {
            return false;
        }

        if (empty($this->cuisinier_id)) {
            return true;
        }

        return (int) $this->cuisinier_id === (int) $user->id;
    }

    public function calculerTotal(): void {
        $this->sous_total = $this->items->sum('sous_total');
        $this->total      = $this->sous_total + ($this->frais_livraison ?? 0) - $this->remise;
        $this->save();
    }

    public static function genererNumero(): string {
        $date = now()->format('Ymd');
        $last = static::whereDate('created_at', today())->count() + 1;
        return 'CMD-'.$date.'-'.str_pad($last, 4, '0', STR_PAD_LEFT);
    }
    
}