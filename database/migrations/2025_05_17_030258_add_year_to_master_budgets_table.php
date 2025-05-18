<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{


    public function up()
{
    Schema::table('master_budgets', function (Blueprint $table) {
        $table->year('year')->after('total_amount');
    });
}

public function down()
{
    Schema::table('master_budgets', function (Blueprint $table) {
        $table->dropColumn('year');
    });
}

};
