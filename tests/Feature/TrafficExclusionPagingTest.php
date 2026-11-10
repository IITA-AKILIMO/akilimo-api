<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

use App\Enums\EnumUserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

function exclusionAdmin(): User
{
    return User::create([
        'name' => 'Exclusion Admin',
        'username' => 'excl_admin_' . str()->random(8),
        'email' => 'excl_admin_' . str()->random(8) . '@example.com',
        'password' => bcrypt('password'),
        'role' => EnumUserRole::Admin,
    ]);
}

it('pages the exclusion rules instead of loading all of them', function () {
    // some rules may already exist (seeded), so count from what is there
    $existing = DB::table('traffic_exclusions')->count();

    for ($i = 1; $i <= 30; $i++) {
        DB::table('traffic_exclusions')->insert([
            'field' => 'phone', 'match_type' => 'starts_with', 'value' => '2547' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            'note' => $i <= 5 ? 'zz-paging-marker' : null, 'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
    $admin = exclusionAdmin();
    $total = $existing + 30;

    $this->actingAs($admin)->get('/admin/traffic-exclusions?per_page=10')
        ->assertInertia(fn (Assert $page) => $page
            ->has('rules', 10)
            ->where('pagination.meta.total', $total)
            ->where('pagination.meta.last_page', (int) ceil($total / 10))
            ->where('pagination.meta.per_page', 10));

    // the count follows the filter, and a page past the end is empty
    $this->actingAs($admin)->get('/admin/traffic-exclusions?per_page=10&search=zz-paging-marker')
        ->assertInertia(fn (Assert $page) => $page
            ->has('rules', 5)
            ->where('pagination.meta.total', 5)
            ->where('filters.search', 'zz-paging-marker'));

    $this->actingAs($admin)->get('/admin/traffic-exclusions?per_page=10&page=2&search=zz-paging-marker')
        ->assertInertia(fn (Assert $page) => $page->has('rules', 0)->where('pagination.meta.total', 5));
});
