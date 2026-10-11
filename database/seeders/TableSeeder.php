<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $number = sprintf('%02d', $i);
            Table::updateOrCreate(
                ['number' => $number],
                [
                    'token' => 'tbl_'.strtolower($number).'_'.Str::random(20),
                    'is_active' => true,
                ]
            );
        }
    }
}
