<?php

// database/migrations/xxxx_xx_xx_create_problems_and_goals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProblemsAndGoalsTable extends Migration
{
    public function up()
    {
        Schema::create('problems_and_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transcriptId')->constrained('meeting_transcripts');
            $table->longText('problemGoalText');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('problems_and_goals');
    }
}
