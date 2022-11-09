<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name','first_name');
            $table->string('last_name')->nullable()->after('name');
            $table->dropUnique(['email'])->nullable()->change();
            $table->string('username')->nullable()->after('remember_token');
            $table->string('profile_picture')->nullable()->after('username');
            $table->enum('gender',['male', 'female', 'other'])->nullable()->after('profile_picture');
            $table->string('profession')->nullable()->after('gender');
            $table->string('contact_number')->nullable()->after('profession');
            $table->string('whatsapp_number')->nullable()->after('contact_number');
            $table->longText('about_me')->nullable()->after('whatsapp_number');
            $table->longText('user_skills')->nullable()->after('about_me');
            $table->longText('user_experience')->nullable()->after('user_skills');
            $table->longText('business_name')->nullable()->after('user_experience');
            $table->string('facebook_link')->nullable()->after('business_name');
            $table->string('twitter_link')->nullable()->after('facebook_link');
            $table->string('linkedin_link')->nullable()->after('twitter_link');
            $table->string('instagram_link')->nullable()->after('linkedin_link');
            $table->string('youtube_link')->nullable()->after('instagram_link');
            $table->string('latitude')->nullable()->after('youtube_link');
            $table->string('longitude')->nullable()->after('latitude');
            $table->foreignId('country_id')->nullable()->after('longitude')->constrained('master_countries')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('state_id')->nullable()->after('country_id')->constrained('master_states')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('district_id')->nullable()->after('state_id')->constrained('master_districts')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('taluka_id')->nullable()->after('district_id')->constrained('master_talukas')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('status', ['active','inactive'])->default('active')->after('taluka_id');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('first_name','name');
            $table->dropColumn('last_name');
            $table->string('email')->unique()->nullable(false)->change();
            $table->dropColumn('username');
            $table->dropColumn('profile_picture');
            $table->dropColumn('gender');
            $table->dropColumn('profession');
            $table->dropColumn('contact_number');
            $table->dropColumn('whatsapp_number');
            $table->dropColumn('about_me');
            $table->dropColumn('user_skills');
            $table->dropColumn('user_experience');
            $table->dropColumn('business_name');
            $table->dropColumn('facebook_link');
            $table->dropColumn('twitter_link');
            $table->dropColumn('linkedin_link');
            $table->dropColumn('instagram_link');
            $table->dropColumn('youtube_link');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
            $table->dropForeign(['state_id']);
            $table->dropColumn('state_id');
            $table->dropForeign(['district_id']);
            $table->dropColumn('district_id');
            $table->dropForeign(['taluka_id']);
            $table->dropColumn('taluka_id');
            $table->dropColumn('status');
            $table->dropSoftDeletes();
        });
    }
}
