<?php

declare(strict_types=1);

namespace App\Enum;

enum ImagePath: string
{
    case TOUR_PATH = 'public/images/tours';
    case COMMENT_PATH = 'public/images/comments';
}
