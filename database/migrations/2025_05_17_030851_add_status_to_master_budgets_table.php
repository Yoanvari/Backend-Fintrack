<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToMasterBudgetsTable extends Migration
{
    public function up()
    {
        Schema::table('master_budgets', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('description');
            // Kamu bisa atur default sesuai kebutuhan, misal 'draft', 'pending', dll
        });
    }

    public function down()
    {
        Schema::table('master_budgets', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
