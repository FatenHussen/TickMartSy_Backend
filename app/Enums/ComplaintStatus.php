<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case NEW = 'new';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';
}
