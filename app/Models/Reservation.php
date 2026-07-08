<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    //
    protected $fillable = [
        'date_reservation',
        'statut',
        'user_id',
        'voiture_id'
    ];


    public function voiture()
    {
        return $this->belongsTo(Voiture::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
