<?php
/**
 * وظيفة لترجمة النصوص الثابتة في الصفحات
 *
 * هذه الوظيفة تقوم بتحويل النصوص العربية الثابتة إلى اللغة المختارة
 * يتم استخدامها في الصفحات التي تحتوي على نصوص ثابتة غير مترجمة
 */

// قاموس الترجمة للنصوص الثابتة
$static_translations = [
    // طرق الدفع
    'طرق الدفع المسجلة' => [
        'he' => 'אמצעי תשלום רשומים',
        'en' => 'Registered Payment Methods'
    ],
    'إضافة طريقة دفع' => [
        'he' => 'הוסף אמצעי תשלום',
        'en' => 'Add Payment Method'
    ],
    // العنوان الرئيسي في الصفحة الرئيسية
    'احجز تذاكرك الآن' => [
        'he' => 'הזמן כרטיסים עכשיו',
        'en' => 'Book Your Tickets Now'
    ],
    'احجز تذاكرك لأفضل العروض الموسيقية والمسرحية والثقافية بسهولة وأمان تام' => [
        'he' => 'הזמן כרטיסים למיטב המופעים המוסיקליים, התיאטרון והתרבות בקלות ובבטיחות מלאה',
        'en' => 'Book your tickets for the best music, theater and cultural shows with ease and complete security'
    ],
    'تذاكر الحفلات' => [
        'he' => 'כרטיסי הופעות',
        'en' => 'Concert Tickets'
    ],
    'كيف تعمل الخدمة؟' => [
        'he' => 'איך זה עובד?',
        'en' => 'How does it work?'
    ],
    // الصفحة الرئيسية
    'استعرض الفعاليات' => [
        'he' => 'סקור אירועים',
        'en' => 'Browse Events'
    ],
    'احجز تذكرتك' => [
        'he' => 'הזמן כרטיס',
        'en' => 'Book Your Ticket'
    ],
    'استلم تذكرتك' => [
        'he' => 'קבל את הכרטיס שלך',
        'en' => 'Receive Your Ticket'
    ],
    'البحث عن الفعاليات' => [
        'he' => 'חיפוש אירועים',
        'en' => 'Search for Events'
    ],
    'اختيار المقاعد' => [
        'he' => 'בחירת מושבים',
        'en' => 'Choose Seats'
    ],
    'إتمام عملية الدفع' => [
        'he' => 'השלמת התשלום',
        'en' => 'Complete Payment'
    ],
    'تصفح بسهولة واكتشف أحدث الفعاليات والعروض المميزة حسب التاريخ أو الموقع أو النوع، واعثر على ما يناسب ذوقك واهتماماتك.' => [
        'he' => 'עיין בקלות וגלה את האירועים והמופעים האחרונים לפי תאריך, מיקום או סוג, ומצא את מה שמתאים לטעם ולתחומי העניין שלך.',
        'en' => 'Browse easily and discover the latest events and featured shows by date, location, or type, and find what suits your taste and interests.'
    ],
    'اختر أفضل المقاعد المتاحة من خلال مخطط القاعة التفاعلي واستمتع بأفضل رؤية وتجربة مميزة للعرض.' => [
        'he' => 'בחר את המושבים הטובים ביותר הזמינים דרך תרשים האולם האינטראקטיבי ותיהנה מהתצוגה והחוויה הטובים ביותר למופע.',
        'en' => 'Choose the best available seats through the interactive hall chart and enjoy the best view and a distinctive experience for the show.'
    ],
    'أتمم عملية الدفع بسهولة وأمان من خلال بوابة دفع آمنة بنسبة 100٪ واحصل على تذاكرك فوراً على بريدك الإلكتروني.' => [
        'he' => 'השלם את התשלום בקלות ובבטחה דרך שער תשלום מאובטח ב-100% וקבל את הכרטיסים שלך מיד לדואר האלקטרוני שלך.',
        'en' => 'Complete the payment easily and securely through a 100% secure payment gateway and get your tickets immediately to your email.'
    ],
    'أحمد محمود' => [
        'he' => 'אחמד מחמוד',
        'en' => 'Ahmed Mahmoud'
    ],
    'سارة خالد' => [
        'he' => 'שרה חאלד',
        'en' => 'Sarah Khaled'
    ],
    'محمد عبدالله' => [
        'he' => 'מוחמד עבדאללה',
        'en' => 'Mohammed Abdullah'
    ],
    '"تجربة استثنائية! حجزت تذاكر لحفلة محمد عساف وكانت العملية سلسة وسريعة للغاية. الموقع سهل الاستخدام والتذاكر وصلت فوراً. سأستخدم هذه المنصة دائماً لحجز تذاكري!"' => [
        'he' => '"חוויה יוצאת דופן! הזמנתי כרטיסים להופעה של מוחמד עסאף והתהליך היה חלק ומהיר מאוד. האתר קל לשימוש והכרטיסים הגיעו מיד. אשתמש בפלטפורמה זו תמיד להזמנת הכרטיסים שלי!"',
        'en' => '"Exceptional experience! I booked tickets for Mohammed Assaf\'s concert and the process was smooth and very fast. The site is easy to use and the tickets arrived immediately. I will always use this platform to book my tickets!"'
    ],
    '"منصة رائعة لحجز التذاكر! استطعت حجز تذاكر لمسرحية الملك لير بسعر مناسب جداً. الموقع يوفر خيارات متنوعة من الفعاليات وخدمة العملاء ممتازة وسريعة الاستجابة!"' => [
        'he' => '"פלטפורמה נהדרת להזמנת כרטיסים! הצלחתי להזמין כרטיסים להצגה המלך ליר במחיר מתאים מאוד. האתר מציע מגוון אפשרויות של אירועים ושירות לקוחות מצוין ומהיר תגובה!"',
        'en' => '"Great platform for booking tickets! I was able to book tickets for King Lear play at a very reasonable price. The site offers a variety of events and excellent, fast-responding customer service!"'
    ],
    '"تجربة لا تُصدق! حجزت تذاكر لمعرض الفن التشكيلي لعائلتي بأكملها واستفدنا من عرض خاص للمجموعات. الموقع سهل الاستخدام والتذاكر وصلت فوراً مع تعليمات واضحة للحضور."' => [
        'he' => '"חוויה לא תיאמן! הזמנתי כרטיסים לתערוכת האמנות הפלסטית לכל משפחתי ונהנינו מהצעה מיוחדת לקבוצות. האתר קל לשימוש והכרטיסים הגיעו מיד עם הוראות ברורות להגעה."',
        'en' => '"Incredible experience! I booked tickets for the art exhibition for my entire family and we benefited from a special offer for groups. The site is easy to use and the tickets arrived immediately with clear instructions for attendance."'
    ],
    'اشترك في نشرتنا البريدية واحصل على خصم 10٪' => [
        'he' => 'הירשם לניוזלטר שלנו וקבל 10% הנחה',
        'en' => 'Subscribe to our newsletter and get a 10% discount'
    ],
    'كن أول من يعلم بالفعاليات الجديدة والعروض الحصرية والخصومات الخاصة لمشتركي النشرة البريدية' => [
        'he' => 'היה הראשון לדעת על אירועים חדשים, הצעות בלעדיות והנחות מיוחדות למנויי הניוזלטר',
        'en' => 'Be the first to know about new events, exclusive offers, and special discounts for newsletter subscribers'
    ],
    'أدخل بريدك الإلكتروني' => [
        'he' => 'הזן את הדוא"ל שלך',
        'en' => 'Enter your email'
    ],
    'اشترك' => [
        'he' => 'הירשם',
        'en' => 'Subscribe'
    ],
    'تفاصيل الفعالية' => [
        'he' => 'פרטי האירוע',
        'en' => 'Event Details'
    ],
    'عرض جميع الفعاليات' => [
        'he' => 'הצג את כל האירועים',
        'en' => 'View All Events'
    ],
    'قريباً' => [
        'he' => 'בקרוב',
        'en' => 'Coming Soon'
    ],
    // إضافات جديدة لصفحة الإندكس
    'الفعاليات القادمة' => [
        'he' => 'אירועים קרובים',
        'en' => 'Upcoming Events'
    ],
    'كيفية حجز التذاكر' => [
        'he' => 'איך להזמין כרטיסים',
        'en' => 'How to Book Tickets'
    ],
    'البحث عن الفعاليات' => [
        'he' => 'חיפוש אירועים',
        'en' => 'Search for Events'
    ],
    'اختيار المقاعد' => [
        'he' => 'בחירת מושבים',
        'en' => 'Choose Seats'
    ],
    'إتمام عملية الدفع' => [
        'he' => 'השלמת התשלום',
        'en' => 'Complete Payment'
    ],
    'آراء عملائنا' => [
        'he' => 'חוות דעת לקוחות',
        'en' => 'Customer Reviews'
    ],
    'اشترك في نشرتنا البريدية واحصل على خصم 10٪' => [
        'he' => 'הירשם לניוזלטר שלנו וקבל 10% הנחה',
        'en' => 'Subscribe to our newsletter and get a 10% discount'
    ],
    // المراجعات في صفحة الإندكس
    '"موقع ممتاز لحجز التذاكر! واجهة المستخدم سهلة وبسيطة، وعملية الدفع آمنة وسريعة. أحب كيف يمكنني تصفية الفعاليات حسب التاريخ والموقع. سأستخدم هذا الموقع دائماً لحجز تذاكري!"' => [
        'he' => '"אתר מצוין להזמנת כרטיסים! ממשק המשתמש קל ופשוט, ותהליך התשלום מאובטח ומהיר. אני אוהב את האפשרות לסנן אירועים לפי תאריך ומיקום. אשתמש באתר זה תמיד להזמנת הכרטיסים שלי!"',
        'en' => '"Excellent site for booking tickets! The user interface is easy and simple, and the payment process is secure and fast. I love how I can filter events by date and location. I will always use this site to book my tickets!"'
    ],
    '"أنا سعيد جداً بالأسعار التنافسية على هذا الموقع. لقد وفرت الكثير مقارنة بمنصات التذاكر الأخرى. خدمة العملاء ممتازة وسريعة الاستجابة، والموقع يوفر خيارات متنوعة من الفعاليات."' => [
        'he' => '"אני מאוד מרוצה מהמחירים התחרותיים באתר זה. חסכתי הרבה בהשוואה לפלטפורמות כרטיסים אחרות. שירות הלקוחות מצוין ומהיר תגובה, והאתר מציע מגוון אפשרויות של אירועים."',
        'en' => '"I am very happy with the competitive prices on this site. I have saved a lot compared to other ticket platforms. Customer service is excellent and fast-responding, and the site offers a variety of events."'
    ],
    '"أحب العروض الخاصة للمجموعات التي يقدمها هذا الموقع. التطبيق المحمول سهل الاستخدام ويمكنني عرض تذاكري الرقمية بسهولة عند الدخول. التعليمات واضحة والدعم الفني ممتاز."' => [
        'he' => '"אני אוהב את ההצעות המיוחדות לקבוצות שהאתר הזה מציע. האפליקציה הניידת קלה לשימוש ואני יכול להציג את הכרטיסים הדיגיטליים שלי בקלות בכניסה. ההוראות ברורות והתמיכה הטכנית מצוינת."',
        'en' => '"I love the special group offers that this site provides. The mobile app is easy to use and I can easily display my digital tickets at entry. The instructions are clear and the technical support is excellent."'
    ],
    // أسماء العملاء في صفحة الإندكس
    'موشيه كوهين' => [
        'he' => 'מושה כהן',
        'en' => 'Moshe Cohen'
    ],
    'روت ليفي' => [
        'he' => 'רות לוי',
        'en' => 'Ruth Levi'
    ],
    'دافيد شارون' => [
        'he' => 'דוד שרון',
        'en' => 'David Sharon'
    ],
    // نصوص وصفية في صفحة الإندكس
    'تصفح بسهولة واكتشف أحدث الفعاليات والعروض المميزة حسب التاريخ أو الموقع أو النوع، واعثر على ما يناسب ذوقك واهتماماتك.' => [
        'he' => 'עיין בקלות וגלה את האירועים והמופעים האחרונים לפי תאריך, מיקום או סוג, ומצא את מה שמתאים לטעם ולתחומי העניין שלך.',
        'en' => 'Browse easily and discover the latest events and featured shows by date, location, or type, and find what suits your taste and interests.'
    ],
    'اختر أفضل المقاعد المتاحة من خلال مخطط القاعة التفاعلي واستمتع بأفضل رؤية وتجربة مميزة للعرض.' => [
        'he' => 'בחר את המושבים הטובים ביותר הזמינים דרך תרשים האולם האינטראקטיבי ותיהנה מהתצוגה והחוויה הטובים ביותר למופע.',
        'en' => 'Choose the best available seats through the interactive hall chart and enjoy the best view and a distinctive experience for the show.'
    ],
    'أتمم عملية الدفع بسهولة وأمان من خلال بوابة دفع آمنة بنسبة 100٪ واحصل على تذاكرك فوراً على بريدك الإلكتروني.' => [
        'he' => 'השלם את התשלום בקלות ובבטחה דרך שער תשלום מאובטח ב-100% וקבל את הכרטיסים שלך מיד לדואר האלקטרוני שלך.',
        'en' => 'Complete the payment easily and securely through a 100% secure payment gateway and get your tickets immediately to your email.'
    ],
    // نصوص قسم كيف يعمل
    'ايك زه عوبد؟' => [
        'he' => 'איך זה עובד?',
        'en' => 'How does it work?'
    ],
    'كبل ات هكرطيس شلخ' => [
        'he' => 'קבל את הכרטיס שלך',
        'en' => 'Receive Your Ticket'
    ],
    'هزمن كرطيسيم' => [
        'he' => 'הזמן כרטיסים',
        'en' => 'Book Tickets'
    ],
    'سكور ايروعيم' => [
        'he' => 'סקור אירועים',
        'en' => 'Browse Events'
    ],
    'استلم بريدات الكترونية عن طريق موقعنا واحصل على قسيمة للدخول السريع للفعالية.' => [
        'he' => 'קבל הודעות דואר אלקטרוני דרך האתר שלנו וקבל שובר לכניסה מהירה לאירוע.',
        'en' => 'Receive electronic mail through our site and get a voucher for quick entry to the event.'
    ],
    'احجز تذاكرك الالكترونية عبر موقعنا وادفع بسهولة وامان من خلال نظام دفع مبسط.' => [
        'he' => 'הזמן את הכרטיסים שלך באופן מקוון דרך האתר שלנו ושלם בקלות ובבטחה דרך מערכת תשלום פשוטה.',
        'en' => 'Book your electronic tickets through our website and pay easily and securely through a simplified payment system.'
    ],
    'استعرض مجموعة متنوعة من الفعاليات والمسرحيات والمعارض الفنية والفعاليات الثقافية المميزة.' => [
        'he' => 'סקור מגוון של אירועים, הצגות, תערוכות אמנות ואירועי תרבות ייחודיים.',
        'en' => 'Browse a variety of events, plays, art exhibitions and distinctive cultural events.'
    ],
    // نصوص إضافية لقسم كيف يعمل
    'استلم تذاكرك الإلكترونية فوراً على بريدك الإلكتروني واحفظها على هاتفك للدخول السريع إلى الفعالية.' => [
        'he' => 'קבל את הכרטיסים האלקטרוניים שלך מיד לדואר האלקטרוני שלך ושמור אותם על הטלפון שלך לכניסה מהירה לאירוע.',
        'en' => 'Receive your electronic tickets immediately to your email and save them on your phone for quick entry to the event.'
    ],
    'اختر الفعالية المفضلة لديك واحجز تذاكرك بسهولة وسرعة من خلال نظام حجز آمن ومبسط.' => [
        'he' => 'בחר את האירוע המועדף עליך והזמן את הכרטיסים שלך בקלות ובמהירות דרך מערכת הזמנות מאובטחת ופשוטה.',
        'en' => 'Choose your favorite event and book your tickets easily and quickly through a secure and simplified booking system.'
    ],
    'استعرض مجموعة متنوعة من الحفلات الموسيقية والمسرحيات والمعارض الفنية والفعاليات الثقافية المميزة.' => [
        'he' => 'סקור מגוון של הופעות מוסיקה, הצגות, תערוכות אמנות ואירועי תרבות ייחודיים.',
        'en' => 'Browse a variety of music concerts, plays, art exhibitions and distinctive cultural events.'
    ],
];

