<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToMasterBudgetsTable extends Migration
{
    
    public function up()
    {
        Schema::table('master_budgets', function (Blueprint $table) {
            $table->text('description')->nullable()->after('some_column'); 
            
        });
    }


    public function down()
    {
        Schema::table('master_budgets', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
