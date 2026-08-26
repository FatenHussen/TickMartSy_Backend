<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Seed all world countries (ISO 3166-1 alpha-2 as code).
     * Matches existing rows by English name so re-runs stay safe.
     */
    public function run(): void
    {
        foreach ($this->countries() as $country) {
            $existing = Country::query()
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) = ?", [$country['name']['en']])
                ->first();

            if ($existing) {
                // لا تغيّر is_active ولا تعيد كتابة الأسماء إذا الصف موجود — ثبّت الـ id
                $updates = [];
                if (blank($existing->code) && filled($country['code'])) {
                    $codeTaken = Country::query()
                        ->where('code', $country['code'])
                        ->where('id', '!=', $existing->id)
                        ->exists();
                    if (! $codeTaken) {
                        $updates['code'] = $country['code'];
                    }
                }
                if ($updates !== []) {
                    $existing->update($updates);
                }
                continue;
            }

            Country::firstOrCreate(
                ['code' => $country['code']],
                [
                    'name' => $country['name'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * @return list<array{name: array{en: string, ar: string}, code: string}>
     */
    private function countries(): array
    {
        return [
            ['name' => ['en' => 'Afghanistan', 'ar' => 'أفغانستان'], 'code' => 'AF'],
            ['name' => ['en' => 'Albania', 'ar' => 'ألبانيا'], 'code' => 'AL'],
            ['name' => ['en' => 'Algeria', 'ar' => 'الجزائر'], 'code' => 'DZ'],
            ['name' => ['en' => 'Andorra', 'ar' => 'أندورا'], 'code' => 'AD'],
            ['name' => ['en' => 'Angola', 'ar' => 'أنغولا'], 'code' => 'AO'],
            ['name' => ['en' => 'Antigua and Barbuda', 'ar' => 'أنتيغوا وباربودا'], 'code' => 'AG'],
            ['name' => ['en' => 'Argentina', 'ar' => 'الأرجنتين'], 'code' => 'AR'],
            ['name' => ['en' => 'Armenia', 'ar' => 'أرمينيا'], 'code' => 'AM'],
            ['name' => ['en' => 'Australia', 'ar' => 'أستراليا'], 'code' => 'AU'],
            ['name' => ['en' => 'Austria', 'ar' => 'النمسا'], 'code' => 'AT'],
            ['name' => ['en' => 'Azerbaijan', 'ar' => 'أذربيجان'], 'code' => 'AZ'],
            ['name' => ['en' => 'Bahamas', 'ar' => 'الباهاما'], 'code' => 'BS'],
            ['name' => ['en' => 'Bahrain', 'ar' => 'البحرين'], 'code' => 'BH'],
            ['name' => ['en' => 'Bangladesh', 'ar' => 'بنغلاديش'], 'code' => 'BD'],
            ['name' => ['en' => 'Barbados', 'ar' => 'باربادوس'], 'code' => 'BB'],
            ['name' => ['en' => 'Belarus', 'ar' => 'بيلاروس'], 'code' => 'BY'],
            ['name' => ['en' => 'Belgium', 'ar' => 'بلجيكا'], 'code' => 'BE'],
            ['name' => ['en' => 'Belize', 'ar' => 'بليز'], 'code' => 'BZ'],
            ['name' => ['en' => 'Benin', 'ar' => 'بنين'], 'code' => 'BJ'],
            ['name' => ['en' => 'Bhutan', 'ar' => 'بوتان'], 'code' => 'BT'],
            ['name' => ['en' => 'Bolivia', 'ar' => 'بوليفيا'], 'code' => 'BO'],
            ['name' => ['en' => 'Bosnia and Herzegovina', 'ar' => 'البوسنة والهرسك'], 'code' => 'BA'],
            ['name' => ['en' => 'Botswana', 'ar' => 'بوتسوانا'], 'code' => 'BW'],
            ['name' => ['en' => 'Brazil', 'ar' => 'البرازيل'], 'code' => 'BR'],
            ['name' => ['en' => 'Brunei', 'ar' => 'بروناي'], 'code' => 'BN'],
            ['name' => ['en' => 'Bulgaria', 'ar' => 'بلغاريا'], 'code' => 'BG'],
            ['name' => ['en' => 'Burkina Faso', 'ar' => 'بوركينا فاسو'], 'code' => 'BF'],
            ['name' => ['en' => 'Burundi', 'ar' => 'بوروندي'], 'code' => 'BI'],
            ['name' => ['en' => 'Cabo Verde', 'ar' => 'الرأس الأخضر'], 'code' => 'CV'],
            ['name' => ['en' => 'Cambodia', 'ar' => 'كمبوديا'], 'code' => 'KH'],
            ['name' => ['en' => 'Cameroon', 'ar' => 'الكاميرون'], 'code' => 'CM'],
            ['name' => ['en' => 'Canada', 'ar' => 'كندا'], 'code' => 'CA'],
            ['name' => ['en' => 'Central African Republic', 'ar' => 'جمهورية أفريقيا الوسطى'], 'code' => 'CF'],
            ['name' => ['en' => 'Chad', 'ar' => 'تشاد'], 'code' => 'TD'],
            ['name' => ['en' => 'Chile', 'ar' => 'تشيلي'], 'code' => 'CL'],
            ['name' => ['en' => 'China', 'ar' => 'الصين'], 'code' => 'CN'],
            ['name' => ['en' => 'Colombia', 'ar' => 'كولومبيا'], 'code' => 'CO'],
            ['name' => ['en' => 'Comoros', 'ar' => 'جزر القمر'], 'code' => 'KM'],
            ['name' => ['en' => 'Congo', 'ar' => 'الكونغو'], 'code' => 'CG'],
            ['name' => ['en' => 'Costa Rica', 'ar' => 'كوستاريكا'], 'code' => 'CR'],
            ['name' => ['en' => 'Croatia', 'ar' => 'كرواتيا'], 'code' => 'HR'],
            ['name' => ['en' => 'Cuba', 'ar' => 'كوبا'], 'code' => 'CU'],
            ['name' => ['en' => 'Cyprus', 'ar' => 'قبرص'], 'code' => 'CY'],
            ['name' => ['en' => 'Czech Republic', 'ar' => 'التشيك'], 'code' => 'CZ'],
            ['name' => ['en' => 'Democratic Republic of the Congo', 'ar' => 'جمهورية الكونغو الديمقراطية'], 'code' => 'CD'],
            ['name' => ['en' => 'Denmark', 'ar' => 'الدنمارك'], 'code' => 'DK'],
            ['name' => ['en' => 'Djibouti', 'ar' => 'جيبوتي'], 'code' => 'DJ'],
            ['name' => ['en' => 'Dominica', 'ar' => 'دومينيكا'], 'code' => 'DM'],
            ['name' => ['en' => 'Dominican Republic', 'ar' => 'جمهورية الدومينيكان'], 'code' => 'DO'],
            ['name' => ['en' => 'Ecuador', 'ar' => 'الإكوادور'], 'code' => 'EC'],
            ['name' => ['en' => 'Egypt', 'ar' => 'مصر'], 'code' => 'EG'],
            ['name' => ['en' => 'El Salvador', 'ar' => 'السلفادور'], 'code' => 'SV'],
            ['name' => ['en' => 'Equatorial Guinea', 'ar' => 'غينيا الاستوائية'], 'code' => 'GQ'],
            ['name' => ['en' => 'Eritrea', 'ar' => 'إريتريا'], 'code' => 'ER'],
            ['name' => ['en' => 'Estonia', 'ar' => 'إستونيا'], 'code' => 'EE'],
            ['name' => ['en' => 'Eswatini', 'ar' => 'إسواتيني'], 'code' => 'SZ'],
            ['name' => ['en' => 'Ethiopia', 'ar' => 'إثيوبيا'], 'code' => 'ET'],
            ['name' => ['en' => 'Fiji', 'ar' => 'فيجي'], 'code' => 'FJ'],
            ['name' => ['en' => 'Finland', 'ar' => 'فنلندا'], 'code' => 'FI'],
            ['name' => ['en' => 'France', 'ar' => 'فرنسا'], 'code' => 'FR'],
            ['name' => ['en' => 'Gabon', 'ar' => 'الغابون'], 'code' => 'GA'],
            ['name' => ['en' => 'Gambia', 'ar' => 'غامبيا'], 'code' => 'GM'],
            ['name' => ['en' => 'Georgia', 'ar' => 'جورجيا'], 'code' => 'GE'],
            ['name' => ['en' => 'Germany', 'ar' => 'ألمانيا'], 'code' => 'DE'],
            ['name' => ['en' => 'Ghana', 'ar' => 'غانا'], 'code' => 'GH'],
            ['name' => ['en' => 'Greece', 'ar' => 'اليونان'], 'code' => 'GR'],
            ['name' => ['en' => 'Grenada', 'ar' => 'غرينادا'], 'code' => 'GD'],
            ['name' => ['en' => 'Guatemala', 'ar' => 'غواتيمالا'], 'code' => 'GT'],
            ['name' => ['en' => 'Guinea', 'ar' => 'غينيا'], 'code' => 'GN'],
            ['name' => ['en' => 'Guinea-Bissau', 'ar' => 'غينيا بيساو'], 'code' => 'GW'],
            ['name' => ['en' => 'Guyana', 'ar' => 'غيانا'], 'code' => 'GY'],
            ['name' => ['en' => 'Haiti', 'ar' => 'هايتي'], 'code' => 'HT'],
            ['name' => ['en' => 'Honduras', 'ar' => 'هندوراس'], 'code' => 'HN'],
            ['name' => ['en' => 'Hungary', 'ar' => 'المجر'], 'code' => 'HU'],
            ['name' => ['en' => 'Iceland', 'ar' => 'آيسلندا'], 'code' => 'IS'],
            ['name' => ['en' => 'India', 'ar' => 'الهند'], 'code' => 'IN'],
            ['name' => ['en' => 'Indonesia', 'ar' => 'إندونيسيا'], 'code' => 'ID'],
            ['name' => ['en' => 'Iran', 'ar' => 'إيران'], 'code' => 'IR'],
            ['name' => ['en' => 'Iraq', 'ar' => 'العراق'], 'code' => 'IQ'],
            ['name' => ['en' => 'Ireland', 'ar' => 'أيرلندا'], 'code' => 'IE'],
            ['name' => ['en' => 'Italy', 'ar' => 'إيطاليا'], 'code' => 'IT'],
            ['name' => ['en' => 'Ivory Coast', 'ar' => 'ساحل العاج'], 'code' => 'CI'],
            ['name' => ['en' => 'Jamaica', 'ar' => 'جامايكا'], 'code' => 'JM'],
            ['name' => ['en' => 'Japan', 'ar' => 'اليابان'], 'code' => 'JP'],
            ['name' => ['en' => 'Jordan', 'ar' => 'الأردن'], 'code' => 'JO'],
            ['name' => ['en' => 'Kazakhstan', 'ar' => 'كازاخستان'], 'code' => 'KZ'],
            ['name' => ['en' => 'Kenya', 'ar' => 'كينيا'], 'code' => 'KE'],
            ['name' => ['en' => 'Kiribati', 'ar' => 'كيريباتي'], 'code' => 'KI'],
            ['name' => ['en' => 'Kuwait', 'ar' => 'الكويت'], 'code' => 'KW'],
            ['name' => ['en' => 'Kyrgyzstan', 'ar' => 'قيرغيزستان'], 'code' => 'KG'],
            ['name' => ['en' => 'Laos', 'ar' => 'لاوس'], 'code' => 'LA'],
            ['name' => ['en' => 'Latvia', 'ar' => 'لاتفيا'], 'code' => 'LV'],
            ['name' => ['en' => 'Lebanon', 'ar' => 'لبنان'], 'code' => 'LB'],
            ['name' => ['en' => 'Lesotho', 'ar' => 'ليسوتو'], 'code' => 'LS'],
            ['name' => ['en' => 'Liberia', 'ar' => 'ليبيريا'], 'code' => 'LR'],
            ['name' => ['en' => 'Libya', 'ar' => 'ليبيا'], 'code' => 'LY'],
            ['name' => ['en' => 'Liechtenstein', 'ar' => 'ليختنشتاين'], 'code' => 'LI'],
            ['name' => ['en' => 'Lithuania', 'ar' => 'ليتوانيا'], 'code' => 'LT'],
            ['name' => ['en' => 'Luxembourg', 'ar' => 'لوكسمبورغ'], 'code' => 'LU'],
            ['name' => ['en' => 'Madagascar', 'ar' => 'مدغشقر'], 'code' => 'MG'],
            ['name' => ['en' => 'Malawi', 'ar' => 'ملاوي'], 'code' => 'MW'],
            ['name' => ['en' => 'Malaysia', 'ar' => 'ماليزيا'], 'code' => 'MY'],
            ['name' => ['en' => 'Maldives', 'ar' => 'المالديف'], 'code' => 'MV'],
            ['name' => ['en' => 'Mali', 'ar' => 'مالي'], 'code' => 'ML'],
            ['name' => ['en' => 'Malta', 'ar' => 'مالطا'], 'code' => 'MT'],
            ['name' => ['en' => 'Marshall Islands', 'ar' => 'جزر مارشال'], 'code' => 'MH'],
            ['name' => ['en' => 'Mauritania', 'ar' => 'موريتانيا'], 'code' => 'MR'],
            ['name' => ['en' => 'Mauritius', 'ar' => 'موريشيوس'], 'code' => 'MU'],
            ['name' => ['en' => 'Mexico', 'ar' => 'المكسيك'], 'code' => 'MX'],
            ['name' => ['en' => 'Micronesia', 'ar' => 'ميكرونيزيا'], 'code' => 'FM'],
            ['name' => ['en' => 'Moldova', 'ar' => 'مولدوفا'], 'code' => 'MD'],
            ['name' => ['en' => 'Monaco', 'ar' => 'موناكو'], 'code' => 'MC'],
            ['name' => ['en' => 'Mongolia', 'ar' => 'منغوليا'], 'code' => 'MN'],
            ['name' => ['en' => 'Montenegro', 'ar' => 'الجبل الأسود'], 'code' => 'ME'],
            ['name' => ['en' => 'Morocco', 'ar' => 'المغرب'], 'code' => 'MA'],
            ['name' => ['en' => 'Mozambique', 'ar' => 'موزمبيق'], 'code' => 'MZ'],
            ['name' => ['en' => 'Myanmar', 'ar' => 'ميانمار'], 'code' => 'MM'],
            ['name' => ['en' => 'Namibia', 'ar' => 'ناميبيا'], 'code' => 'NA'],
            ['name' => ['en' => 'Nauru', 'ar' => 'ناورو'], 'code' => 'NR'],
            ['name' => ['en' => 'Nepal', 'ar' => 'نيبال'], 'code' => 'NP'],
            ['name' => ['en' => 'Netherlands', 'ar' => 'هولندا'], 'code' => 'NL'],
            ['name' => ['en' => 'New Zealand', 'ar' => 'نيوزيلندا'], 'code' => 'NZ'],
            ['name' => ['en' => 'Nicaragua', 'ar' => 'نيكاراغوا'], 'code' => 'NI'],
            ['name' => ['en' => 'Niger', 'ar' => 'النيجر'], 'code' => 'NE'],
            ['name' => ['en' => 'Nigeria', 'ar' => 'نيجيريا'], 'code' => 'NG'],
            ['name' => ['en' => 'North Korea', 'ar' => 'كوريا الشمالية'], 'code' => 'KP'],
            ['name' => ['en' => 'North Macedonia', 'ar' => 'مقدونيا الشمالية'], 'code' => 'MK'],
            ['name' => ['en' => 'Norway', 'ar' => 'النرويج'], 'code' => 'NO'],
            ['name' => ['en' => 'Oman', 'ar' => 'عُمان'], 'code' => 'OM'],
            ['name' => ['en' => 'Pakistan', 'ar' => 'باكستان'], 'code' => 'PK'],
            ['name' => ['en' => 'Palau', 'ar' => 'بالاو'], 'code' => 'PW'],
            ['name' => ['en' => 'Palestine', 'ar' => 'فلسطين'], 'code' => 'PS'],
            ['name' => ['en' => 'Panama', 'ar' => 'بنما'], 'code' => 'PA'],
            ['name' => ['en' => 'Papua New Guinea', 'ar' => 'بابوا غينيا الجديدة'], 'code' => 'PG'],
            ['name' => ['en' => 'Paraguay', 'ar' => 'باراغواي'], 'code' => 'PY'],
            ['name' => ['en' => 'Peru', 'ar' => 'بيرو'], 'code' => 'PE'],
            ['name' => ['en' => 'Philippines', 'ar' => 'الفلبين'], 'code' => 'PH'],
            ['name' => ['en' => 'Poland', 'ar' => 'بولندا'], 'code' => 'PL'],
            ['name' => ['en' => 'Portugal', 'ar' => 'البرتغال'], 'code' => 'PT'],
            ['name' => ['en' => 'Qatar', 'ar' => 'قطر'], 'code' => 'QA'],
            ['name' => ['en' => 'Romania', 'ar' => 'رومانيا'], 'code' => 'RO'],
            ['name' => ['en' => 'Russia', 'ar' => 'روسيا'], 'code' => 'RU'],
            ['name' => ['en' => 'Rwanda', 'ar' => 'رواندا'], 'code' => 'RW'],
            ['name' => ['en' => 'Saint Kitts and Nevis', 'ar' => 'سانت كيتس ونيفيس'], 'code' => 'KN'],
            ['name' => ['en' => 'Saint Lucia', 'ar' => 'سانت لوسيا'], 'code' => 'LC'],
            ['name' => ['en' => 'Saint Vincent and the Grenadines', 'ar' => 'سانت فينسنت والغرينادين'], 'code' => 'VC'],
            ['name' => ['en' => 'Samoa', 'ar' => 'ساموا'], 'code' => 'WS'],
            ['name' => ['en' => 'San Marino', 'ar' => 'سان مارينو'], 'code' => 'SM'],
            ['name' => ['en' => 'Sao Tome and Principe', 'ar' => 'ساو تومي وبرينسيبي'], 'code' => 'ST'],
            ['name' => ['en' => 'Saudi Arabia', 'ar' => 'السعودية'], 'code' => 'SA'],
            ['name' => ['en' => 'Senegal', 'ar' => 'السنغال'], 'code' => 'SN'],
            ['name' => ['en' => 'Serbia', 'ar' => 'صربيا'], 'code' => 'RS'],
            ['name' => ['en' => 'Seychelles', 'ar' => 'سيشل'], 'code' => 'SC'],
            ['name' => ['en' => 'Sierra Leone', 'ar' => 'سيراليون'], 'code' => 'SL'],
            ['name' => ['en' => 'Singapore', 'ar' => 'سنغافورة'], 'code' => 'SG'],
            ['name' => ['en' => 'Slovakia', 'ar' => 'سلوفاكيا'], 'code' => 'SK'],
            ['name' => ['en' => 'Slovenia', 'ar' => 'سلوفينيا'], 'code' => 'SI'],
            ['name' => ['en' => 'Solomon Islands', 'ar' => 'جزر سليمان'], 'code' => 'SB'],
            ['name' => ['en' => 'Somalia', 'ar' => 'الصومال'], 'code' => 'SO'],
            ['name' => ['en' => 'South Africa', 'ar' => 'جنوب أفريقيا'], 'code' => 'ZA'],
            ['name' => ['en' => 'South Korea', 'ar' => 'كوريا الجنوبية'], 'code' => 'KR'],
            ['name' => ['en' => 'South Sudan', 'ar' => 'جنوب السودان'], 'code' => 'SS'],
            ['name' => ['en' => 'Spain', 'ar' => 'إسبانيا'], 'code' => 'ES'],
            ['name' => ['en' => 'Sri Lanka', 'ar' => 'سريلانكا'], 'code' => 'LK'],
            ['name' => ['en' => 'Sudan', 'ar' => 'السودان'], 'code' => 'SD'],
            ['name' => ['en' => 'Suriname', 'ar' => 'سورينام'], 'code' => 'SR'],
            ['name' => ['en' => 'Sweden', 'ar' => 'السويد'], 'code' => 'SE'],
            ['name' => ['en' => 'Switzerland', 'ar' => 'سويسرا'], 'code' => 'CH'],
            ['name' => ['en' => 'Syria', 'ar' => 'سوريا'], 'code' => 'SY'],
            ['name' => ['en' => 'Taiwan', 'ar' => 'تايوان'], 'code' => 'TW'],
            ['name' => ['en' => 'Tajikistan', 'ar' => 'طاجيكستان'], 'code' => 'TJ'],
            ['name' => ['en' => 'Tanzania', 'ar' => 'تنزانيا'], 'code' => 'TZ'],
            ['name' => ['en' => 'Thailand', 'ar' => 'تايلاند'], 'code' => 'TH'],
            ['name' => ['en' => 'Timor-Leste', 'ar' => 'تيمور الشرقية'], 'code' => 'TL'],
            ['name' => ['en' => 'Togo', 'ar' => 'توغو'], 'code' => 'TG'],
            ['name' => ['en' => 'Tonga', 'ar' => 'تونغا'], 'code' => 'TO'],
            ['name' => ['en' => 'Trinidad and Tobago', 'ar' => 'ترينيداد وتوباغو'], 'code' => 'TT'],
            ['name' => ['en' => 'Tunisia', 'ar' => 'تونس'], 'code' => 'TN'],
            ['name' => ['en' => 'Turkey', 'ar' => 'تركيا'], 'code' => 'TR'],
            ['name' => ['en' => 'Turkmenistan', 'ar' => 'تركمانستان'], 'code' => 'TM'],
            ['name' => ['en' => 'Tuvalu', 'ar' => 'توفالو'], 'code' => 'TV'],
            ['name' => ['en' => 'Uganda', 'ar' => 'أوغندا'], 'code' => 'UG'],
            ['name' => ['en' => 'Ukraine', 'ar' => 'أوكرانيا'], 'code' => 'UA'],
            ['name' => ['en' => 'United Arab Emirates', 'ar' => 'الإمارات'], 'code' => 'AE'],
            ['name' => ['en' => 'United Kingdom', 'ar' => 'المملكة المتحدة'], 'code' => 'GB'],
            ['name' => ['en' => 'USA', 'ar' => 'أمريكا'], 'code' => 'US'],
            ['name' => ['en' => 'Uruguay', 'ar' => 'الأوروغواي'], 'code' => 'UY'],
            ['name' => ['en' => 'Uzbekistan', 'ar' => 'أوزبكستان'], 'code' => 'UZ'],
            ['name' => ['en' => 'Vanuatu', 'ar' => 'فانواتو'], 'code' => 'VU'],
            ['name' => ['en' => 'Vatican City', 'ar' => 'الفاتيكان'], 'code' => 'VA'],
            ['name' => ['en' => 'Venezuela', 'ar' => 'فنزويلا'], 'code' => 'VE'],
            ['name' => ['en' => 'Vietnam', 'ar' => 'فيتنام'], 'code' => 'VN'],
            ['name' => ['en' => 'Yemen', 'ar' => 'اليمن'], 'code' => 'YE'],
            ['name' => ['en' => 'Zambia', 'ar' => 'زامبيا'], 'code' => 'ZM'],
            ['name' => ['en' => 'Zimbabwe', 'ar' => 'زيمبابوي'], 'code' => 'ZW'],
        ];
    }
}
