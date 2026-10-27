<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BookLeafSampleSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/bookleaf_sample_data.json');

        if (!File::exists($filePath)) {
            $this->command->error("JSON file not found at: {$filePath}");
            return;
        }

        $data = json_decode(File::get($filePath), true);

        if (!isset($data['authors'])) {
            $this->command->error("Invalid JSON format: 'authors' array missing.");
            return;
        }

        // Clean existing collections before seeding using table()
        DB::connection('mongodb')->table('authors')->truncate();
        DB::connection('mongodb')->table('books')->truncate();

        $authorsList = [];
        $booksList = [];

        foreach ($data['authors'] as $authorData) {
            $authorBooks = $authorData['books'] ?? [];

            // Author document
            $authorsList[] = [
                'author_id'   => $authorData['author_id'],
                'name'        => $authorData['name'],
                'email'       => $authorData['email'],
                'phone'       => $authorData['phone'],
                'city'        => $authorData['city'],
                'joined_date' => $authorData['joined_date'],
                'total_books' => count($authorBooks),
                'created_at'  => now(),
                'updated_at'  => now(),
            ];

            // Books collection documents
            foreach ($authorBooks as $book) {
                $booksList[] = array_merge($book, [
                    'author_id'   => $authorData['author_id'],
                    'author_name' => $authorData['name'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        // Insert into MongoDB collections using table()
        if (!empty($authorsList)) {
            DB::connection('mongodb')->table('authors')->insert($authorsList);
        }

        if (!empty($booksList)) {
            DB::connection('mongodb')->table('books')->insert($booksList);
        }

        $this->command->info('BookLeaf data seeded successfully: ' . count($authorsList) . ' authors and ' . count($booksList) . ' books created!');
    }
}