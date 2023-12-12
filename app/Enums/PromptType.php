<?php

namespace App\Enums;

enum PromptType : int
{
    case PROJECT_SUMMARY = 1;
    case PROBLEMS_AND_GOALS = 2;
    case PROJECT_OVERVIEW = 3;
    case SCOPE_OF_WORK = 4;
    case DELIVERABLES = 5;
    case MEETING_SUMMARY = 6;

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

}