/**
 * وظيفة لترجمة النص الثابت
 *
 * @param string $text النص المراد ترجمته
 * @param string $lang اللغة المطلوبة
 * @return string النص المترجم
 */
function translate_static_text($text, $lang = 'ar') {
    global $static_translations;

    // إذا كانت اللغة هي العربية، أرجع النص كما هو
    if ($lang == 'ar') {
        return $text;
    }

    // إذا كان النص موجود في قاموس الترجمة واللغة المطلوبة متوفرة
    if (isset($static_translations[$text]) && isset($static_translations[$text][$lang])) {
        return $static_translations[$text][$lang];
    }

    // إذا لم يتم العثور على ترجمة، أرجع النص الأصلي
    return $text;
}

/**
 * وظيفة لترجمة محتوى HTML كامل
 *
 * @param string $html محتوى HTML المراد ترجمته
 * @param string $lang اللغة المطلوبة
 * @return string محتوى HTML المترجم
 */
function translate_html_content($html, $lang = 'ar') {
    global $static_translations;

    // إذا كانت اللغة هي العربية، أرجع المحتوى كما هو
    if ($lang == 'ar') {
        return $html;
    }

    // استبدال كل النصوص الثابتة في المحتوى
    foreach ($static_translations as $arabic => $translations) {
        if (isset($translations[$lang])) {
            $html = str_replace($arabic, $translations[$lang], $html);
        }
    }

    return $html;
}

/**
 * وظيفة لبدء تخزين مؤقت للمخرجات
 */
function start_translation_buffer() {
    // تعطيل مؤقت لحل مشكلة العرض
    // ob_start();
}

/**
 * وظيفة لإنهاء التخزين المؤقت وترجمة المحتوى
 *
 * @param string $lang اللغة المطلوبة
 * @return string المحتوى المترجم
 */
function end_translation_buffer($lang = 'ar') {
    // تعطيل مؤقت لحل مشكلة العرض
    // $content = ob_get_clean();
    // return translate_html_content($content, $lang);
    return '';
}
