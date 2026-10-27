<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
        $this->baseUrl = rtrim(env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');
    }

    /**
     * BookLeaf Official Knowledge Base Context
     */
    protected function getKnowledgeBase(): string
    {
        return <<<TEXT
BookLeaf Publishing Knowledge Base & Operational Guidelines:
- Company: Self-publishing company operating in India & US. In-house print/warehouse in Delhi; partners Repro India & Epitome Books.
- Royalty Policy: 80/20 net profit split (80% to author, 20% to BookLeaf). Net profit = MRP minus printing cost, platform commission (Amazon/Flipkart), and shipping charges. Paid quarterly within 45 days post quarter end. ₹1,000 minimum payout threshold (rolls over if under).
- ISBN Policy: BookLeaf provides unique ISBNs under BookLeaf publisher imprint. Duplicate or wrong ISBN on Amazon is treated as high priority and escalated to production.
- Printing & Quality: Turnaround is 5-7 business days. Defective copies (misprints, binding defects, color inconsistency) get free reprints after photo verification.
- Distribution: Amazon India, Flipkart, Amazon US/UK, BookLeaf Store. Goes live 7-10 business days post-publication. "Currently Unavailable" usually means stock sync issue (re-synced in 24-48 hours).
- Production Stages: Manuscript Received -> Editing -> Cover Design -> Typesetting -> Proofreading -> ISBN Assignment -> Printing -> Distribution -> Published & Live.
- Communication Tone: Empathetic, professional, partners not customers. Acknowledge frustration first, provide concrete numbers/dates, own errors directly, provide clear timelines.
TEXT;
    }

    /**
     * Triage ticket: Auto-classify category and priority score via LLM
     */
    public function triageTicket(string $subject, string $description, ?string $bookTitle = null): array
    {
        if (!$this->apiKey) {
            return $this->fallbackTriage($subject, $description);
        }

        try {
            $prompt = <<<PROMPT
You are the AI triage assistant for BookLeaf Publishing.
Analyze the following author support query:
Book Title: {$bookTitle}
Subject: {$subject}
Description: {$description}

Classify into EXACTLY one of these categories:
- Royalty & Payments
- ISBN & Metadata Issues
- Printing & Quality
- Distribution & Availability
- Book Status & Production Updates
- General Inquiry

Assign a priority score:
- Critical (e.g., long delayed royalties, financial distress, serious duplicate ISBN)
- High (e.g., defective author copies received, book unavailable on launch)
- Medium (e.g., status inquiry within reasonable window, routine royalty question)
- Low (e.g., updating author bio, general question)

Respond strictly with a valid JSON object only:
{"category": "<category>", "priority": "<priority>"}
PROMPT;

            $response = Http::withToken($this->apiKey)
                ->timeout(8)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a strict JSON-only classifier. Return only raw JSON without code fences or formatting.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.1
                ]);

            if ($response->successful()) {
                $rawContent = trim($response->json('choices.0.message.content', ''));
                $cleanJson = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $rawContent);
                $data = json_decode($cleanJson, true);

                if (isset($data['category']) && isset($data['priority'])) {
                    return [
                        'category' => $data['category'],
                        'priority' => $data['priority']
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('LLM Triage API failed, fallback engaged: ' . $e->getMessage());
        }

        return $this->fallbackTriage($subject, $description);
    }

    /**
     * Generate policy-grounded draft response for operations admin
     */
    public function generateDraftResponse(array $ticket): string
    {
        $author = $ticket['author_name'] ?? 'Author';
        $book = $ticket['book_title'] ?? 'your title';
        $subject = $ticket['subject'] ?? '';
        $description = $ticket['description'] ?? '';
        $category = $ticket['category'] ?? 'General Inquiry';

        if (!$this->apiKey) {
            return $this->fallbackDraft($author, $book, $category, $description);
        }

        try {
            $kb = $this->getKnowledgeBase();
            $systemMessage = "You are a senior support lead at BookLeaf Publishing. Ground your response strictly on BookLeaf policies:\n" . $kb;
            
            $userMessage = <<<USER
Author: {$author}
Book: {$book}
Category: {$category}
Subject: {$subject}
Query: {$description}

Draft an official, empathetic response to the author. 
- Acknowledge their concern first.
- Explain relevant policy details accurately (80/20 split, 45-day window, ₹1,000 threshold, or 5-7 day reprint turnaround).
- Give an actionable next step or clear resolution timeline (e.g., 24-48 hours).
- Do not make generic promises. Be concrete and supportive.
USER;

            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemMessage],
                        ['role' => 'user', 'content' => $userMessage]
                    ],
                    'temperature' => 0.3
                ]);

            if ($response->successful()) {
                $content = trim($response->json('choices.0.message.content', ''));
                if (!empty($content)) {
                    return $content;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('LLM Draft API failed, fallback engaged: ' . $e->getMessage());
        }

        return $this->fallbackDraft($author, $book, $category, $description);
    }

    /**
     * Graceful fallback triage when API is unavailable / offline
     */
    protected function fallbackTriage(string $subject, string $description): array
    {
        $text = strtolower($subject . ' ' . $description);

        if (str_contains($text, 'royalt') || str_contains($text, 'payment') || str_contains($text, 'payout') || str_contains($text, 'money') || str_contains($text, 'earn')) {
            $priority = (str_contains($text, 'month') || str_contains($text, 'delay') || str_contains($text, 'not received') || str_contains($text, 'never')) ? 'Critical' : 'High';
            return ['category' => 'Royalty & Payments', 'priority' => $priority];
        }

        if (str_contains($text, 'isbn') || str_contains($text, 'metadata') || str_contains($text, 'barcode')) {
            return ['category' => 'ISBN & Metadata Issues', 'priority' => 'High'];
        }

        if (str_contains($text, 'print') || str_contains($text, 'quality') || str_contains($text, 'binding') || str_contains($text, 'blur') || str_contains($text, 'defect')) {
            return ['category' => 'Printing & Quality', 'priority' => 'High'];
        }

        if (str_contains($text, 'amazon') || str_contains($text, 'flipkart') || str_contains($text, 'stock') || str_contains($text, 'unavailable') || str_contains($text, 'out of stock')) {
            return ['category' => 'Distribution & Availability', 'priority' => 'Medium'];
        }

        if (str_contains($text, 'typeset') || str_contains($text, 'proof') || str_contains($text, 'cover') || str_contains($text, 'status') || str_contains($text, 'stage')) {
            return ['category' => 'Book Status & Production Updates', 'priority' => 'Medium'];
        }

        return ['category' => 'General Inquiry', 'priority' => 'Low'];
    }

    /**
     * Graceful fallback draft generation calibrated with BookLeaf policies
     */
    protected function fallbackDraft(string $author, string $book, string $category, string $description): string
    {
        $salutation = "Dear {$author},\n\nThank you for reaching out to BookLeaf Publishing.\n\n";
        $signoff = "\n\nPlease let us know if you have any questions in the interim.\n\nWarm regards,\nBookLeaf Operations Team";

        switch ($category) {
            case 'Royalty & Payments':
                $body = "We understand your concern regarding the royalty disbursement for '{$book}'. BookLeaf follows an 80/20 net profit royalty structure calculated quarterly and disbursed within 45 days after the close of the quarter, subject to our ₹1,000 threshold. We have flagged your account with our finance desk for priority verification and will update you within 48 hours.";
                break;
            case 'Printing & Quality':
                $body = "We sincerely apologize for the quality issue experienced with your copies of '{$book}'. BookLeaf provides free reprints for any verified binding, alignment, or color defects. Please reply with photos of the damaged copies so our production team can approve and dispatch a replacement within 5-7 business days.";
                break;
            case 'Distribution & Availability':
                $body = "Thank you for alerting us regarding the availability of '{$book}'. Listings on Amazon and Flipkart occasionally face temporary inventory sync delays. Our team has triggered a manual stock re-sync with our channel partners, which will restore live purchasing status within 24-48 business hours.";
                break;
            case 'ISBN & Metadata Issues':
                $body = "We take catalog accuracy very seriously. We have escalated the ISBN record for '{$book}' directly to our production head for immediate correction on retail databases within 48 hours.";
                break;
            default:
                $body = "We have received your query regarding '{$book}'. Our support desk is reviewing your account records against standard publishing guidelines and will provide a full update within 24 business hours.";
                break;
        }

        return $salutation . $body . $signoff;
    }
}