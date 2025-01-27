<?php

namespace App\Model;
enum Etat: string
{
    case MAINTENANCE = 'En maintenance';
    case FONCTIONNEL = 'Fonctionnel';
}