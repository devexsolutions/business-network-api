<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->string('type')->default('networking'); // networking, conference, workshop, webinar
            $table->string('format')->default('in_person'); // in_person, virtual, hybrid
            $table->json('location')->nullable(); // address for in-person, link for virtual
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->integer('max_attendees')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->string('image')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
