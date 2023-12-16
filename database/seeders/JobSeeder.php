<?php

namespace Database\Seeders;

use App\Models\Job;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobs = ['مهندس مقيم', 'مدير النظام'];

        collect($jobs)->each(fn ($job) => Job::create(['title' => $job]));
    }
}
