<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'type','titre','message','icone',
        'couleur','lien','roles','lue','user_id',
    ];

    protected $casts = [
        'roles' => 'array',
        'lue'   => 'boolean',
    ];

    // Créer une notif pour un rôle ou tous
    public static function notifier(
        string $titre,
        string $message,
        array  $roles = [],
        string $type  = 'info',
        string $lien  = null
    ): void {
        $configs = [
            'info'    => ['icone' => 'info-circle',   'couleur' => 'info'],
            'success' => ['icone' => 'check-circle',  'couleur' => 'success'],
            'warning' => ['icone' => 'exclamation-triangle', 'couleur' => 'warning'],
            'danger'  => ['icone' => 'x-circle',      'couleur' => 'danger'],
            'cuisine' => ['icone' => 'fire',           'couleur' => 'warning'],
            'caisse'  => ['icone' => 'cash-register',  'couleur' => 'success'],
            'stock'   => ['icone' => 'box-seam',       'couleur' => 'danger'],
        ];

        $cfg = $configs[$type] ?? $configs['info'];

        static::create([
            'type'    => $type,
            'titre'   => $titre,
            'message' => $message,
            'icone'   => $cfg['icone'],
            'couleur' => $cfg['couleur'],
            'lien'    => $lien,
            'roles'   => $roles,
            'lue'     => false,
        ]);
    }
}