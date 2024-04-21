<?php

namespace App\Enums;

enum TicketStatusEnum: int
{
    case Draft = 10;
    case Published = 20;
    case Closed = 100;
}
