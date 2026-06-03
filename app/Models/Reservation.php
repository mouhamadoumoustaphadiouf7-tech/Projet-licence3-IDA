<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    
    protected $primaryKey = 'id_reservation';

    protected $fillable = [
        'date_debut',
        'date_fin',
        'id_utilisateur',
        'id_vehicule',
        'statut_reservation'
    ];
}
