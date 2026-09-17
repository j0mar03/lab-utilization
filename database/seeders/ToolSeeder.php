<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * ToolSeeder
 *
 * Tools are now managed dynamically via the admin panel (Admin → Tools).
 * The Lab Head can add, edit, set quantities, and deactivate tools without
 * touching the database or code.
 *
 * This seeder is intentionally empty for the initial setup.
 * To add tools: log in as Lab Head → go to Admin → Tools → Add Tool.
 */
class ToolSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->line('');
        $this->command->info('ℹ️  Tools are managed via the admin panel — no tools seeded.');
        $this->command->line('   Log in as Lab Head → Admin → Tools → Add Tool');
        $this->command->line('');
    }
}
