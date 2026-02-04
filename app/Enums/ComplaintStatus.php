<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case NEW = 'new';
    case IN_REVIEW = 'in_review';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';
}
