<?php

namespace App\Entity;

enum OrderStatus: string
{
    case PENDING = 'PENDING';
    case PREPARING = 'PREPARING';
    case DELIVERED = 'DELIVERED';
    case CANCELED = 'CANCELED';
}
