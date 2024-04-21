<?php

namespace App\Enums;

enum TagCategoryEnum: int
{
    case Frontend = 1;
    case Backend = 2;
    case Language = 3;
    case Infrastructure = 4;
    case Question = 5;
    case Others = 6;
}
