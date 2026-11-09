<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_references', function (Blueprint $table) {
            $table->id();

            // The record that shows the picture: a brand, a menu link, a page.
            $table->morphs('mediable');
            $table->string('collection', 60);

            // The library keeps the only copy of the file. Deleting it clears
            // the pointer instead of leaving a record with a broken image.
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();

            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(
                ['mediable_type', 'mediable_id', 'collection', 'media_id'],
                'media_references_owner_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_references');
    }
};
