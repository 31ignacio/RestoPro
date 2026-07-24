<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use Notifiable;

    protected $fillable = ['role_id','name','email','password','actif'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['actif' => 'boolean', 'email_verified_at' => 'datetime'];

    public function role()      { return $this->belongsTo(Role::class); }
    public function commandes() { return $this->hasMany(commande::class); }
    public function paiements() { return $this->hasMany(Paiement::class); }

    public function hasRole(string $role): bool {
        return $this->role?->nom === $role;
    }
    public function hasAnyRole(array $roles): bool {
        return in_array($this->role?->nom, $roles);
    }
}