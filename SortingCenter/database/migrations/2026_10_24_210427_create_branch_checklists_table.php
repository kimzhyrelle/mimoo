<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('branch_key');
            $table->string('title');
            $table->string('station')->nullable();
            $table->string('shift_key');
            $table->string('shift_label');
            $table->date('shift_date');
            $table->timestamp('submitted_at')->nullable();
            $table->string('submitted_by')->nullable();
            $table->timestamps();

            $table->unique(['branch_key', 'shift_key', 'shift_date']);
            $table->index(['shift_date', 'shift_key']);
        });

        Schema::create('branch_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_checklist_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('title');
            $table->text('body')->nullable();
            $table->boolean('checked')->default(false);
            $table->string('note', 200)->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->unique(['branch_checklist_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_checklist_items');
        Schema::dropIfExists('branch_checklists');
    }
};
