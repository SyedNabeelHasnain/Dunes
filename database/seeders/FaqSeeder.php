<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqsPath = database_path('seeders/data/faqs.json');
        if (!File::exists($faqsPath)) {
            return;
        }

        $faqs = json_decode(File::get($faqsPath), true);
        foreach ($faqs as $f) {
            Faq::updateOrCreate(
                ['id' => $f['id']],
                [
                    'question' => $f['question'],
                    'answer' => $f['answer'],
                    'category' => $f['category'] ?: 'general',
                    'priority' => (int)$f['priority'],
                    'status' => $f['status'] ?: 'active',
                ]
            );
        }

        // Seed FAQ Assignments
        $assignmentsPath = database_path('seeders/data/faq_assignments.json');
        if (File::exists($assignmentsPath)) {
            $assignments = json_decode(File::get($assignmentsPath), true);
            foreach ($assignments as $a) {
                DB::table('faq_assignments')->updateOrInsert(
                    ['id' => $a['id']],
                    [
                        'faq_id' => $a['faq_id'],
                        'entity_type' => $a['entity_type'] ?: 'general',
                        'entity_id' => $a['entity_id'] ? (int)$a['entity_id'] : null,
                    ]
                );
            }
        }
    }
}
