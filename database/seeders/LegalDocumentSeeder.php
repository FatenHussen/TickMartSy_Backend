<?php

namespace Database\Seeders;

use App\Models\LegalDocument;
use Illuminate\Database\Seeder;
use App\Models\Package;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $englishText = <<<'TEXT'
        Privacy Policy
        
        The privacy policy adopted by Online Tikmool outlines how we process personal data, payment data, and other information collected from users, suppliers, third parties, or provided directly through www.tikmool.com and the tikmool mobile application. We understand the importance of this data and commit to protecting, respecting, and preserving your privacy. Please read the following carefully. By using our services, you agree that your data may be handled in accordance with this policy.
        
        Data we collect:
        
        - Information provided when filling out forms during account registration, social logins, subscriptions, content publication, or additional service requests.
        - Device-specific data when you install or use the tikmool app, such as location details and your device ID.
        - Technical information about your computer or device (IP address, operating system, browser type).
        - Inputs submitted via surveys, votes, reviews, testimonials, or feedback messages.
        - Details provided when reporting issues encountered on the site or app.
        - Correspondence logs when you contact us through "Contact Us".
        - Non-personal and demographic data.
        - Transaction data for orders placed on the site/app, including fulfilment and delivery records.
        - Email and/or mobile number shared by third parties who confirmed you consented to sharing.
        - Any other data necessary to enhance your experience with our site or app.
        
        Why we use your data:
        
        - To deliver requested information, products, or services and reach out with content you consented to.
        - To offer location-based services (e.g., ads, personalized search content).
        - To fulfil obligations under any contract between you and another entity using our site, or between you and us.
        - To improve and personalize our services.
        - To present our site optimally for your device.
        - To notify you about site changes.
        - For any other valid reason that enhances your browsing experience.
        - To manage incentive programs, promotional events, competition entries, and notify winners.
        
        Data disclosure:
        
        We treat customer data with the utmost confidentiality. Sharing occurs only under the following limited circumstances and in compliance with applicable laws:
        
        - Third-party service and logistic partners: We cooperate with entities (e.g., logistics providers, marketing agencies) to fulfil orders. They receive only the data required to deliver the order and process cash-on-delivery collections.
        
        Note on payment: Tikmool sells physical goods delivered to homes, and payment is cash-on-delivery only. We do not collect, store, or process credit card or electronic payment details within the app.
        
        - Marketing and promotional initiatives: We may use your data to improve your experience and send information about relevant goods/services, service notices, new features, special offers, and events through channels such as email, web push, in-app messages, WhatsApp, phone calls, or social media.
        - Third-party advertising: We may provide data to advertisers to help them reach target audiences while honouring our commitments.
        - Contest or promotional participation may require additional data to verify eligibility. That data may be collected by us or by the sponsoring third party, with reminders to review their privacy policy.
        - Acquisition, sale, or transfer of business assets may include customer data as part of the assets transferred to new owners, following applicable law.
        - Site/app protection: We disclose data to comply with legal requirements, assist investigations, protect rights/property, and safeguard users, including sharing with companies or authorities to prevent fraud, security risks, and credit-related threats.
        
        Links to external sites may lead to other privacy policies for which we are not responsible; review those policies before sharing personal information.
        
        - Customer care and logistics partners: We may share your contact details with these partners when contracted or affiliated to provide or facilitate service, resolve issues, and ensure a smooth experience.
        
        Legal basis for processing:
        
        We process your information when a legal basis exists under the laws of the countries where we operate.
        
        Data processing and usage:
        
        - Provide the quality service you requested.
        - Track service delivery and ensure receipt.
        - Enable invoicing and payment collection.
        - Communicate with customers to collect feedback, handle complaints, or answer inquiries.
        - Inform you about updates, improvements, or new services and regional coverage.
        - Use reviews or comments in marketing (only first name and city displayed).
        - Personalize recommended products/services based on behavior and interests.
        - Fulfill legal obligations in the countries where we operate.
        
        Data storage:
        
        Data is stored when necessary under our policy and applicable laws. Our teams process it for orders, billing, support, and it may be used by affiliates or partners for logistics, technical support, or related services. Retention periods comply with local regulations and may support analysis and market studies.
        
        Security controls:
        
        We apply administrative, technical, logistical, and commercial safeguards, including encryption and access restrictions, to protect data from unauthorized access, alteration, deletion, or disclosure. You remain responsible for securing your credentials, especially on shared devices, while we implement protections on our servers.
        
        Access and correction:
        
        Customers can access and update personal details, preferences, addresses, and orders. We may keep historical copies for compliance purposes.
        
        Privacy policy updates:
        
        We may update the policy as our operations evolve. The latest version is published on the site/app and becomes effective upon publication. Continued use after updates constitutes acceptance. We may also notify you via email, WhatsApp, Telegram, or other channels.
        
        Account deletion:
        
        You may request deletion of your account and all related data by emailing tikmool.cs@gmail.com (subject: "Account Deletion Request") or contacting support via the app's listed numbers. Requests are processed within 14 business days.
        
        Contact us:
        
        Reach us through site/app contact details or email tikmool.cs@gmail.com for inquiries, complaints, feedback, or data requests.
        TEXT;

        $arabicText = <<<'TEXT'
        سياسة الخصوصية
        
        ان سياسة الخصوصية التي نعتمدها شركة اونلاين تيك مول هي القواعد التي سنقوم بموجبها بالتعامل مع أي بيانات شخصية، بيانات الدفع، وغيرها من البيانات الأخرى التي نجمعها من المستخدمين أو الموردين أو من مصادر آخرى أو تلك التي تقدمها إلينا وذلك عند استخدامك لموقع www.tikmool.com و/او تطبيق tikmool للجوال، ونحن ندرك أهمية هذه البيانات، ونلتزم بحماية خصوصيتك واحترامها والحفاظ عليها. يُرجى قراءة ما يلي جيداً لفهم سياستنا فيما يتعلق بالبيانات. باستخدامك لخدماتنا فإنك توافق على التعامل مع البيانات بما يتفق مع سياسة الخصوصية هذه.
        
        البيانات التي نقوم بجمعها:
        
        · البيانات التي تزودنا بها عند تعبئة النماذج والحقول في الموقع (تسجيل الحساب)، بما في ذلك البيانات التي زودتنا بها عند التسجيل لاستخدام الموقع والتسجيلات الأخرى (مثل دخول مواقع التواصل الاجتماعي المختلفة) أو الاشتراك في الخدمات التي نقدمها أو نشر المواد أو طلب خدمات أخرى.
        
        · في حال تحميل أو استخدام تطبيق tikmool على هاتفك الجوال، فقد نتمكن من الوصول إلى تفاصيل تتعلق بموقعك وموقع جوالك، بما في ذلك ID الخاص بجهازك.
        
        · تفاصيل حول جهاز الكمبيوتر الذي تستخدمه، كعنوان بروتوكول الإنترنت IP الخاص بك ونظام التشغيل ونوع المتصفح.
        
        · البيانات التي تقدمها عند دخول استطلاع للرأي أو عمليات تصويت أو تقييمات أو شهادات أو ملاحظات.
        
        · البيانات التي تقدمها لنا، أو التي قد نجمعها منك، عندما تبلغنا عن اي مشاكل او صعوبات تواجهها عند استخدام موقعنا او تطبيقنا.
        
        · جمع سجل المراسلات في حال تواصلك معنا (اتصل بنا).
        
        · جمع بيانات عامة وديموغرافية وغير شخصية.
        
        · تفاصيل المعاملات التي قمت بإجرائها عبر موقعنا وتطبيقنا وتفاصيل قيامنا بتجهيز وتسليم البضائع التي طلبتها.
        
        · عنوان بريدك الالكتروني و/أو رقم الجوال الذي تم تزويدنا به من قبل الغير الذين اكدوا لنا استحصالهم على موافقتك على مشاركة عنوان بريدك الالكتروني.
        
        · أي بيانات أخرى نعتبرها ضرورية لتعزيز تجربتك في استخدام الموقع والتطبيق.
        
        لماذا نستخدم بياناتك:
        
        · لتزويدك بالمعلومات أو المنتجات أو الخدمات التي تطلبها منا أو نرى أنك قد تكون مهتماً بها، وحيث قمت بالموافقة على أن يتم الاتصال بك لمثل هذه الأغراض.
        
        · لتزويدك بالخدمات المعتمدة على المكان الذي توجد فيه، مثل الإعلان ونتائج البحث ومحتويات أخرى مخصصة لك.
        
        · لتنفيذ الالتزامات الناجمة عن أي عقود مبرمة بينك وبين أي جهة أخرى تستخدم موقعنا، أو بينك وبيننا.
        
        · لتحسين خدماتنا ولتقديم خدمات أفضل ومخصصة بدرجة أكبر.
        
        · لضمان تقديم محتوى موقعنا بأكثر الطرق فاعلية لك وللجهاز الذي تستخدمه للوصول إلى موقعنا.
        
        · لإخطارك بالتغييرات التي يجريها موقعنا.
        
        · لأي سبب آخر نراه ضرورياً لتعزيز تجربة تصفحك للموقع.
        
        · لإدارة البرامج التحفيزية والإعلانية وتلبية طلباتك للحصول على هذه الحوافز، و/أو للسماح لك بالمشاركة بالمسابقات وإخطارك في حالة الفوز.
        
        كشف ومشاركة بيانات العميل:
        
        تعتبر البيانات الخاصة بعملائنا ذات خصوصية عالية ومحمية وجزءاً مهماً جداً من عملنا. ولذلك فإن عملية مشاركة بياناتك ليست بالأمر السهل وغير متاحة إلا حسب ما هو مذكور أدناه فقط وبالحد الأدنى المسموح حسب القوانين والتشريعات المعمول بها حيث اننا ملتزمون بالقوانين الموضوعة حسب كل بلد نعمل فيه وحسب التعليمات الواردة في سياسة الخصوصية:
        
        · الطرف الثالث (الوسيط الخدمي واللوجستي): من أجل العمل على توفير خدماتنا، نتمكن من التعاون مع شركات أخرى تابعة لنا أو غير تابعين لنا (على سبيل المثال: الشركات اللوجستية لتوصيل المنتجات إليك، وكالات وشركات التسويق). يتم تزويد هؤلاء الشركاء بالبيانات الضرورية فقط لإتمام عملية التوصيل وتحصيل قيمة الطلبات نقداً عند الاستلام (Cash on Delivery).
        
        ملاحظة هامة حول الدفع: تطبيق Tikmool مخصص لبيع المنتجات المادية وتوصيلها للمنازل، وعملية الدفع تتم حصراً بشكل نقدي عند الاستلام. نحن لا نقوم بجمع، تخزين، أو معالجة أي بيانات تتعلق ببطاقات الائتمان أو وسائل الدفع الإلكتروني داخل التطبيق.
        
        · عمليات التسويق والعروض الترويجية: قد نعمل على استخدام البيانات لتعزيز وتطوير تجربتك في استخدام الموقع والتطبيق ولتزويدك بمعلومات عن السلع والخدمات التي قد تكون مهتماً بها، ورسائل الخدمة، والمزايا الجديدة والتحسينات والعروض الخاصة والفعاليات ذات الأهمية. ومن الممكن التواصل معك عبر وسائل مختلفة، بما في ذلك على سبيل المثال لا الحصر البريد الإلكتروني وإشعارات الويب والمنشورات والهاتف ورسائل التطبيق ورسائل الواتساب ومواقع التواصل الاجتماعي.
        
        · التسويق عبر طرف ثالث باستخدام البيانات: قد نقدم البيانات إلى المعلنين لمساعدتهم في الوصول إلى الجمهور المستهدف لنتمكن من تنفيذ التزامنا مع الجهات المعلنة (عبر عرض إعلاناتهم على الجمهور المستهدف).
        
        · المشاركة في الفعاليات والترويج والمسابقات قد تتطلب بيانات إضافية للتحقق من أهليتك، وقد يتم جمع هذه المعلومات من قبلنا أو الطرف الراعي مع تذكيرك بمراجعة سياسة الخصوصية الخاصة بالطرف الثالث لاطلاعك على استخدامها للبيانات.
        
        · الاستحواذ أو البيع أو التنازل: في حال تم بيع أو التنازل أو الاستحواذ على شركتنا أو جميع الأصول التابعة لنا، فستكون بيانات العملاء أحد الأصول التي يتم تحويلها للجهة أو المالك الجديد حسب القانون المتبع في الدولة التي نعمل بها.
        
        · حماية الموقع والتطبيق: نُفصح عن البيانات عند الامتثال للقوانين والتشريعات، وأي تحقيقات قانونية، لحماية الحقوق والممتلكات، وسلامة المستخدمين، بما في ذلك تبادل البيانات مع الشركات والهيئات الأخرى لمنع الاحتيال ومخاطر الأمن وبيانات الائتمان.
        
        يرجى الانتباه إلى أن بعض روابط المنتجات أو الإعلانات قد تقودك إلى مواقع أخرى تمتلك سياسات خصوصية مستقلة؛ نحن لسنا مسؤولين عن سياسات تلك المواقع، لذا تأكد من مراجعته قبل مشاركة أي بيانات.
        
        · خدمة العملاء وشركات الدعم اللوجستي: قد نُشارك معلومات التواصل الخاصة بك مع هذه الشركات عند التعاقد معها أو كونها تابعة لنا لتقديم الخدمة، حل المشكلات، وضمان تجربة تسوق مميزة.
        
        أساس قانوني لمعالجة البيانات:
        
        نقوم بمعالجة بياناتك طالما كان ذلك ضرورياً وفق التشريعات المعتمدة في الدول التي نعمل بها.
        
        معالجة البيانات واستخدامها:
        
        · توفير الخدمة المطلوبة بالجودة الممكنة.
        · متابعة تقديم الخدمة وضمان استلامها.
        · تمكيننا من تحصيل الفواتير وقيمها النقدية.
        · التواصل مع العملاء واستبيان آرائهم حول الخدمات والمنتجات.
        · الرد على الملاحظات أو الشكاوى أو الاستفسارات ومتابعتها.
        · إعلامك بالتحديثات أو التحسينات على الموقع والتطبيق أو تقديم خدمات جديدة أو تغطية مناطق جديدة.
        · استخدام التعليقات أو التقييمات في الحملات التسويقية مع الإشارة إلى الاسم الأول والمدينة فقط.
        · تخصيص المحتوى والعروض بناءً على سلوكك واهتماماتك.
        · الالتزام القانوني تجاه القوانين التي تلزمنا باستخدام بياناتك امتثالاً للتشريعات.
        
        تخزين البيانات:
        
        نحتفظ بالبيانات عندما يكون ذلك ضرورياً وفق السياسة والقوانين المحلية. تتم معالجتها من قبل فريقنا لتلبية الطلبات، الفواتير، الدعم، وقد يستخدمها شركاؤنا لأغراض الدعم اللوجستي أو الفني.
        نحتفظ بالبيانات لفترات تتوافق مع القوانين وقد تفيد في التحليل الإحصائي ودراسة السوق.
        
        معايير الحماية:
        
        نتخذ إجراءات إدارية وتقنية ولوجستية وتجارية لحماية البيانات من الوصول غير المصرح أو التعديل أو الحذف. نستخدم التشفير ونطبق ضوابط وصول صارمة، كما نؤكد على مسؤوليتك في حماية كلمات المرور الخاصة بحسابك، خصوصاً في الأجهزة المشتركة.
        
        إمكانية الوصول والتعديل:
        
        يمكن للعملاء الاطلاع على بيانات الحساب، التفضيلات، العناوين، وطلبات الشراء، وتحديثها عند الحاجة. قد نحتفظ بنسخ سابقة للامتثال للمتطلبات القانونية.
        
        تعديل سياسة الخصوصية:
        
        قد يحدث تغيير في سياسة الخصوصية مع تطوير خدماتنا. يتم نشر النسخة الأخيرة على الموقع والتطبيق، ويعد استمرارك في الاستخدام بعد التعديل موافقة ضمنية.
        قد نبلغ العملاء بالتعديلات عبر البريد، واتساب، تيليجرام، أو الوسائط الأخرى، ويُفضل مراجعة السياسة باستمرار.
        
        إمكانية حذف الحساب والبيانات:
        
        نحترم خصوصيتك، ويمكنك طلب حذف حسابك وجميع البيانات المرتبطة به عن طريق:
        
        - إرسال طلب إلى البريد الإلكتروني: tikmool.cs@gmail.com (يرجى كتابة "طلب حذف حساب" في الموضوع).
        - التواصل مع الدعم الفني عبر الأرقام الواردة في التطبيق. سنعالج الطلب خلال 14 يوم عمل على الأكثر.
        
        تواصل معنا:
        
        يمكن للعملاء التواصل معنا عبر أرقام الموقع، التطبيق، أو البريد الإلكتروني tikmool.cs@gmail.com لأي استفسارات، شكاوى، ملاحظات، أو طلبات بيانات.
        TEXT;

        $marketerTermsEn = <<<'TEXT'
        Marketer Terms & Conditions
        
        This document explains the rules and commitments that apply to any individual or entity that promotes products or services on behalf of Tikmool. By participating in our marketer programs you agree to:
        
        - Register with valid commercial information, including proof of authorization if you act on behalf of a company.
        - Provide accurate contact and tax details, and update them whenever they change.
        - Only promote products, campaigns, or services that Tikmool has explicitly approved.
        - Respect trademark, copyright, and advertising guidelines, and avoid misleading or prohibited content.
        - Follow Tikmool instructions about pricing, offers, messaging, and promotional mechanics.
        - Use only official channels or links we provide for campaigns and avoid redirecting users outside the platform without prior consent.
        - Protect any confidential information or marketing collateral shared with you.
        - Notify Tikmool immediately if you suspect any fraudulent activity or breach related to your marketing account.
        - Comply with all applicable laws regarding marketing, data privacy, electronic messaging, and contest administration.
        - Cease marketing activities if your status is suspended, your account is cancelled, or you no longer meet the eligibility requirements.
        
        Tikmool reserves the right to suspend, terminate, or update marketer eligibility, commissions, or access at any time. Compensation is subject to proper tracking and verification. Failure to comply may result in removal from the marketer program and withholding of rewards.
        TEXT;

        $marketerTermsAr = <<<'TEXT'
        شروط وأحكام المسوقين
        
        تشرح هذه الوثيقة القواعد والالتزامات التي تنطبق على كل فرد أو جهة تروّج لمنتجات أو خدمات تيك مول. من خلال مشاركتك في برامج المسوقين، فإنك توافق على ما يلي:
        
        · التسجيل بمعلومات تجارية صحيحة، بما في ذلك دليل التفويض في حال كنت تمثل شركة.
        · تزويدنا ببيانات اتصال وضرائب دقيقة وتحديثها عند أي تغيير.
        · الترويج فقط للمنتجات أو الحملات أو الخدمات التي وافقت عليها تيك مول صراحةً.
        · الالتزام بحقوق العلامات التجارية والملكية وتفادي المحتوى المضلل أو المحظور.
        · اتباع تعليمات تيك مول الخاصة بالتسعير، والعروض، والرسائل، وآليات الترويج.
        · استخدام القنوات الرسمية أو الروابط التي نُقدمها فقط ولا تقم بتحويل العملاء خارج المنصة بدون موافقة مسبقة.
        · حماية أي معلومات أو مواد تسويقية سرية تم مشاركتها معك.
        · إبلاغ تيك مول فوراً في حال لاحظت أي نشاط احتيالي أو خرق متعلق بحسابك التسويقي.
        · الالتزام بجميع القوانين المعمول بها في مجال التسويق، وخصوصية البيانات، والرسائل الإلكترونية، وإدارة المسابقات.
        · التوقف عن أي نشاط تسويقي إذا تم تعليق حسابك، أو إلغاؤه، أو إذا لم تعد تستوفي شروط الأهلية.
        
        تيك مول تحتفظ بالحق في تعليق أو إنهاء أو تعديل أهلية المسوقين أو شروط العمولات أو الوصول في أي وقت. تُصرف التعويضات بعد التحقق من الأداء الصحيح. الإخلال قد يؤدي إلى الإزالة من البرنامج وحجز المكافآت.
        TEXT;

        $termsOfUseEn = <<<'TEXT'
        Terms of Use
        Last updated: 13/06/2025
        
        Welcome to our website www.tikmool.com and our mobile application tikmool.app. We are pleased to introduce the terms of use for each. These terms include all policies, procedures, and protocols governing your use of our services via the website and mobile application.
        
        Your use or registration in our services constitutes your agreement to comply with the terms of use stated in this document. These terms may be amended and updated by us at any time. Your use of our website and application after publishing such amendments or updates constitutes implicit acceptance of the modified or updated terms of use.
        
        About the Site
        
        This website is an e-commerce platform that allows business institutions to display and sell products, and allows companies and individuals to purchase a variety of products.
        
        We reserve the right to introduce new services, update any of the services, or withdraw them at our sole discretion without any liability.
        
        v Registration
        
        1- You may register as a buyer or seller and benefit from the services if you meet the qualifying conditions, including:
        
        1) Buyers:
        
        - You must be of legal age to purchase products in your country of residence.
        - You must be able to provide a fixed and valid address in your country of residence for product delivery.
        
        2) Sellers:
        
        - You must have a registered and valid commercial registration in accordance with the laws of the country where you conduct your business.
        - Provide proof of authorization for individuals who register on the site or use it.
        - Proof of identity for the authorized person.
        - Provide supporting banking details.
        - Agree that additional requirements may apply for certain product categories.
        
        2- To register on the site, we will need certain information. Your registration will not be accepted if the required information is not provided. We have the right to reject any registration without stating reasons. We also have the right to conduct the necessary verification procedures to confirm your identity and registration requirements.
        
        3- Upon successful registration, your registration continues for an indefinite period, subject to suspension or cancellation according to the terms set out in these Terms of Use.
        
        v Your Obligations
        
        Once you access or register for the services, you agree to the following:
        
        1. You are responsible for maintaining the privacy and restricting access to your account and password, and you agree to be responsible for all activities that occur under your account and password.
        2. Notify us immediately of any unauthorized use of your password or account or any other breach of secure site use.
        3. Provide complete, true, accurate, and current information about yourself and your use of the services as specified by us.
        4. Do not provide any user information provided to you by us to any third party, regardless of their status (except where required or as specified by us).
        5. Agree and cooperate if information is requested to verify your eligibility or your use of the site and our services.
        6. Refrain from uploading or downloading materials and content that violate laws, regulations, legislation, ethics, values, and public morals.
        7. Refrain from uploading, downloading, or publishing prohibited and inappropriate content or materials, whether morally or religiously inappropriate or threatening to security and public safety in any form.
        8. Refrain from publishing or uploading any content related to securities (shares, bonds, checks, etc.) and content that may promote or fall under gambling.
        9. Refrain from publishing or uploading any content related to weapons, tobacco, drugs, sedatives, intoxicants, medical drugs, and chemical materials.
        10. Refrain from publishing or uploading any false, misleading, stolen, or harmful content or materials when used normally, or defaming others.
        11. Do not publish what you are not entitled to share (links, content, images, videos, etc.).
        12. Do not break or circumvent the law, infringe on the rights of others, or violate our systems, policies, and protocols related to your account.
        13. Refrain from using our services if you no longer meet the eligibility requirements or are unable to legally comply, or if your account has been suspended or canceled.
        14. Commit to delivering the products you sold unless a legal reason prevents you from doing so as stated in our policies.
        15. Commit to paying the price of the products you purchased unless a legal reason prevents you from doing so as stated in our policies.
        16. Refrain from manipulating or monopolizing product prices.
        17. Commit to not taking any action that lowers the site's rating or ranking.
        18. Refrain from using any contact information we provided to conclude a deal outside our site or through other sites to increase your sales.
        19. Do not transfer or sell your account to a third party without our written consent.
        20. Commit to not publishing unsolicited or harmful emails or messages, or viruses or technologies that harm our services and users.
        21. Finally, refrain from violating laws related to copyright, trademark, patent, ethics, advertising, database, or any intellectual property rights related to us, licensed to us, or related to others. Do not collect any private user information without their consent or circumvent any technical measures we use to provide services.
        
        v Intellectual Property Rights
        
        In addition to all rights expressly granted under these Terms of Use, intellectual property rights include the following:
        
        - All site contents are our property or the property of our providers, including but not limited to content, icons, texts, graphics, logos, images, audio clips, digital products, and software. We (or the providers, as applicable) retain all our rights, ownership, and interest in the site, services, and all intellectual property rights included in these Terms of Use.
        - All rights, ownership, and interests in any information, materials, or other content you provide through your use of the services, in addition to all your intellectual property rights mentioned in these Terms of Use, will become our property.
        - You may not use our trademarks without prior written consent.
        - All rights not expressly granted to you under these Terms of Use are reserved for us or our providers.
        
        v Warranties and Undertakings
        
        By using our services, you agree and undertake the following:
        
        1- You are the owner and authorized to grant rights and licenses to us under these Terms of Use.
        2- You have full authority to contract under these Terms of Use, and your fulfillment of your obligations does not conflict with any laws, regulations, or governmental rules to which you are subject, or any other agreements you are bound by.
        3- If you create or use an account on behalf of a company, you are authorized to act on its behalf and guarantee its compliance with these Terms of Use. We consider this account to belong to the company and under its full management.
        4- You commit to laws and regulations related to privacy and content organization, as well as all applicable laws, systems, and regulations.
        5- You will not violate the rights of others anywhere in the world (e.g., intellectual property rights whether registered or not) and will not provide content or use services that conflict with or infringe the rights of others.
        6- We provide you with services and products as-is without warranties, undertakings, or representations, and we disclaim all warranties, undertakings, and representations of all kinds, express and implied. This includes, by way of example, warranties or undertakings regarding the suitability or fitness of content for commercial purposes or for any specific or general purpose, non-infringement, or that the services are secure, error-free, uninterrupted, or provided in a timely manner. Nevertheless, we use our best efforts and accuracy in selecting products and providers to match the described quality. Therefore, we do not guarantee that product specifications or any content for any service are accurate, complete, reliable, or free of defects or errors. As a buyer, you agree that we are not responsible for inspecting or testing services provided by us or by others. As for sellers, you are responsible for reviewing the accuracy of content in your products and services and must not obstruct our efforts to provide accurate information and correct content.
        
        v Liability and Indemnities
        
        1- Nothing in these Terms of Use authorizes or permits limiting or excluding liability of any party for:
        
        - Fraud, including deceit committed by that party.
        - Causing death or personal injury due to that party's negligence.
        - Any other liabilities that cannot be limited or excluded under applicable law.
        
        2- Our company, partners, or employees, regardless of their position or nature of work, shall not be liable, whether based on a claim in contract, tort, negligence, or breach of law or these Terms of Use, for any loss of profits, loss of data or information, business interruption, financial loss, or direct, indirect, or incidental damages, even if we have been notified of the possibility of such damages.
        
        3- You decide that you will not hold us liable for any damage or loss arising directly or indirectly from any of the following:
        
        - Your use or inability to use the services.
        - Delay or interruption in providing the services.
        - Information and content we provide when you use the services.
        - Coordination, shipping, pricing, and any instructions provided by us.
        - Any error or malfunction in the services in any form.
        - Malfunction or damage to your device when using products and services sold through the site.
        - Any content or act resulting from others' use of our services.
        - Malware or viruses, if any, while attempting to access or use the services.
        - Any action or suspension we take related to your use of our services.
        - The time it takes for your listings to appear in search results or how they appear.
        - Modification of content or activity, or your loss or inability to conduct business due to any change in these Terms of Use.
        
        4- If any of the above cannot be applied for any reason, our total obligations, including our company, employees, partners, and suppliers, whether resulting from any claim or demand in contract, negligence, breach of legal duty, or otherwise arising from these Terms of Use, shall be limited to the minimum of:
        
        - The price of the product sold on the site and shipping costs.
        - The disputed fees amount, not exceeding the total fees paid to us during the 12 months preceding the action that led to liability.
        
        5- You must indemnify and hold us harmless, including our company, employees, partners, and suppliers, whether as a result of any claim or demand in contract, negligence, breach of legal duty, or otherwise, against any losses, expenses, or damages, including all legal fees and attorney fees, in the following cases:
        
        - Third-party claims and demands resulting from your use of our services.
        - Violation or breach of any of these Terms of Use in all its details.
        - Violation or breach of any applicable laws or policies, including data protection laws or anti-spam laws.
        - Infringement of third-party intellectual property rights through your content or what you publish, and the products you list on the site, or if your content includes defamation, slander, or violation of any third-party rights or privacy.
        
        v Suspension and Ban
        
        We at Tikmool have the right to suspend, ban, or restrict your use of the services, cancel your product orders, or delete and hide your content at our sole discretion and judgment, without prejudice to any rights or compensation and without any liability to you. Any amounts paid and received by us related to a canceled product order will be refunded.
        
        v Reporting Violations of Terms of Use
        
        If there is content that does not comply with the Terms of Use, we ask users and customers to report it, and we will verify the matter. We remain committed to ensuring that products and content on the site comply with these Terms of Use.
        
        v General Provisions
        
        1- Applicable law: These Terms of Use and any related non-contractual rights or obligations shall be governed by and interpreted under the laws applicable in the countries where we operate.
        
        2- Dispute resolution: If there are issues with our services, please contact us. We will resolve all problems you face as soon as possible. Note and confirm that any dispute or disagreement related to these Terms of Use will be settled through the courts of the countries in which we operate.
        
        3- Third-party rights: A person who is not a party to these Terms of Use has no right to enforce any of its terms.
        
        4- Relationship between parties: All parties to the agreement are independent parties.
        
        5- Assignment of rights and obligations: These Terms of Use ensure that you may not assign or transfer any of your rights or obligations under these Terms of Use, directly or indirectly, without our prior written consent as a condition precedent.
        
        6- Agreement: These Terms of Use and the documents referred to or incorporated in the Terms of Use represent the entire agreement between the parties regarding the subject of the agreement.
        
        7- Amendment of Terms of Use: No one other than us has the right to make any amendment (addition, deletion, modification) to the Terms of Use. We reserve the right to introduce any amendment to these Terms of Use at any time. We will publish the final version of the Terms of Use on the site, and it will be effective upon publication on the site or as of the effective date we determine. Your continued use of the services in the event of any changes constitutes your acceptance to comply with the amended Terms of Use.
        
        8- Cancellation of a term: If any term of these Terms of Use is canceled by competent courts or deemed unlawful, that term shall be canceled and the remaining terms and provisions shall remain in effect, provided it does not affect transactions conducted under them.
        
        9- Force majeure: No party shall be responsible for disruption, loss, damage, delay, or failure to perform due to force majeure circumstances beyond the control of any party, including but not limited to (acts of God and measures issued by legislative, judicial, or regulatory authorities of any federal or local government, or any third party supplier of goods or services to us, labor disturbances, complete power outages, or economic boycotts).
        
        10- Waiver of Terms of Use: Waiver of any term of these Terms of Use does not constitute waiver of other similar or dissimilar terms, and it is not continuous unless expressly stated in writing.
        
        11- Contact: You can contact us through "Contact Us" or via phone numbers or email.
        
        12- Survival of terms and conditions: All provisions stated to be effective, or that by their nature remain effective after termination, shall remain in effect after termination or suspension of your membership in the site.
        TEXT;

        $termsOfUseAr = <<<'TEXT'
        شروط الاستخدام
        
        التحديث الاخير 13/06/2025
        
        نرحب بكم في موقعنا الالكتروني www.tikmool.com وتطبيقنا الالكتروني tikmool.app ويسعدنا تعريفكم بشروط استخدامكم لكل منها. وتضم هذا الشروط جميع السياسات والاجراءات والبروتوكولات الخاصة باستخدامكن لخدماتنا عبر الموقع الالكتروني وتطبيق الجوال.
        
        ان استخدامك او تسجيلك في خدماتنا يعتبر موافقة من قبلك على الالتزام بشروط الاستخدام المذكورة في هذه الوثيقة. وهذه الشروط قابلة للتعديل والتحديث من قبلنا في أي وقت واستخدامك لموقعنا وتطبيقنا بعد نشر هذه التعديلات او التحديثات هو موافقة ضمنية من قبلك على شروط الاستخدام التي تم تعديلها او تحديثها.
        
        نُبذة عن الموقع
        
        يعد هذا الموقع منصة للتجارة الإلكترونية التي تتيح للمستخدمين من المؤسسات التجارية عرض وبيع المنتجات، كما يتيح للشركات والأفراد شراء مجموعة متنوعة من المنتجات.
        
        نحتفظ بحق تقديم خدمات جديدة وتحديث أي من الخدمات أو سحبها، وفقاً لتقديرنا الخاص دون اية مسؤولية.
        
        v      شروط التسجيل
        
        1-      يحق لك التسجيل كمشترٍ أو بائع والاستفادة من الخدمات إذا توفرت لديك الشروط التي تؤهلك ومنها:
        
        1)      المشترين:
        
        ·         أن تكون بالغاً السن القانونية لتتمكن من شراء المنتجات في بلد إقامتك.
        
        ·         أن تكون قادراً على تقديم عنوان ثابت وحقيقي في بلد اقامتك لتسليم المنتجات لك؟
        
        2)      البائعين:
        
        ·         أن يكون لديك سجل تجاري مسجل وساري المفعول وفق القوانين الخاصة بالدولة التي تمارس نشاطك التجاري فيها.
        
        ·         تقديم ما يثبت تفويض الأفراد الذين يقومون بالتسجيل في الموقع أو باستخدامه.
        
        ·         إثبات الهوية للشخص المفوض.
        
        ·         تقديم بيانات مصرفية داعمة.
        
        ·         الموافقة على أنه قد تنطبق بعض المتطلبات الإضافية لبعض الفئات من المنتجات.
        
        2-      للتسجيل في الموقع، سنحتاج إلى تقديم بعض المعلومات، ولن يتم قبول تسجيلك في الموقع إذا لم يتم تقديم المعلومات اللازمة لنا. لدينا الحق في رفض أي من عمليات التسجيل دون إبداء الأسباب. كما يحق لنا أيضاً القيام بعمليات التحقق اللازمة للتأكد من هويتك ومتطلبات التسجيل.
        
        3-      عند الانتهاء من التسجيل بنجاح، يستمر التسجيل الخاص بك لفترة غير محددة خاضعاً لاحتمال تعليقه أو إلغائه وفقاً للبنود المذكورة في شروط الاستخدام هذه .
        
        v      الالتزامات المتوجبة عليك:
        
        بمجرد وصولك للخدمات او تسجيلك فيها فانت توافق على ما يلي:
        
        1.       انت مسؤول عن الحفاظ على الخصوصية وتقييد الوصول إلى حسابك الخاص واستخدامه هو وكلمة المرور، والموافقة على تحمل مسؤولية جميع الأنشطة التي تتم باسم الحساب الخاص بك وكلمة المرور الخاصة بك.
        
        2.       ابلاغنا فورا عن أي استخدام غير مصرح به لكلمة المرور أو الحساب الخاص بك أو أي خرق آخر لمعايير الاستخدام الآمن للموقع.
        
        3.       تقديم المعلومات الكاملة والحقيقية والدقيقة والحالية عن نفسك وعن استخدامك للخدمات كما هو محدد من قبلنا.
        
        4.       عدم تقديم اي من معلومات المستخدم المقدمة لك من قبلنا للغير مهما كانت صفته ( يستثنى من ذلك ما يلزم او ما هو محدد من قبلنا ) .
        
        5.       الموافقة والتعاون في حال طلب معلومات للتحقق من اهليتك او استخدامك للموقع وخدماتنا.
        
        6.       الامتناع عن رفع او تحميل المواد والمحتوى المخالف للانظمة والقوانين والتشريعات والاخلاق والقيم والاداب العامة.
        
        7.       الامتناع عن رفع وتحميل ونشر المحتويات او المواد المحظورة وغير المناسبة سواء كان اخلاقيا او دينيا او تهدد الامن والسلم باي صيغة وشكل مهما كان.
        
        8.       الامتناع عن نشر ورفع اي محتوى يخص الاوراق المالية ( اسهم، صكوك، شيكات، وغيرها ) والمحتوى الذي قد يروج للمقامرة او يندرج تحتها.
        
        9.       الامتناع عن نشر ورفع اي محتوى يخص الاسلحة والتبغ والمخدرات والمنومات والمسكرات والادوية الطبية والمواد الكيميائية.
        
        10.   الامتناع عن نشر ورفع اي محتوى او مواد زائفة او مضللة او مسروقة او تسبب ضرر عند استخدامها بشكل طبيعي، او التشهير بالغير.
        
        11.   عدم نشر ما لا يحق لك مشاركته ( روابط، محتوى، صور، فيديو وغيرها )
        
        12.   عدم خرق القانون او التحايل عليه او الاعتداء على حقوق الغير وخرقها، وعد خرق الانظمة والسياسات والبروتوكولات الخاصة بنا والمتعلقة بحسابك الخاص.
        
        13.   الامتناع عن استخدام خدماتنا في حال اصبحت غير محقق لشروط اهليتك لاستخدام خدماتنا او كنت غير قادر على الالتزام قانونيا او تم ايقاف او الغاء حسابك لدينا
        
        14.   الالتزام بتسليم المنتجات التي تم بيعها من قبلك الا اذا وجد سبب قانوني يمنعك من ذلك وتم ذكره في سياساتنا.
        
        15.   الالتزام بتسديد ثمن المنتجات التي قمت بشرائها الا اذا وجد سبب قانوني يمنعك من ذلك وتم ذكره في سياساتنا.
        
        16.   الامتناع عن التلاعب باسعار المنتجات او احتكارها.
        
        17.   الالتزام بعد القيام باي اجراء او عمل يقلل من تقييم الموقع او تصنيفه.
        
        18.   الامتناع عن استخدام اي من معلومات الاتصال التي قمنا بتزويدك بها لعقد صفقة عبر الموقع لمحاولة زيادة مبيعاتك خارج موقعنا او عبر مواقع اخرى.
        
        19.   عدم نقل او بيع الحساب الخاص بك لطرف ثالث دون موافقة خطية من قبلنا.
        
        20.   الالتزام بعدم نشر الرسائل والمراسلات الالكترونية غير المرغوب بها او الصارة او اي فيروسات او تقنيات تضر بخدماتنا وبالمستخدمين.
        
        21.   الامتناع النهائي عن خرق القوانين الخاصة بحقوق النشر، العلامة التجارية، براءة الاختراع، الأخلاق، الإعلان، قاعدة البيانات، أو أي من حقوق الملكية الفكرية التي تتعلق بنا أو المرخصة لنا او التي تتعلق بالغير. وجمع اي معلومات خاصة بالمستخدمين دون موافقتهم. أو التحايل على أي من الإجراءات التقنية التي نتبعها لتقديم الخدمات.
        
        v      حقوق الملكية الفكرية:
        
        بالاضافة الى كل الحقوق التي تم منحها صراحة وفقاً لشروط الاستخدام هذه تشمل حقوق الملكية ما يلي:
        
        ·         ان كل محتويات الموقع تدخل ضمن ملكيتنا الخاصة أو ضمن الملكية الخاصة بمزودينا، وتشمل على سبيل المثال لا الحصر المحتوى والايقونات والنصوص والرسوم البيانية والشعارات والصور والمقاطع الصوتية والمنتجات الرقمية والبرمجيات. فنحن (أو المزودين، وفقاً لما تقتضيه الحالة) نحتفظ بجميع حقوقنا، وملكيتنا ومصلحتنا بالموقع والخدمات وبجميع حقوق الملكية الفكرية الواردة ضمن شروط الاستخدام هذه.
        
        ·         جميع الحقوق والملكيات والمصالح لأي من المعلومات أو المواد أو المحتويات الأخرى التي تقدمها أنت من خلال استخدامك للخدمات بالإضافة الى جميع حقوق الملكية الفكرية الخاصة بك والواردة ضمن شروط الاستخدام هذه ستصبح ملكاً لنا.
        
        ·         لا يحق لك استخدام العلامات التجارية الخاصة بنا دون وجود موافقة خطية مسبقة.
        
        ·         إن جميع الحقوق غير المخولة لك بشكل واضح في شروط الاستخدام المماثلة يتم الاحتفاظ بها لنا أو للمزودين لنا.
        
        v      الضمانات والتعهدات:
        
        بمجرد استخدامك لخدماتنا فانت توافق وتتعهد بما يلي:
        
        1-      انت مالك ومخول لمنح الحقوق والتراخيص لنا وفق شروط الاستخدام هذه.
        
        2-      لديك سلطة كاملة للتعاقد وفق شروط الاستخدام هذه وتنفيذك لالتزاماتك هذه لا يتعارض مع اي من القوانين والتشريعات واللوائح الحكومية التي تخضع لها. او مع اي اتفاقيات اخرى انت ملتزم بها.
        
        3-      في حال انشاء او استخدام حساب بالنيابة عن شركة فانت مخول بالتصرف نيابة عنها وتضمن التزامها بالعمل وفق شروط الاستخدام هذه ونعتبر ان هذا الحساب ملك للشركة ورهن ادارتها الكاملة.
        
        4-      الالتزام بالتشريعات والقوانين المتعلقة بالخصوصية وتنظيم المحتوى وكذلك الالتزام بكافة القوانين والانظمة واللوائح المطبقة.
        
        5-      عدم انتهاك حقوق الغير في اي مكان في العالم ( مثال حقوق الملكية الفكرية سواء كانت مسجلة ام لا ) وعدم تقديم محتوى او استخدام للخدمات يتعارض او ينتهك حقوق الغير.
        
        6-      نحن نقدم لك الخدمات والمنتجات على حالتها الحالية دون ضمانات او تعهدات او اقرارات ونخلي مسؤوليتنا عن جميع الضمانات والتعهدات والاقرارات بكل اشكالها الصريحة والضمنية وكمثال جميع الضمانات او التعهدات الخاصة بصلاحية او ملائمة المحتوى للاغراض التجارية او لغرض محدد او عام او عدم مخالفته لاي حقوق او كون الخدمات امنة وخالية من العيوب او تعمل بدون اعطال او سيتم تقديمها في الوقت المناسب او على العموم . ومع ذلك نحن نعمل بكل جهد ونتوخى الدقة لاختيار المنتجات ومزودي المنتجات لتطابق الوصف والجودة المرجوة. ولذلك لا نضمن ان تكون مواصفات المنتج او اي محتوى لاي خدمة دقيقاً وكاملاً او موثوقا به او خالي من العيوب والاخطاء، وعليه وبما انك كمشتري فأنت توافق على اننا غير مسؤولين عن فحص واختبار الخدمات المقدمة من قبلنا او من غيرنا. وبالنسبة للبائعين فأنتم مسؤولين عن مراجعة دقة المحتوى الوارد ضمن منتجاتكم وخدماتكم ولا تحاولون اعاقة عملنا بما يخص توخي الدقة في تقديم المعلومات والمحتوى الصحيح.
        
        v      المسؤولية والتعويضات
        
        1-      لا تتضمن سياسة الاستخدام هذه اي بند او تخويل او سماح يعفي او يحد من المسؤولية لكل الاطراف عن:
        
        ·         عمليات الاحتيال، بما فيها التدليس والذي يقوم به هذا الطرف.
        
        ·         التسبب بالوفاة أو الإصابة الشخصية نتيجة لإهمال هذا الطرف.
        
        ·         جميع المسؤوليات الأخرى التي لا يمكن حدها أو الإعفاء منها وفقاً للقانون المعمول به.
        
        2-      لا تتحمل شركتنا او شركائنا او موظفينا مهما كان مركزهم وطبيعة عملهم سواء بناءً على دعوى او مطالبة في العقد او ضرر او اهمال او خرق للقانون او شروط الاستخدام هذه المسؤولية عن اي خسارة بالارباح او فقد البيانات او المعلومات او عطل في العمل او خسارة مالية او اضرار مباشرة او غير مباشرة او عرضية  حتى وان تم اخطارنا باحتمال وجود هذه الاضرار.
        
        3-      انت تقرر بأنك لن تحملنا مسؤولية حدوث اي ضرر او فقدان ناتج بشكل مباشر او غير مباشر عن كل مما يلي:
        
        ·         استخدامك او عدم قدرتك على استخدام الخدمات.
        
        ·         التأخر أو الانقطاع عن تقديم الخدمات
        
        ·         المعلومات والمحتوى الذي نقدمه عند استخدامك للخدمات.
        
        ·         التنسيق والشحن والتسعير واي تعليمات مقدمة من قبلنا
        
        ·         وجود اي خطأ أو عطل في الخدمات باي شكل من الاشكال.
        
        ·         عطل او تلف الجهاز الخاص بك عند استخدام المنتجات والخدمات التي يتم بيعها عن طريق الموقع.
        
        ·         اي محتوى او فعل ناتج عن استخدام الغير لخدماتنا.
        
        ·         البرامج الضارة او الفيروسات ان وجدت اثناء محاولة الوصول او استخدام خدماتنا.
        
        ·         اي اجراء او تعليق نتخذه يتعلق باستخدامك للخدماتنا.
        
        ·         المدة الزمنية لظهور قوائمك الخاصة في نتائج البحث او شكل ظهورها.
        
        ·         تعديل المحتوى او النشاط او خسارتك او عدم القدرة على القيام بالاعمال التجارية نتيجة وجود اي تغيير في شروط الاستخدام هذه.
        
        4-      في حال عدم امكانية تطبيق اي بند مما سبق لاي سبب من الاسباب تقتصر التزاماتنا الكلية بما في ذلك شركتنا وموظفينا وشركائنا وموردينا سواء كان ذلك نتيجة لأي دعوى او مطالبة في العقد او اهمال او خرق لاي واجب قانوني او غيره ناتجة عن شروط الاستخدام هذه، لتكون بحد أقصى القيمة الدنيا لـ:
        
        ·         سعر المنتج المباع على الموقع وتكاليف الشحن.
        
        ·         مبلغ الرسوم المتنازع عليها على الا تتجاوز قيمتها اجمالي قيمة الرسوم التي تم تسديدها الينا خلال فترة 12 شهر التي تسبق الاجراء الذي ادى الى المسؤولية.
        
        5-      عليك الالتزام بتعويضنا وابراء ذمتنا بما في ذلك شركتنا وموظفينا وشركائنا وموردينا سواء كان ذلك نتيجة لأي دعوى او مطالبة في العقد او اهمال او خرق لاي واجب قانوني او غيره وضد اي خسائر او نفقات او اضرار بما في ذلك كافة الرسوم القانونية واتعاب المحامين وغيرها من النفقات المتعلقة بذلك، في الحالات التالية:
        
        ·         ادعاءات ومطالبات الغير نتيجة استخدامك لخدماتنا.
        
        ·         مخالفة وخرق اي من شروط واحكام شروط الاستخدام هذه بكافة تفاصيلها.
        
        ·         خرق وانتهاك اي من القوانين والسياسات المعمول بها بما في ذلك قوانين حماية البيانات أو قوانين مكافحة البريد الإلكتروني العشوائي.
        
        ·         انتهاك حقوق الملكية الفكرية للغير من خلال محتواك او ما تقوم بنشره والمنتجات التي تقوم بادراجها في الموقع او اذا كان المحتوى الخاص بك فيه تعدي او قذف او افتراء او انتهاك لاي من حقوق الغير او خصوصيتهم.
        
        v      التوقيف والحظر:
        
        يحق لنا في تيك موول توقيف او حظر او الحد استخدامك للخدمات او الغاء طلباتك للمنتجات او حذف واخفاء المحتوى الخاص بك وفقا لتقديرنا ورؤيتنا الخاصة. مع عدم المساس بأيٍّ من الحقوق أو التعويضات أو دون أي مسؤولية تجاهك، وسيتم رد أي مبالغ تم دفعها واستلامها من قبلنا فيما يتعلق بطلب منتجات تم إلغائه.
        
        v      الإبلاغ عن انتهاك شروط الاستخدام:
        
        في حال وجود محتوى لا يتناسب مع شروط الاستخدام نرجو المستخدمين والعملاء لتبليغنا بذلك وسنقوم بالتحقق من الأمر. مع التزامنا بامتثال المنتجات والمحتوى الوارد في الموقع لشروط الاستخدام هذه.
        
        v      أحكام عامة:
        
        1-      القانون المطبق: إن شروط الاستخدام هذه وأياً من الحقوق أو الواجبات غير التعاقدية ذات الصلة يجب إخضاعها وتفسيرها للقوانين المطبقة في الدول التي نعمل بها.
        
        2-      حل النزاعات: في حال وجود مشاكل خاصة بخدماتنا يرجى الاتصال بنا‎. وسنعمل على حل جميع المشاكل التي تواجهك في أقرب وقت ممكن. مع التنويه والتأكيد الى انه سيتم تسوية اي نزاع او خلاف متعلق بشروط الاستخدام هذه عن طريق محاكم الدول التي نعمل بها.
        
        3-      حقوق الغير: الشخص الذي لا يُعد جزءاً من شروط الاستخدام هذه ليس لديه أي حق في تنفيذ أي من شروطها.
        
        4-      العلاقة بين الأطراف:  أن كل الأطراف في الاتفاق هي أطراف مستقلة .
        
        5-      التنازل عن الحقوق والواجبات:  تضمن شروط الاستخدام هذه عدم التنازل او نقل اي من شروط الاستخدام او الحقوق او الواجبات الخاصة بك سواء بشكل مباشر او غير مباشر دون الحصول على موافقة خطية من قبلنا كشرط مسبق.
        
        6-      الاتفاق:  إن شروط الاستخدام هذه والوثائق المشار إليها أو المدرجة في شروط الاستخدام تمثل مجمل الاتفاق بين الأطراف فيما يتعلق بموضوع الاتفاقية.
        
        7-      تعديل شروط الاستخدام:  ليس من حق أحد غيرنا إجراء أي تعديل على شروط الاستخدام ( اضافة، حذف، تعديل ) . نحن نحتفظ بالحق في إدخال أي تعديل بما يخص شروط الاستخدام هذه في أي وقت . وسنقوم بنشر النسخة النهائية لشروط الاستخدام على الموقع وستكون سارية المفعول عند نشرها على الموقع أو حسب تاريخ السريان الذي نحدده . إن استخدامك المستمر للخدمات في حال حدوث أي تغييرات يعد موافقة منك على الالتزام بشروط الاستخدام المعدلة.
        
        8-      الغاء احد الشروط:  في حال تم الغاء اي شرط من شروط الاستخدام هذه من قبل المحاكم المختصة او اعتبر انه غير قانوني فيتم الغاء هذا الشرط وتستمر باقي الشروط والاحكام سارية المفعول بشرط عدم تاثيرها على الصفقات التي تمت تحت مظلتها.
        
        9-      الظروف والقوة القاهرة:  لا يتحمل أي طرف مسؤولية عن التعطل أو الخسارة أو الضرر أو التأخير أو عدم الوفاء نتيجة للظروف القاهرة الخارجة عن السيطرة لأي من الأطراف  وكمثال لا للحصر ( القضاء والقدر والإجراءات الصادرة عن السلطات التشريعية أو القضائية أو التنظيمية لأي من الحكومة الفيدرالية أو المحلية أو السلطات القضائية أو أي طرف ثالث مورد البضائع أو الخدمات لنا أو الاضطرابات العمالية أو الانقطاع الكامل للتيار الكهربائي أو المقاطعة الاقتصادية).
        
        10-   التنازل عن شروط الاستخدام:  إن التنازل عن شرط من شروط الاستخدام لا يعني بالمطلق تنازلا عن باقي الشروط المشابهة او غير المشابهة ولا يعتبر مستمرا اذا لم نذكر ذلك خطيا وبشكل صريح.
        
        11-   التواصل:  يمكنك التواصل معنا عن طريق اتصل بنا او عن طريق ارقام الهاتف او البريد الالكتروني.
        
        12-   سريان الاحكام والشروط:  ان جميع الأحكام التي ينص على أنها سارية أو التي تسري بطبيعتها بعد إنهاء التعاقد تبقى سارية المفعول بعد إنهاء أو تعليق عضويتك في الموقع.
        TEXT;

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
                'en' => 'Terms of Use',
                'ar' => 'شروط الاستخدام',
            ],
            'content' => [
                'en' => $termsOfUseEn,
                'ar' => $termsOfUseAr,
            ],
        ]);

        LegalDocument::create([
            'key' => 'marketer_terms_conditions',
            'title' => [
                'en' => 'Marketer Terms & Conditions',
                'ar' => 'شروط وأحكام المسوقين',
            ],
            'content' => [
                'en' => $marketerTermsEn,
                'ar' => $marketerTermsAr,
            ],
        ]);
    }
}
