<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUpdateLogsTable extends Migration
{
    public function up()
    {
        Schema::create('update_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->longText('deployed');
            $table->longText('next');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('update_logs');
    }
}

