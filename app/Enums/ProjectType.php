<?php
namespace App\Enums;

enum ProjectType : int
{
    case LOGO_BRANDING = 1;
    case GRAPHIC_DESIGN = 2;
    case PRINTING = 3;
    case WEBSITE_DESIGN = 4;
    case WEBSITE_REDESIGN = 5;
    case RESEARCH = 6;
    case CUSTOM_DEVELOPMENT = 7;

    public static function getTitle($id)
    {
        switch ($id) {
            case self::LOGO_BRANDING:
                return 'Logo/Branding';
            case self::GRAPHIC_DESIGN:
                return 'Graphic Design';
            case self::PRINTING:
                return 'Printing';
            case self::WEBSITE_DESIGN:
                return 'Website Design';
            case self::WEBSITE_REDESIGN:
                return 'Website Redesign';
            case self::RESEARCH:
                return 'Research';
            case self::CUSTOM_DEVELOPMENT:
                return 'Custom Development';
            default:
                return null;
        }
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}

