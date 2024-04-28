<?php

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Channel::where('is_default', true)->exists()) {
            Channel::create([
                'name'       => 'Webstore',
                'is_default' => true,
                'url'        => 'http://localhost',
            ]);
        }
    }
}
