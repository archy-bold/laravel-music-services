<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMusicServicesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('music-services.table_names');
        // $columnNames = config('music-services.column_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/music-services.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['users'], function (Blueprint $table) use ($tableNames) {
            $table->id();
            $table->string('name');
            $table->json('meta')->nullable();
            $table->string('url')->nullable();
            $table->string('vendor', 25);
            $table->string('vendor_id', 100)->nullable();
            $table->timestamps();

            $table->unique(['vendor', 'vendor_id'], $tableNames['users'] . '_vendor_index');
        });

        Schema::create($tableNames['playlists'], function (Blueprint $table) use ($tableNames) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('public')->nullable();
            $table->json('meta')->nullable();
            $table->string('url')->nullable();
            $table->string('vendor', 25);
            $table->string('vendor_id', 100)->nullable();
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained($tableNames['users'])
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['vendor', 'vendor_id'], $tableNames['playlists'] . '_vendor_index');
        });

        Schema::create($tableNames['playlist_snapshots'], function (Blueprint $table) use ($tableNames) {
            $table->id();
            $table->unsignedInteger('num_followers')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('playlist_id')
                ->constrained($tableNames['playlists'])
                ->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create($tableNames['albums'], function (Blueprint $table) use ($tableNames) {
            $table->id();
            $table->string('name');
            $table->date('release_date');
            $table->string('release_date_str', 10);
            $table->string('release_date_precision', 10);
            $table->json('meta')->nullable();
            $table->string('url')->nullable();
            $table->string('vendor', 25);
            $table->string('vendor_id', 100)->nullable();
            $table->timestamps();

            $table->unique(['vendor', 'vendor_id'], $tableNames['albums'] . '_vendor_index');
        });

        Schema::create($tableNames['tracks'], function (Blueprint $table) use ($tableNames) {
            $table->id();
            $table->string('title');
            $table->text('artists');
            $table->string('isrc')->nullable();
            $table->json('meta')->nullable();
            $table->string('url')->nullable();
            $table->string('vendor', 25);
            $table->string('vendor_id', 100)->nullable();
            $table->foreignId('album_id')
                ->constrained($tableNames['albums'])
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['vendor', 'vendor_id'], $tableNames['tracks'] . '_vendor_index');
        });

        Schema::create($tableNames['playlist_snapshot_track_pivot'], function (Blueprint $table) use ($tableNames) {
            $table->foreignId('playlist_snapshot_id')
                ->constrained($tableNames['playlist_snapshots'])
                ->onDelete('cascade');
            $table->foreignId('track_id')
                ->constrained($tableNames['tracks'])
                ->onDelete('cascade');
            $table->unsignedInteger('order')->nullable();
            $table->datetime('added_at')->nullable();
            $table->json('meta')->nullable();
        });

        Schema::create($tableNames['track_information'], function (Blueprint $table) use ($tableNames) {
            $table->id();
            $table->string('type', 20);
            $table->foreignId('track_id')
                ->nullable()
                ->constrained($tableNames['tracks'])
                ->onDelete('set null');
            $table->json('meta')->nullable();
            $table->string('vendor', 25);
            $table->timestamps();

            $table->unique(['type', 'vendor', 'track_id'], $tableNames['track_information'] . '_vendor_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('music-services.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/music-services.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['track_information']);
        Schema::drop($tableNames['playlist_snapshot_track_pivot']);
        Schema::drop($tableNames['tracks']);
        Schema::drop($tableNames['albums']);
        Schema::drop($tableNames['playlist_snapshots']);
        Schema::drop($tableNames['playlists']);
        Schema::drop($tableNames['users']);
    }
}
