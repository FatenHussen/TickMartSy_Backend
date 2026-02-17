<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalDocument;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $englishText = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et
dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea
commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat
nulla pariatur.";

        $arabicText = "هذا نص عربي تجريبي ليتم وضعه كمحتوى. يمكنك تعديله لاحقاً ليكون نص سياسة خصوصية أو شروط وأحكام حقيقية حسب الحاجة.";

        LegalDocument::create([
            'key' => 'privacy_policy',
            'title' => [
                'en' => 'Privacy Policy',
                'ar' => 'سياسة الخصوصية',
            ],
            'content' => [
                'en' => $englishText,
                'ar' => $arabicText,
            ],
        ]);

        LegalDocument::create([
            'key' => 'terms_conditions',
            'title' => [
                'en' => 'Terms & Conditions',
                'ar' => 'الشروط والأحكام',
            ],
            'content' => [
                'en' => $englishText,
                'ar' => $arabicText,
            ],
        ]);

        LegalDocument::create([
            'key' => 'marketer_terms_conditions',
            'title' => [
                'en' => 'Marketer Terms & Conditions',
                'ar' => 'شروط وأحكام المسوقين',
            ],
            'content' => [
                'en' => $englishText,
                'ar' => $arabicText,
            ],
        ]);
    }
}
