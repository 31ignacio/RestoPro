<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model {
    
    protected $appends = ['photo_url'];
    
    protected $fillable = ['categorie_id','nom','description','prix','photo','disponible','gerer_stock','ordre'];
    
    protected $casts    = ['disponible' => 'boolean', 'gerer_stock' => 'boolean', 'prix' => 'decimal:2'];

    public function categorie()      { return $this->belongsTo(Categorie::class); }
    public function stock()          { return $this->hasOne(Stock::class); }
    public function commandeItems()  { return $this->hasMany(CommandeItem::class); }

  public function getPhotoUrlAttribute(): string
{
    if ($this->photo && file_exists(public_path('produits/' . $this->photo))) {
        return asset('produits/' . $this->photo);
    }
    return asset('images/no-photo.png');
}
}


