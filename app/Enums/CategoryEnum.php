<?php

namespace App\Enums;

enum CategoryEnum: int
{
    case BugAndConsultation = 1;
    case SeminarAndStudy = 2;
    case Event = 3;
    case CorporateProject = 4;
    case Recruitment = 5;
    case Others = 6;
}
