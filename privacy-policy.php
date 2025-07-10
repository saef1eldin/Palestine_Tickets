<?php
require_once 'includes/init.php';
require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-purple-800 mb-6"><?php echo $lang['privacy_policy'] ?? 'سياسة الخصوصية'; ?></h1>

        <div class="prose max-w-none">
            <p class="mb-4">
                <?php echo $lang['privacy_policy_intro'] ?? 'نحن في تذاكر فلسطين نقدر خصوصيتك ونلتزم بحماية بياناتك الشخصية. تشرح سياسة الخصوصية هذه كيفية جمعنا واستخدامنا وحمايتنا لمعلوماتك الشخصية عند استخدامك لموقعنا وخدماتنا.'; ?>
            </p>

            <h2 class="text-xl font-semibold text-purple-700 mt-6 mb-3">
                <?php echo $lang['information_we_collect'] ?? 'المعلومات التي نجمعها'; ?>
            </h2>
            <p class="mb-4">
                <?php echo $lang['information_we_collect_desc'] ?? 'نقوم بجمع المعلومات التالية عند استخدامك لموقعنا:'; ?>
            </p>
            <ul class="list-disc list-inside mb-4 space-y-2">
                <li><?php echo $lang['personal_info'] ?? 'معلومات شخصية: الاسم، البريد الإلكتروني، رقم الهاتف، العنوان (عند الضرورة).'; ?></li>
                <li><?php echo $lang['payment_info'] ?? 'معلومات الدفع: تفاصيل بطاقة الائتمان، معلومات الفواتير. نحن نستخدم طرق تشفير متقدمة لحماية هذه المعلومات.'; ?></li>
                <li><?php echo $lang['account_info'] ?? 'معلومات الحساب: اسم المستخدم، كلمة المرور (مشفرة)، تفضيلات الحساب.'; ?></li>
                <li><?php echo $lang['usage_info'] ?? 'معلومات الاستخدام: تفاصيل حول كيفية استخدامك للموقع، الفعاليات التي تهتم بها، سجل الشراء.'; ?></li>
                <li><?php echo $lang['technical_info'] ?? 'معلومات تقنية: عنوان IP، نوع المتصفح، نظام التشغيل، معلومات الجهاز، بيانات تسجيل الدخول.'; ?></li>
            </ul>

            <h2 class="text-xl font-semibold text-purple-700 mt-6 mb-3">
                <?php echo $lang['how_we_use_information'] ?? 'كيف نستخدم معلوماتك'; ?>
            </h2>
            <p class="mb-4">
                <?php echo $lang['how_we_use_information_desc'] ?? 'نستخدم المعلومات التي نجمعها للأغراض التالية:'; ?>
            </p>
            <ul class="list-disc list-inside mb-4 space-y-2">
                <li><?php echo $lang['process_transactions'] ?? 'معالجة المعاملات: لإتمام عمليات حجز التذاكر والدفع.'; ?></li>
                <li><?php echo $lang['provide_services'] ?? 'تقديم الخدمات: لتوفير خدماتنا وتحسينها، بما في ذلك إرسال التذاكر الإلكترونية والتأكيدات.'; ?></li>
                <li><?php echo $lang['customer_support'] ?? 'دعم العملاء: للرد على استفساراتك وتقديم المساعدة.'; ?></li>
                <li><?php echo $lang['personalization'] ?? 'التخصيص: لتخصيص تجربتك وتقديم محتوى وعروض تناسب اهتماماتك.'; ?></li>
                <li><?php echo $lang['communication'] ?? 'التواصل: لإرسال تحديثات حول الفعاليات، والعروض الخاصة، والتغييرات في سياساتنا (يمكنك إلغاء الاشتراك في أي وقت).'; ?></li>
                <li><?php echo $lang['security'] ?? 'الأمان: لحماية موقعنا وخدماتنا ومنع الاحتيال.'; ?></li>
                <li><?php echo $lang['legal_compliance'] ?? 'الامتثال القانوني: للامتثال للالتزامات القانونية والتنظيمية.'; ?></li>
            </ul>

            <h2 class="text-xl font-semibold text-purple-700 mt-6 mb-3">
                <?php echo $lang['data_security'] ?? 'أمان البيانات'; ?>
            </h2>
            <p class="mb-4">
                <?php echo $lang['data_security_desc'] ?? 'نحن نأخذ أمان بياناتك على محمل الجد ونتخذ تدابير تقنية وتنظيمية مناسبة لحماية معلوماتك الشخصية من الفقدان أو الوصول غير المصرح به أو التغيير أو الإفصاح. نستخدم تقنيات تشفير متقدمة (SSL/TLS) لحماية المعلومات الحساسة مثل تفاصيل بطاقة الائتمان أثناء النقل. نقوم بتخزين بياناتك على خوادم آمنة محمية بجدران حماية وأنظمة أمان متعددة الطبقات.'; ?>
            </p>

            <h2 class="text-xl font-semibold text-purple-700 mt-6 mb-3">
                <?php echo $lang['data_sharing'] ?? 'مشاركة البيانات'; ?>
            </h2>
            <p class="mb-4">
                <?php echo $lang['data_sharing_desc'] ?? 'قد نشارك معلوماتك الشخصية مع الأطراف التالية:'; ?>
            </p>
            <ul class="list-disc list-inside mb-4 space-y-2">
                <li><?php echo $lang['event_organizers'] ?? 'منظمي الفعاليات: لتسهيل دخولك إلى الفعاليات وإدارة قوائم الحضور.'; ?></li>
                <li><?php echo $lang['payment_processors'] ?? 'معالجي الدفع: لإتمام معاملات الدفع الخاصة بك.'; ?></li>
                <li><?php echo $lang['service_providers'] ?? 'مزودي الخدمات: الشركات التي تساعدنا في تشغيل موقعنا وتقديم خدماتنا (مثل استضافة الويب، خدمات البريد الإلكتروني، التحليلات).'; ?></li>
                <li><?php echo $lang['legal_authorities'] ?? 'السلطات القانونية: عندما يكون ذلك مطلوبًا بموجب القانون أو للامتثال للإجراءات القانونية.'; ?></li>
            </ul>
            <p class="mb-4">
                <?php echo $lang['data_sharing_note'] ?? 'نحن لا نبيع معلوماتك الشخصية لأطراف ثالثة. عندما نشارك بياناتك مع مزودي الخدمات، نضمن أنهم يلتزمون بمعايير حماية البيانات المناسبة.'; ?>
            </p>

            <h2 class="text-xl font-semibold text-purple-700 mt-6 mb-3">
                <?php echo $lang['cookies'] ?? 'ملفات تعريف الارتباط (الكوكيز)'; ?>
            </h2>
            <p class="mb-4">
                <?php echo $lang['cookies_desc'] ?? 'نستخدم ملفات تعريف الارتباط وتقنيات مماثلة لتحسين تجربتك على موقعنا. تساعدنا هذه التقنيات في تذكر تفضيلاتك، وفهم كيفية استخدامك لموقعنا، وتقديم محتوى مخصص. يمكنك التحكم في إعدادات ملفات تعريف الارتباط من خلال متصفحك، ولكن تعطيل بعض ملفات تعريف الارتباط قد يؤثر على تجربتك على موقعنا.'; ?>
            </p>

            <h2 class="text-xl font-semibold text-purple-700 mt-6 mb-3">
                <?php echo $lang['your_rights'] ?? 'حقوقك'; ?>
            </h2>
            <p class="mb-4">
                <?php echo $lang['your_rights_desc'] ?? 'اعتمادًا على موقعك، قد يكون لديك الحقوق التالية فيما يتعلق ببياناتك الشخصية:'; ?>
            </p>
            <ul class="list-disc list-inside mb-4 space-y-2">
                <li><?php echo $lang['access_right'] ?? 'الوصول: الحق في طلب نسخة من المعلومات الشخصية التي نحتفظ بها عنك.'; ?></li>
                <li><?php echo $lang['correction_right'] ?? 'التصحيح: الحق في طلب تصحيح المعلومات غير الدقيقة أو غير المكتملة.'; ?></li>
                <li><?php echo $lang['deletion_right'] ?? 'الحذف: الحق في طلب حذف معلوماتك الشخصية في ظروف معينة.'; ?></li>
                <li><?php echo $lang['restriction_right'] ?? 'تقييد المعالجة: الحق في طلب تقييد معالجة معلوماتك الشخصية في ظروف معينة.'; ?></li>
                <li><?php echo $lang['objection_right'] ?? 'الاعتراض على المعالجة: الحق في الاعتراض على معالجة معلوماتك الشخصية في ظروف معينة.'; ?></li>
                <li><?php echo $lang['portability_right'] ?? 'نقل البيانات: الحق في طلب نقل معلوماتك الشخصية إلى منظمة أخرى أو إليك.'; ?></li>
            </ul>
            <p class="mb-4">
                <?php echo $lang['exercise_rights'] ?? 'لممارسة أي من هذه الحقوق، يرجى الاتصال بنا باستخدام معلومات الاتصال المقدمة أدناه.'; ?>
            </p>

            <h2 class="text-xl font-semibold text-purple-700 mt-6 mb-3">
                <?php echo $lang['data_retention'] ?? 'الاحتفاظ بالبيانات'; ?>
            </h2>
            <p class="mb-4">
                <?php echo $lang['data_retention_desc'] ?? 'نحتفظ بمعلوماتك الشخصية طالما كان ذلك ضروريًا للأغراض المذكورة في سياسة الخصوصية هذه، أو للامتثال للالتزامات القانونية، أو لحل النزاعات، أو لإنفاذ اتفاقياتنا. عندما لم تعد هناك حاجة للاحتفاظ بمعلوماتك الشخصية، سنقوم بحذفها أو إخفاء هويتها بشكل آمن.'; ?>
            </p>

            <h2 class="text-xl font-semibold text-purple-700 mt-6 mb-3">
                <?php echo $lang['children_privacy'] ?? 'خصوصية الأطفال'; ?>
            </h2>
            <p class="mb-4">
                <?php echo $lang['children_privacy_desc'] ?? 'موقعنا غير موجه للأطفال دون سن 16 عامًا، ولا نجمع عن علم معلومات شخصية من الأطفال دون هذا السن. إذا كنت تعتقد أننا قد جمعنا معلومات من طفل دون سن 16 عامًا، يرجى الاتصال بنا وسنتخذ الخطوات المناسبة لإزالة هذه المعلومات.'; ?>
            </p>

            <h2 class="text-xl font-semibold text-purple-700 mt-6 mb-3">
                <?php echo $lang['policy_changes'] ?? 'التغييرات في سياسة الخصوصية'; ?>
            </h2>
            <p class="mb-4">
                <?php echo $lang['policy_changes_desc'] ?? 'قد نقوم بتحديث سياسة الخصوصية هذه من وقت لآخر لتعكس التغييرات في ممارساتنا أو لأسباب تشغيلية أو قانونية أو تنظيمية أخرى. سنقوم بإخطارك بأي تغييرات جوهرية من خلال إشعار واضح على موقعنا أو عن طريق الاتصال المباشر. نشجعك على مراجعة سياسة الخصوصية هذه بشكل دوري للبقاء على اطلاع بكيفية حمايتنا لمعلوماتك الشخصية.'; ?>
            </p>

            <h2 class="text-xl font-semibold text-purple-700 mt-6 mb-3">
                <?php echo $lang['contact_us'] ?? 'اتصل بنا'; ?>
            </h2>
            <p class="mb-4">
                <?php echo $lang['privacy_contact_desc'] ?? 'إذا كان لديك أي أسئلة أو مخاوف بشأن سياسة الخصوصية هذه أو ممارسات الخصوصية لدينا، يرجى الاتصال بنا على:'; ?>
            </p>
            <ul class="list-none mb-4 space-y-2">
                <li><strong><?php echo $lang['email'] ?? 'البريد الإلكتروني'; ?>:</strong> privacy@tickets-palestine.com</li>
                <li><strong><?php echo $lang['phone'] ?? 'الهاتف'; ?>:</strong> 0501234567</li>
                <li><strong><?php echo $lang['address'] ?? 'العنوان'; ?>:</strong> <?php echo $lang['company_address'] ?? 'شارع الرئيسي 123، غزة، فلسطين'; ?></li>
            </ul>

            <p class="mt-8 text-sm text-gray-600">
                <?php echo $lang['last_updated'] ?? 'آخر تحديث'; ?>: 25/4/2024
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
