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
    case MARKETING = 8;

    public static function getTitle($id)
    {
        // return $id;
        switch ($id) {
            case 1:
                return 'Logo/Branding';
            case 2:
                return 'Graphic Design';
            case 3:
                return 'Printing';
            case 4:
                return 'Website Design';
            case 5:
                return 'Website Redesign';
            case 6:
                return 'Research';
            case 7:
                return 'Custom Development';
            case 8:
                return 'Marketing';
            default:
                return 'Not Found';
        }
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}

