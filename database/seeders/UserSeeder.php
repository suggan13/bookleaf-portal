<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Users collection clean karein
        DB::connection('mongodb')->table('users')->truncate();

        // 1. Create Default Ops Admin Account
        User::create([
            'name' => 'BookLeaf Ops Admin',
            'email' => 'admin@bookleafpub.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'author_id' => null,
        ]);

        // 2. Sample Data JSON se har Author ka Account banayein
        $filePath = database_path('data/bookleaf_sample_data.json');
        if (File::exists($filePath)) {
            $data = json_decode(File::get($filePath), true);

            foreach ($data['authors'] as $author) {
                User::create([
                    'name' => $author['name'],
                    'email' => $author['email'],
                    'password' => Hash::make('password123'), // Common password for test login
                    'role' => 'author',
                    'author_id' => $author['author_id'],
                ]);
            }
        }

        $this->command->info('Users seeded: 1 Admin and 10 Authors ready!');
    }
}