<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Author;
use App\Models\Book;
use App\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Wipe existing data for fresh evaluation run
        User::truncate();
        Author::truncate();
        Book::truncate();
        Ticket::truncate();

        // 2. Create BookLeaf Operations Admin
        User::create([
            'name'      => 'BookLeaf Operations',
            'email'     => 'admin@bookleafpub.com',
            'password'  => Hash::make('admin123'),
            'role'      => 'admin',
            'author_id' => null,
        ]);

        // 3. Official 10 Authors from Sample Dataset
        $authors = [
            ['author_id' => 'AUTH001', 'name' => 'Priya Sharma', 'email' => 'priya.sharma@email.com'],
            ['author_id' => 'AUTH002', 'name' => 'Rohit Kapoor', 'email' => 'rohit.kapoor@email.com'],
            ['author_id' => 'AUTH003', 'name' => 'Ananya Iyer', 'email' => 'ananya.iyer@email.com'],
            ['author_id' => 'AUTH004', 'name' => 'Vikram Malhotra', 'email' => 'vikram.malhotra@email.com'],
            ['author_id' => 'AUTH005', 'name' => 'Sunita Deshmukh', 'email' => 'sunita.deshmukh@email.com'],
            ['author_id' => 'AUTH006', 'name' => 'Arjun Nair', 'email' => 'arjun.nair@email.com'],
            ['author_id' => 'AUTH007', 'name' => 'Meera Patel', 'email' => 'meera.patel@email.com'],
            ['author_id' => 'AUTH008', 'name' => 'Kabir Sen', 'email' => 'kabir.sen@email.com'],
            ['author_id' => 'AUTH009', 'name' => 'Neha Verma', 'email' => 'neha.verma@email.com'],
            ['author_id' => 'AUTH010', 'name' => 'Devendra Joshi', 'email' => 'devendra.joshi@email.com'],
        ];

        foreach ($authors as $auth) {
            Author::create($auth);
            User::create([
                'name'      => $auth['name'],
                'email'     => $auth['email'],
                'password'  => Hash::make('password123'),
                'role'      => 'author',
                'author_id' => $auth['author_id'],
            ]);
        }

        // 4. Official 18 Books from Sample Dataset (Including Edge Cases)
        $books = [
            // AUTH001 - Priya Sharma
            [
                'book_id' => 'BK001', 'author_id' => 'AUTH001', 'title' => 'Whispers of the Ganges',
                'isbn' => '978-93-90001-01-1', 'genre' => 'Poetry', 'publication_date' => '2023-11-15',
                'status' => 'Published & Live', 'mrp' => 299, 'total_copies_sold' => 450,
                'total_royalty_earned' => 18900.00, 'royalty_paid' => 15000.00, 'royalty_pending' => 3900.00,
            ],
            [
                'book_id' => 'BK002', 'author_id' => 'AUTH001', 'title' => 'Varanasi Courtyards',
                'isbn' => '978-93-90001-02-8', 'genre' => 'Literary Fiction', 'publication_date' => '2024-03-10',
                'status' => 'Published & Live', 'mrp' => 399, 'total_copies_sold' => 120,
                'total_royalty_earned' => 6720.00, 'royalty_paid' => 5000.00, 'royalty_pending' => 1720.00,
            ],
            // AUTH002 - Rohit Kapoor
            [
                'book_id' => 'BK003', 'author_id' => 'AUTH002', 'title' => 'The Startup Crucible',
                'isbn' => '978-93-90001-03-5', 'genre' => 'Business & Management', 'publication_date' => '2023-08-20',
                'status' => 'Published & Live', 'mrp' => 499, 'total_copies_sold' => 880,
                'total_royalty_earned' => 52800.00, 'royalty_paid' => 52800.00, 'royalty_pending' => 0.00,
            ],
            [
                'book_id' => 'BK004', 'author_id' => 'AUTH002', 'title' => 'Zero to Pitch Deck',
                'isbn' => '978-93-90001-04-2', 'genre' => 'Entrepreneurship', 'publication_date' => '2024-01-15',
                'status' => 'Published & Live', 'mrp' => 349, 'total_copies_sold' => 40,
                'total_royalty_earned' => 1680.00, 'royalty_paid' => 0.00, 'royalty_pending' => 1680.00,
            ],
            // AUTH003 - Ananya Iyer
            [
                'book_id' => 'BK005', 'author_id' => 'AUTH003', 'title' => 'Monsoon Melodies',
                'isbn' => '978-93-90001-05-9', 'genre' => 'Romance', 'publication_date' => '2023-07-12',
                'status' => 'Published & Live', 'mrp' => 249, 'total_copies_sold' => 310,
                'total_royalty_earned' => 9300.00, 'royalty_paid' => 8500.00, 'royalty_pending' => 800.00, // Below 1,000 threshold
            ],
            [
                'book_id' => 'BK006', 'author_id' => 'AUTH003', 'title' => 'Autumn in Coorg',
                'isbn' => '978-93-90001-06-6', 'genre' => 'Contemporary Fiction', 'publication_date' => '2024-04-05',
                'status' => 'Published & Live', 'mrp' => 299, 'total_copies_sold' => 15,
                'total_royalty_earned' => 540.00, 'royalty_paid' => 0.00, 'royalty_pending' => 540.00, // Zero payout threshold rollover
            ],
            // AUTH004 - Vikram Malhotra
            [
                'book_id' => 'BK007', 'author_id' => 'AUTH004', 'title' => 'Shadows of the Fort',
                'isbn' => '978-93-90001-07-3', 'genre' => 'Historical Thriller', 'publication_date' => '2023-09-30',
                'status' => 'Published & Live', 'mrp' => 449, 'total_copies_sold' => 620,
                'total_royalty_earned' => 33480.00, 'royalty_paid' => 25000.00, 'royalty_pending' => 8480.00,
            ],
            [
                'book_id' => 'BK008', 'author_id' => 'AUTH004', 'title' => 'The Devanagari Cipher',
                'isbn' => '978-93-90001-08-0', 'genre' => 'Mystery', 'publication_date' => '2024-02-18',
                'status' => 'Published & Live', 'mrp' => 399, 'total_copies_sold' => 210,
                'total_royalty_earned' => 10080.00, 'royalty_paid' => 5000.00, 'royalty_pending' => 5080.00,
            ],
            // AUTH005 - Sunita Deshmukh (EDGE CASE: Books in Production Pipeline)
            [
                'book_id' => 'BK009', 'author_id' => 'AUTH005', 'title' => 'Culinary Heirlooms of Maharashtra',
                'isbn' => '978-93-90001-09-7', 'genre' => 'Cookbook & Food History', 'publication_date' => null,
                'status' => 'Proofreading', 'mrp' => 599, 'total_copies_sold' => 0,
                'total_royalty_earned' => 0.00, 'royalty_paid' => 0.00, 'royalty_pending' => 0.00,
            ],
            [
                'book_id' => 'BK010', 'author_id' => 'AUTH005', 'title' => 'Spices of the Sahyadri',
                'isbn' => '978-93-90001-10-3', 'genre' => 'Culinary Non-Fiction', 'publication_date' => null,
                'status' => 'Typesetting', 'mrp' => 499, 'total_copies_sold' => 0,
                'total_royalty_earned' => 0.00, 'royalty_paid' => 0.00, 'royalty_pending' => 0.00,
            ],
            // AUTH006 - Arjun Nair
            [
                'book_id' => 'BK011', 'author_id' => 'AUTH006', 'title' => 'Echoes of Malabar',
                'isbn' => '978-93-90001-11-0', 'genre' => 'Historical Non-Fiction', 'publication_date' => '2023-05-14',
                'status' => 'Published & Live', 'mrp' => 399, 'total_copies_sold' => 340,
                'total_royalty_earned' => 16320.00, 'royalty_paid' => 16320.00, 'royalty_pending' => 0.00,
            ],
            [
                'book_id' => 'BK012', 'author_id' => 'AUTH006', 'title' => 'The Spice Merchant\'s Ledger',
                'isbn' => '978-93-90001-12-7', 'genre' => 'Historical Fiction', 'publication_date' => '2024-01-22',
                'status' => 'Published & Live', 'mrp' => 349, 'total_copies_sold' => 95,
                'total_royalty_earned' => 3990.00, 'royalty_paid' => 2000.00, 'royalty_pending' => 1990.00,
            ],
            // AUTH007 - Meera Patel
            [
                'book_id' => 'BK013', 'author_id' => 'AUTH007', 'title' => 'Threads of Gujarat',
                'isbn' => '978-93-90001-13-4', 'genre' => 'Art & Culture', 'publication_date' => '2023-10-08',
                'status' => 'Published & Live', 'mrp' => 699, 'total_copies_sold' => 180,
                'total_royalty_earned' => 15120.00, 'royalty_paid' => 10000.00, 'royalty_pending' => 5120.00,
            ],
            // AUTH008 - Kabir Sen
            [
                'book_id' => 'BK014', 'author_id' => 'AUTH008', 'title' => 'Streets of Calcutta',
                'isbn' => '978-93-90001-14-1', 'genre' => 'Photography & Essays', 'publication_date' => '2023-06-25',
                'status' => 'Published & Live', 'mrp' => 799, 'total_copies_sold' => 260,
                'total_royalty_earned' => 24960.00, 'royalty_paid' => 20000.00, 'royalty_pending' => 4960.00,
            ],
            [
                'book_id' => 'BK015', 'author_id' => 'AUTH008', 'title' => 'Tramline Memories',
                'isbn' => '978-93-90001-15-8', 'genre' => 'Poetry Collection', 'publication_date' => '2024-03-01',
                'status' => 'Published & Live', 'mrp' => 199, 'total_copies_sold' => 80,
                'total_royalty_earned' => 1920.00, 'royalty_paid' => 0.00, 'royalty_pending' => 1920.00,
            ],
            // AUTH009 - Neha Verma
            [
                'book_id' => 'BK016', 'author_id' => 'AUTH009', 'title' => 'The Mindful Leader',
                'isbn' => '978-93-90001-16-5', 'genre' => 'Self-Help & Psychology', 'publication_date' => '2023-12-05',
                'status' => 'Published & Live', 'mrp' => 349, 'total_copies_sold' => 510,
                'total_royalty_earned' => 21420.00, 'royalty_paid' => 15000.00, 'royalty_pending' => 6420.00,
            ],
            // AUTH010 - Devendra Joshi
            [
                'book_id' => 'BK017', 'author_id' => 'AUTH010', 'title' => 'Himalayan Trails',
                'isbn' => '978-93-90001-17-2', 'genre' => 'Travel & Adventure', 'publication_date' => '2023-04-18',
                'status' => 'Published & Live', 'mrp' => 449, 'total_copies_sold' => 740,
                'total_royalty_earned' => 39960.00, 'royalty_paid' => 35000.00, 'royalty_pending' => 4960.00,
            ],
            [
                'book_id' => 'BK018', 'author_id' => 'AUTH010', 'title' => 'Flora of the Ridge',
                'isbn' => '978-93-90001-18-9', 'genre' => 'Botany & Nature', 'publication_date' => '2024-02-11',
                'status' => 'Published & Live', 'mrp' => 549, 'total_copies_sold' => 65,
                'total_royalty_earned' => 4290.00, 'royalty_paid' => 0.00, 'royalty_pending' => 4290.00,
            ],
        ];

        foreach ($books as $bk) {
            Book::create($bk);
        }

        // 5. Initial Seed Tickets to demonstrate AI Triage immediately
        Ticket::create([
            'ticket_id'          => 'TCK-0001',
            'author_id'          => 'AUTH001',
            'author_name'        => 'Priya Sharma',
            'book_id'            => 'BK001',
            'book_title'         => 'Whispers of the Ganges',
            'subject'            => 'Q2 Royalty payout overdue by 3 months',
            'description'        => 'My pending royalty balance has crossed ₹3,000 for over 90 days. I have not received bank transfer.',
            'category'           => 'Royalty & Payments',
            'priority'           => 'Critical',
            'status'             => 'Open',
            'ai_draft_response'  => "Dear Priya,\n\nThank you for reaching out to BookLeaf Publishing. We acknowledge your concern regarding pending royalties for 'Whispers of the Ganges'.\n\nPer BookLeaf's Royalty Policy, royalties are calculated quarterly (80/20 net profit split) and disbursed within 45 days post quarter ending for balances above the ₹1,000 threshold. We have flagged your account with our Finance Desk for verification within 48 hours.\n\nWarm regards,\nBookLeaf Operations Team",
            'admin_response'     => null,
            'internal_notes'     => null,
            'assigned_to'        => null,
            'assigned_name'      => null,
            'created_at'         => now()->subDays(2)->toIso8601String(),
        ]);

        Ticket::create([
            'ticket_id'          => 'TCK-0002',
            'author_id'          => 'AUTH005',
            'author_name'        => 'Sunita Deshmukh',
            'book_id'            => 'BK009',
            'book_title'         => 'Culinary Heirlooms of Maharashtra',
            'subject'            => 'When will typesetting and proofreading complete?',
            'description'        => 'My manuscript was submitted last month. Could you provide a clear schedule on when proofreading rounds will be completed?',
            'category'           => 'Book Status & Production Updates',
            'priority'           => 'Medium',
            'status'             => 'Open',
            'ai_draft_response'  => "Dear Sunita,\n\nThank you for inquiring about the production progress of 'Culinary Heirlooms of Maharashtra'.\n\nYour book proceeds sequentially through proofreading, typesetting, ISBN registration, and proof sign-off. Our editorial supervisor is reviewing your files and will update your dashboard with the scheduled timeline within 2 business days.\n\nWarm regards,\nBookLeaf Editorial Team",
            'admin_response'     => null,
            'internal_notes'     => null,
            'assigned_to'        => null,
            'assigned_name'      => null,
            'created_at'         => now()->subDay()->toIso8601String(),
        ]);
    }
}