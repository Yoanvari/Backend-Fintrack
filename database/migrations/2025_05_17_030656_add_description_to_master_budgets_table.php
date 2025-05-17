<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToMasterBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('master_budgets', function (Blueprint $table) {
            $table->text('description')->nullable()->after('some_column'); 
            // Ganti 'some_column' dengan nama kolom yang ingin didahului description, atau hapus ->after() kalau tidak perlu
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('master_budgets', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
