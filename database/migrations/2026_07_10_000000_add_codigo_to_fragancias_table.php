<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fragancias', function (Blueprint $table) {

            $table->string('codigo', 20)->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('fragancias', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });
    }
};
