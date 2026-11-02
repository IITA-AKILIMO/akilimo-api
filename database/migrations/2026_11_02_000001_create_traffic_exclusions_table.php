<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Service\TrafficExclusionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Staff and test traffic is marked `excluded` on api_requests so the dashboard and reports leave it
 * out. The rule used to be fixed in a generated column and only read the old app's `userInfo`, so
 * requests in the current `user_info` format were never excluded. The rules now live in a table that
 * admins manage (Monitoring > Excluded traffic), and `excluded` becomes a plain column that
 * TrafficExclusionService sets from them.
 *
 * The name, phone and gender columns also read the current `user_info` format now.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('traffic_exclusions', function (Blueprint $table) {
            $table->id();
            // phone, name or device (see TrafficExclusionService::FIELDS)
            $table->string('field', 20);
            // exact, starts_with, contains or regex
            $table->string('match_type', 20);
            $table->string('value', 191);
            $table->string('note', 255)->nullable();
            $table->boolean('active')->default(true);
            $table->timestampsTz();
        });

        // The rules the generated column had, so nothing changes until an admin edits them
        $now = now();
        DB::table('traffic_exclusions')->insert([
            ['field' => 'phone', 'match_type' => 'starts_with', 'value' => '254', 'note' => 'Kenyan numbers (staff before Kenya was a target country)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['field' => 'phone', 'match_type' => 'starts_with', 'value' => '49', 'note' => 'German numbers (staff)', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['field' => 'name', 'match_type' => 'contains', 'value' => 'KREYE', 'note' => 'Staff name', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['field' => 'name', 'match_type' => 'contains', 'value' => 'C K', 'note' => 'Staff initials', 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // A stored generated column turns into a plain one with its values (and index) kept.
        DB::statement('ALTER TABLE `api_requests` MODIFY `excluded` tinyint(1) NOT NULL DEFAULT 0');

        DB::statement("ALTER TABLE `api_requests`
            MODIFY `full_names` varchar(255) GENERATED ALWAYS AS (coalesce(
                json_unquote(json_extract(`droid_request`, '$.userInfo.userName')),
                nullif(trim(concat_ws(' ',
                    json_unquote(json_extract(`droid_request`, '$.user_info.first_name')),
                    json_unquote(json_extract(`droid_request`, '$.user_info.last_name')))), '')
            )) STORED,
            MODIFY `phone_number` varchar(50) GENERATED ALWAYS AS (coalesce(
                json_unquote(json_extract(`droid_request`, '$.userInfo.mobileNumber')),
                nullif(json_unquote(json_extract(`droid_request`, '$.user_info.phone_number')), 'null')
            )) STORED,
            MODIFY `gender` varchar(50) GENERATED ALWAYS AS (coalesce(
                json_unquote(json_extract(`droid_request`, '$.userInfo.gender')),
                json_unquote(json_extract(`droid_request`, '$.user_info.gender'))
            )) STORED");

        DB::statement("ALTER TABLE `api_requests`
            MODIFY `gender_code` varchar(2) GENERATED ALWAYS AS (case
                when `gender` in ('Male', 'Mwanaume', 'M') then 'M'
                when `gender` in ('Female', 'Mwanamke', 'F') then 'F'
                else 'NA' end) STORED");

        // Apply the rules to every request, including the new-format ones the old column missed
        app(TrafficExclusionService::class)->refresh();
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `api_requests`
            MODIFY `gender_code` varchar(2) GENERATED ALWAYS AS (case
                when `gender` in ('Male', 'Mwanaume') then 'M'
                when `gender` in ('Female', 'Mwanamke') then 'F'
                else 'NA' end) STORED");

        DB::statement("ALTER TABLE `api_requests`
            MODIFY `full_names` varchar(255) GENERATED ALWAYS AS (json_unquote(json_extract(`droid_request`, '$.userInfo.userName'))) STORED,
            MODIFY `phone_number` varchar(50) GENERATED ALWAYS AS (json_unquote(json_extract(`droid_request`, '$.userInfo.mobileNumber'))) STORED,
            MODIFY `gender` varchar(50) GENERATED ALWAYS AS (json_unquote(json_extract(`droid_request`, '$.userInfo.gender'))) STORED");

        DB::statement("ALTER TABLE `api_requests`
            MODIFY `excluded` tinyint(1) GENERATED ALWAYS AS (case
                when `phone_number` like '254%' or `phone_number` like '49%' then 1
                when `full_names` like '%KREYE%' or `full_names` like '%C K%' then 1
                else 0 end) STORED");

        Schema::dropIfExists('traffic_exclusions');
    }
};
