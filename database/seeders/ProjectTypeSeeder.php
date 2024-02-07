<?php
namespace Database\Seeders;

use App\Enums\ProjectType;
use App\Models\ProjectType as ModelsProjectType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectTypeSeeder extends Seeder
{
    public function run()
    {
        foreach (ProjectType::getValues() as $projectType) {
            ModelsProjectType::updateOrCreate(
                ['id' => $projectType],
                ['name' => ProjectType::getTitle($projectType)]
            );
        }
    }
}
