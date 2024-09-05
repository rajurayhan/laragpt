<?php

namespace App\Enums;

enum PromptType : int
{
    case PROJECT_SUMMARY = 1;
    case PROBLEMS_AND_GOALS = 2;
    case PHASE = 9;
    case PROJECT_OVERVIEW = 3;
    case SCOPE_OF_WORK = 4;
    case DELIVERABLES = 5;
    case TASKS = 8;
    case MEETING_SUMMARY = 6;
    case OTHER = 7;
    case SALES = 10;

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

}
