<?php

namespace App\Model;
enum TypeNotif: string
{
    case ANOMALIE = 'Anomalie';
    case SEUIL = 'Seuil';
    case AFFECTATION = 'Affectation';
}