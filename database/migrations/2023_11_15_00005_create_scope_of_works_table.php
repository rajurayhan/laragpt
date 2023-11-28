<?php

// database/migrations/xxxx_xx_xx_create_scope_of_works_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScopeOfWorksTable extends Migration
{
    public function up()
    {
        Schema::create('scope_of_works', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('problemGoalID')->constrained('problems_and_goals')->nullable();
            $table->longText('scopeText');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scope_of_works');
    }
}
