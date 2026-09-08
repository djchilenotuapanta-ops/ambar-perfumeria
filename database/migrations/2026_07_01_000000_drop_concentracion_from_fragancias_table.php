<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fragancias', function (Blueprint $table) {
            if (Schema::hasColumn('fragancias', 'concentracion')) {
                $table->dropColumn('concentracion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fragancias', function (Blueprint $table) {
            $table->enum('concentracion', ['parfum', 'edp', 'edt', 'edc'])->default('edp')->after('casa_perfumista');
        });
    }
};
