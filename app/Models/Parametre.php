<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    protected $fillable = ['cle','valeur'];

    public static function get(string $cle, mixed $default = null): mixed {
        return static::where('cle', $cle)->value('valeur') ?? $default;
    }

    public static function set(string $cle, mixed $valeur): void {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
    }
}