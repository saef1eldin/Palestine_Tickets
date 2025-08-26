<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

$auth = new Auth();

if(!$auth->isLoggedIn()) {
    redirect('login.php');
}

if(!isset($_GET['event_id'])) {
    redirect('events.php');
}

$event_id = $_GET['event_id'];
$event = get_event_by_id($event_id);

if(!$event) {
    redirect('events.php');
}

// التحقق من وجود حجز مواصلات
$with_transport = isset($_GET['with_transport']) && $_GET['with_transport'] == '1';
$transport_booking = null;
$transport_amount = 0;
$ticket_amount = 0;
$has_event_ticket = false;
$checkout_message = '';

if ($with_transport && isset($_SESSION['booking_data'])) {
    $transport_booking = $_SESSION['booking_data'];
    $transport_amount = $transport_booking['transport_amount'] ?? 0;
    $ticket_amount = $transport_booking['ticket_amount'] ?? 0;
    $has_event_ticket = $transport_booking['has_event_ticket'] ?? false;

    // تحديد رسالة الدفع حسب الحالة
    if ($has_event_ticket) {
        $checkout_message = "لديك تذكرة صالحة للحدث. ستدفع رسوم المواصلات فقط.";
    } else if ($ticket_amount > 0) {
        $checkout_message = "ستحتاج لشراء تذكرة الحدث مع المواصلات.";
    } else {
        $checkout_message = "الحدث مجاني. ستدفع رسوم المواصلات فقط.";
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="event-details.php?id=<?php echo $event_id; ?>" class="inline-flex items-center text-purple-600 hover:text-purple-800 transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            <span><?php echo $lang['back_to_event'] ?? 'العودة إلى الفعالية'; ?></span>
        </a>
    </div>
    <?php if ($with_transport && !empty($checkout_message)): ?>
    <!-- رسالة توضيحية للمواصلات -->
    <div class="mb-6 p-4 rounded-lg border-l-4 <?php echo $has_event_ticket ? 'bg-green-50 border-green-400' : ($ticket_amount > 0 ? 'bg-blue-50 border-blue-400' : 'bg-yellow-50 border-yellow-400'); ?>">
        <div class="flex items-center">
            <i class="fas <?php echo $has_event_ticket ? 'fa-check-circle text-green-600' : ($ticket_amount > 0 ? 'fa-info-circle text-blue-600' : 'fa-exclamation-triangle text-yellow-600'); ?> text-xl ml-3"></i>
            <div>
                <h3 class="font-medium <?php echo $has_event_ticket ? 'text-green-800' : ($ticket_amount > 0 ? 'text-blue-800' : 'text-yellow-800'); ?>">
                    <?php echo $has_event_ticket ? 'تذكرة موجودة' : ($ticket_amount > 0 ? 'شراء تذكرة ومواصلات' : 'حدث مجاني'); ?>
                </h3>
                <p class="<?php echo $has_event_ticket ? 'text-green-700' : ($ticket_amount > 0 ? 'text-blue-700' : 'text-yellow-700'); ?> text-sm">
                    <?php echo $checkout_message; ?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row md:space-x-8 md:rtl:space-x-reverse">
        <div class="w-full md:w-2/3">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">
                <?php
                if ($with_transport) {
                    echo $has_event_ticket ? 'حجز مواصلات لـ ' . $event['title'] : 'حجز تذكرة ومواصلات لـ ' . $event['title'];
                } else {
                    printf($lang['checkout_title'] ?? 'حجز تذكرة لـ %s', $event['title']);
                }
                ?>
            </h1>

            <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800"><?php echo $lang['event_details'] ?? 'تفاصيل الفعالية'; ?></h2>
                    <ul class="divide-y divide-gray-200">
                        <li class="py-3">
                            <div class="flex justify-between">
                                <span class="font-medium"><?php echo $lang['date'] ?? 'التاريخ:'; ?></span>
                                <span class="text-gray-600"><?php echo date('Y-m-d H:i', strtotime($event['date_time'])); ?></span>
                            </div>
                        </li>
                        <li class="py-3">
                            <div class="flex justify-between">
                                <span class="font-medium"><?php echo $lang['location'] ?? 'المكان:'; ?></span>
                                <span class="text-gray-600"><?php echo $event['location']; ?></span>
                            </div>
                        </li>
                        <li class="py-3">
                            <div class="flex justify-between">
                                <span class="font-medium"><?php echo $lang['price'] ?? 'السعر:'; ?></span>
                                <span class="text-gray-600"><?php echo $event['price']; ?> ₪ <?php echo $lang['per_ticket'] ?? 'للتذكرة الواحدة'; ?></span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <form action="payment-process.php" method="post" class="bg-white rounded-lg shadow-md p-6" id="payment-form">
                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                <?php if ($with_transport): ?>
                <input type="hidden" name="with_transport" value="1">
                <input type="hidden" name="transport_amount" value="<?php echo $transport_amount; ?>">
                <?php endif; ?>

                <?php if(isset($_SESSION['payment_error'])): ?>
                <div id="error-box" class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                    <p><?php echo $_SESSION['payment_error']; ?></p>
                </div>
                <?php unset($_SESSION['payment_error']); endif; ?>

                <div class="mb-4">
                    <label for="quantity" class="block text-gray-700 font-medium mb-2"><?php echo $lang['quantity'] ?? 'عدد التذاكر'; ?></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                            <i class="fas fa-ticket-alt text-gray-400"></i>
                        </div>
                        <select class="w-full border border-gray-300 rounded-lg <?php echo ($selected_lang == 'en') ? 'pl-10 pr-4' : 'pr-10 pl-4'; ?> py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent appearance-none"
                                id="quantity" name="quantity" required>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                        <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'right-0 pr-3' : 'left-0 pl-3'; ?> flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- اختيار طريقة الدفع -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800"><?php echo $lang['payment_method'] ?? 'طريقة الدفع'; ?></h3>

                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <label class="payment-method-option border border-gray-200 rounded-lg p-4 flex flex-col items-center justify-center cursor-pointer hover:border-purple-500 transition-colors">
                            <input type="radio" name="payment_type" value="credit_card" class="form-radio text-purple-600 hidden" checked>
                            <div class="flex items-center justify-center mb-2">
                                <i class="fab fa-cc-visa text-blue-600 text-2xl mx-1"></i>
                                <i class="fab fa-cc-mastercard text-red-600 text-2xl mx-1"></i>
                            </div>
                            <span class="font-medium text-center"><?php echo $lang['credit_card'] ?? 'بطاقة ائتمان'; ?></span>
                        </label>
                        <label class="payment-method-option border border-gray-200 rounded-lg p-4 flex flex-col items-center justify-center cursor-pointer hover:border-purple-500 transition-colors">
                            <input type="radio" name="payment_type" value="paypal" class="form-radio text-purple-600 hidden">
                            <div class="flex items-center justify-center mb-2">
                                <i class="fab fa-paypal text-blue-800 text-2xl"></i>
                            </div>
                            <span class="font-medium text-center">PayPal</span>
                        </label>
                        <label class="payment-method-option border border-gray-200 rounded-lg p-4 flex flex-col items-center justify-center cursor-pointer hover:border-purple-500 transition-colors">
                            <input type="radio" name="payment_type" value="crypto" class="form-radio text-purple-600 hidden">
                            <div class="flex items-center justify-center mb-2">
                                <i class="fab fa-bitcoin text-orange-500 text-2xl mx-1"></i>
                                <i class="fas fa-coins text-yellow-500 text-2xl mx-1"></i>
                            </div>
                            <span class="font-medium text-center"><?php echo $lang['crypto_payment'] ?? 'العملات الرقمية'; ?></span>
                        </label>
                    </div>

                    <!-- حقول بطاقة الائتمان -->
                    <div id="credit-card-fields">
                        <div class="mb-4">
                            <label for="card_number" class="block text-gray-700 font-medium mb-2"><?php echo $lang['card_number'] ?? 'رقم البطاقة'; ?></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                    <i class="fas fa-credit-card text-gray-400"></i>
                                </div>
                                <input type="text" class="w-full border border-gray-300 rounded-lg <?php echo ($selected_lang == 'en') ? 'pl-10 pr-4' : 'pr-10 pl-4'; ?> py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:space-x-4 md:rtl:space-x-reverse mb-4">
                            <div class="w-full md:w-1/2 mb-4 md:mb-0">
                                <label for="expiry_date" class="block text-gray-700 font-medium mb-2"><?php echo $lang['expiry_date'] ?? 'تاريخ الانتهاء'; ?></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                        <i class="fas fa-calendar-alt text-gray-400"></i>
                                    </div>
                                    <input type="text" class="w-full border border-gray-300 rounded-lg <?php echo ($selected_lang == 'en') ? 'pl-10 pr-4' : 'pr-10 pl-4'; ?> py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                </div>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label for="cvv" class="block text-gray-700 font-medium mb-2">CVV</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input type="text" class="w-full border border-gray-300 rounded-lg <?php echo ($selected_lang == 'en') ? 'pl-10 pr-4' : 'pr-10 pl-4'; ?> py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        id="cvv" name="cvv" placeholder="123">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- حقول PayPal -->
                    <div id="paypal-fields" class="hidden mb-4">
                        <div class="mb-4">
                            <label for="paypal_email" class="block text-gray-700 font-medium mb-2"><?php echo $lang['paypal_email'] ?? 'بريد PayPal الإلكتروني'; ?></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" class="w-full border border-gray-300 rounded-lg <?php echo ($selected_lang == 'en') ? 'pl-10 pr-4' : 'pr-10 pl-4'; ?> py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    id="paypal_email" name="paypal_email" placeholder="example@email.com">
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-700 mb-4"><?php echo $lang['paypal_redirect_message'] ?? 'سيتم توجيهك إلى صفحة تسجيل الدخول إلى PayPal عند النقر على زر إكمال الدفع'; ?></p>
                            <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg" alt="PayPal Logo" class="mx-auto w-30 max-w-full" />
                        </div>
                    </div>

                    <!-- حقول العملات الرقمية -->
                    <div id="crypto-fields" class="hidden mb-4">
                        <div class="text-center">
                            <p class="text-gray-700 mb-4"><?php echo $lang['crypto_message'] ?? 'سيتم توجيهك إلى صفحة الدفع بالعملات الرقمية عند النقر على زر إكمال الدفع'; ?></p>
                            <div class="flex justify-center space-x-4 rtl:space-x-reverse mb-4">
                                <div class="text-center">
                                    <i class="fab fa-bitcoin text-orange-500 text-3xl mb-2"></i>
                                    <p class="text-sm">Bitcoin</p>
                                </div>
                                <div class="text-center">
                                    <i class="fab fa-ethereum text-purple-600 text-3xl mb-2"></i>
                                    <p class="text-sm">Ethereum</p>
                                </div>
                                <div class="text-center">
                                    <i class="fas fa-coins text-yellow-500 text-3xl mb-2"></i>
                                    <p class="text-sm">USDT</p>
                                </div>
                                <div class="text-center">
                                    <i class="fas fa-gem text-blue-500 text-3xl mb-2"></i>
                                    <p class="text-sm">TON</p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-2"><?php echo $lang['crypto_rate'] ?? 'سعر التحويل: 1 دولار = 3.65 شيكل'; ?></p>
                            <p class="text-sm text-gray-600"><?php echo $lang['crypto_secure'] ?? 'الدفع آمن ومشفر بواسطة CryptoBot'; ?></p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                    <i class="fas fa-lock <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                    <?php echo $lang['complete_payment'] ?? 'اكمل عملية الدفع'; ?>
                </button>
            </form>
        </div>

        <div class="w-full md:w-1/3 mt-6 md:mt-0">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800"><?php echo $lang['order_summary'] ?? 'ملخص الطلب'; ?></h2>
                    <ul class="divide-y divide-gray-200">
                        <?php if (!$with_transport || !$has_event_ticket): ?>
                        <!-- Event Ticket (إذا لم يكن لديه تذكرة أو ليس حجز مواصلات) -->
                        <li class="py-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600"><?php echo $lang['ticket'] ?? 'التذكرة'; ?></span>
                                <span class="font-medium"><?php echo $event['title']; ?></span>
                            </div>
                        </li>
                        <li class="py-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600"><?php echo $lang['price'] ?? 'السعر'; ?></span>
                                <?php if (!empty($event['original_price'])): ?>
                                <div class="text-right">
                                    <span class="line-through text-gray-500 text-sm block"><?php echo $event['original_price']; ?> ₪</span>
                                    <span class="font-medium text-red-600"><?php echo $event['price']; ?> ₪</span>
                                </div>
                                <?php else: ?>
                                <span class="font-medium"><?php echo $event['price']; ?> ₪</span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="py-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600"><?php echo $lang['quantity'] ?? 'العدد'; ?></span>
                                <span class="font-medium" data-quantity>1</span>
                            </div>
                        </li>
                        <?php else: ?>
                        <!-- المستخدم لديه تذكرة - إظهار رسالة -->
                        <li class="py-3 bg-green-50 -mx-6 px-6">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 ml-2"></i>
                                <div>
                                    <span class="font-medium text-green-800">تذكرة موجودة</span>
                                    <p class="text-sm text-green-700">لديك تذكرة صالحة لهذا الحدث</p>
                                </div>
                            </div>
                        </li>
                        <?php endif; ?>

                        <?php if ($with_transport && $transport_booking): ?>
                        <!-- Transport Section -->
                        <li class="py-3 bg-blue-50 -mx-6 px-6">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-bus text-blue-600 ml-2"></i>
                                <span class="font-medium text-blue-800">خدمة المواصلات</span>
                            </div>
                            <div class="text-sm text-blue-700 space-y-1">
                                <div>من: <?php echo htmlspecialchars($transport_booking['starting_point_name'] ?? 'نقطة الانطلاق'); ?></div>
                                <div>عدد الركاب: <?php echo $transport_booking['passengers_count']; ?></div>
                                <div>المبلغ: <?php echo number_format($transport_amount, 2); ?> ₪</div>
                            </div>
                        </li>
                        <?php endif; ?>

                        <!-- Total -->
                        <li class="py-3">
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-800"><?php echo $lang['total_amount'] ?? 'المجموع'; ?></span>
                                <span class="font-bold text-purple-600" data-total>
                                    <?php
                                    if ($with_transport && $transport_booking) {
                                        // استخدم المبلغ الإجمالي المحسوب مسبقاً
                                        $base_total = $transport_booking['total_amount'];
                                    } else {
                                        // حجز تذكرة عادي
                                        $base_total = $event['price'];
                                    }
                                    echo number_format($base_total, 2);
                                    ?> ₪
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <?php if ($with_transport && $transport_booking): ?>
            <!-- Transport Details Card -->
            <div class="bg-blue-50 rounded-lg shadow-md overflow-hidden mt-4">
                <div class="p-4">
                    <h3 class="text-lg font-semibold mb-3 text-blue-800 flex items-center">
                        <i class="fas fa-route ml-2"></i>
                        تفاصيل الرحلة
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-blue-700">نقطة الانطلاق:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($transport_booking['starting_point_name'] ?? 'غير محدد'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">عدد الركاب:</span>
                            <span class="font-medium"><?php echo $transport_booking['passengers_count']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">اسم الراكب:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($transport_booking['passenger_name'] ?? ''); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">رقم الجوال:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($transport_booking['passenger_phone'] ?? ''); ?></span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-blue-200">
                        <div class="flex justify-between font-medium">
                            <span class="text-blue-800">مبلغ المواصلات:</span>
                            <span class="text-blue-800"><?php echo number_format($transport_amount, 2); ?> ₪</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // دالة لإخفاء أرقام البطاقة
    function maskCardNumber(cardNumber) {
        if(!cardNumber) return '';

        // إزالة المسافات من رقم البطاقة
        cardNumber = cardNumber.replace(/\s+/g, '');

        // التأكد من أن رقم البطاقة يحتوي على 4 أرقام على الأقل
        if(cardNumber.length < 4) return 'XXXX';

        return 'XXXX XXXX XXXX ' + cardNumber.slice(-4);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // تحديث المجموع عند تغيير الكمية
        const quantitySelect = document.getElementById('quantity');
        const pricePerTicket = <?php echo $event['price']; ?>; // Always use the current price (after discount if applicable)
        const paymentForm = document.getElementById('payment-form');
        const paymentTypeRadios = document.querySelectorAll('input[name="payment_type"]');
        const creditCardFields = document.getElementById('credit-card-fields');
        const paypalFields = document.getElementById('paypal-fields');
        const cryptoFields = document.getElementById('crypto-fields');
        const savedPaymentMethods = document.getElementById('saved-payment-methods');
        const addNewPaymentMethodBtn = document.getElementById('add-new-payment-method');
        const cardNumberInput = document.getElementById('card_number');
        const expiryDateInput = document.getElementById('expiry_date');
        const cvvInput = document.getElementById('cvv');

        function updateTotal() {
            const quantity = parseInt(quantitySelect.value);
            const withTransport = <?php echo $with_transport ? 'true' : 'false'; ?>;
            const hasEventTicket = <?php echo $has_event_ticket ? 'true' : 'false'; ?>;
            const transportAmount = <?php echo $transport_amount; ?>;
            const ticketAmount = <?php echo $ticket_amount; ?>;

            let totalPrice = 0;

            if (withTransport) {
                // حجز مواصلات
                if (hasEventTicket) {
                    // لديه تذكرة - المواصلات فقط
                    totalPrice = transportAmount;
                } else {
                    // لا يملك تذكرة - التذكرة + المواصلات
                    const eventTotalPrice = (ticketAmount / <?php echo $transport_booking['passengers_count'] ?? 1; ?>) * quantity;
                    totalPrice = eventTotalPrice + transportAmount;
                }
            } else {
                // حجز تذكرة عادي
                totalPrice = pricePerTicket * quantity;
            }

            // تحديث العناصر في ملخص الطلب
            const quantityElement = document.querySelector('[data-quantity]');
            if (quantityElement) {
                quantityElement.textContent = quantity;
            }
            document.querySelector('[data-total]').textContent = totalPrice.toFixed(2) + ' ₪';
        }

        quantitySelect.addEventListener('change', updateTotal);

        // إظهار/إخفاء حقول بطاقة الائتمان وحقول PayPal حسب نوع طريقة الدفع
        paymentTypeRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                // تحديث تصميم خيارات طرق الدفع
                document.querySelectorAll('.payment-method-option').forEach(function(option) {
                    option.classList.remove('border-purple-500', 'bg-purple-50');
                });

                // تحديد الخيار المختار
                this.closest('.payment-method-option').classList.add('border-purple-500', 'bg-purple-50');

                if (this.value === 'credit_card') {
                    // عرض حقول بطاقة الائتمان
                    creditCardFields.classList.remove('hidden');
                    paypalFields.classList.add('hidden');
                    cryptoFields.classList.add('hidden');
                } else if (this.value === 'paypal') {
                    // عرض حقول PayPal
                    creditCardFields.classList.add('hidden');
                    paypalFields.classList.remove('hidden');
                    cryptoFields.classList.add('hidden');
                } else if (this.value === 'crypto') {
                    // عرض حقول العملات الرقمية
                    creditCardFields.classList.add('hidden');
                    paypalFields.classList.add('hidden');
                    cryptoFields.classList.remove('hidden');
                }
            });
        });

        // تحديد الخيار الافتراضي عند تحميل الصفحة
        const defaultPaymentOption = document.querySelector('input[name="payment_type"]:checked');
        if (defaultPaymentOption) {
            defaultPaymentOption.closest('.payment-method-option').classList.add('border-purple-500', 'bg-purple-50');
        }

        // تنسيق رقم البطاقة أثناء الكتابة
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');

                // تقييد الطول إلى 16 رقم
                if (value.length > 16) {
                    value = value.substring(0, 16);
                }

                let formattedValue = '';

                for (let i = 0; i < value.length; i++) {
                    if (i > 0 && i % 4 === 0) {
                        formattedValue += ' ';
                    }
                    formattedValue += value[i];
                }

                e.target.value = formattedValue;

                // تغيير لون الحقل بناءً على نوع البطاقة
                const cardType = getCardType(value);
                if (cardType === 'visa') {
                    e.target.classList.add('border-blue-500');
                    e.target.classList.remove('border-red-500', 'border-green-500', 'border-gray-300');
                } else if (cardType === 'mastercard') {
                    e.target.classList.add('border-red-500');
                    e.target.classList.remove('border-blue-500', 'border-green-500', 'border-gray-300');
                } else if (cardType === 'amex') {
                    e.target.classList.add('border-green-500');
                    e.target.classList.remove('border-blue-500', 'border-red-500', 'border-gray-300');
                } else {
                    e.target.classList.add('border-gray-300');
                    e.target.classList.remove('border-blue-500', 'border-red-500', 'border-green-500');
                }
            });
        }

        // تحديد نوع البطاقة
        function getCardType(number) {
            const firstDigit = number.charAt(0);
            const firstTwoDigits = number.substring(0, 2);

            if (firstDigit === '4') {
                return 'visa';
            } else if (firstTwoDigits >= '51' && firstTwoDigits <= '55') {
                return 'mastercard';
            } else if (firstTwoDigits === '34' || firstTwoDigits === '37') {
                return 'amex';
            }

            return 'unknown';
        }

        // التحقق من صحة رقم البطاقة باستخدام خوارزمية Luhn
        function validateCardLuhn(number) {
            // إزالة المسافات والشرطات
            number = number.replace(/\D/g, '');

            // التحقق من أن الطول 16 رقم
            if (number.length !== 16) {
                return false;
            }

            // تطبيق خوارزمية Luhn
            let sum = 0;
            let alt = false;
            for (let i = number.length - 1; i >= 0; i--) {
                let n = parseInt(number[i]);
                if (alt) {
                    n *= 2;
                    if (n > 9) {
                        n -= 9;
                    }
                }
                sum += n;
                alt = !alt;
            }

            // التحقق من أن المجموع قابل للقسمة على 10
            return (sum % 10 === 0);
        }

        // تنسيق تاريخ الانتهاء
        if (expiryDateInput) {
            expiryDateInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');

                if (value.length > 2) {
                    e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
                } else {
                    e.target.value = value;
                }
            });
        }

        // تحديد الحد الأقصى لرقم CVV
        if (cvvInput) {
            cvvInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                e.target.value = value.substring(0, 3);
            });
        }

        // التحقق من صحة النموذج قبل الإرسال
        paymentForm.addEventListener('submit', function(e) {
            // الحصول على نوع طريقة الدفع المحددة
            const paymentType = document.querySelector('input[name="payment_type"]:checked').value;

            // إذا تم اختيار PayPal، نقوم بإعادة التوجيه إلى صفحة تسجيل دخول PayPal
            if (paymentType === 'paypal') {
                e.preventDefault();

                // إذا كان المستخدم قد أدخل بريد PayPal، نتحقق من صحته أولاً
                const paypalEmail = document.getElementById('paypal_email');
                if (paypalEmail && paypalEmail.value) {
                    // إرسال طلب AJAX للتحقق من صحة حساب PayPal
                    fetch('verify-paypal-ajax.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'email=' + encodeURIComponent(paypalEmail.value)
                    })
                    .then(response => response.json())
                    .then(data => {
                        // التحقق من وجود رسالة خطأ تتعلق بفشل الاتصال
                        if (!data.status && data.message && data.message.includes('فشل الاتصال بموقع PayPal')) {
                            console.warn('PayPal connection error:', data.message);

                            // التحقق من النطاقات الشائعة
                            const commonDomains = ['@gmail.com', '@yahoo.com', '@hotmail.com', '@outlook.com', '@icloud.com', '@aol.com', '@mail.com'];
                            const email = paypalEmail.value.toLowerCase();
                            let isCommonDomain = false;

                            for (const domain of commonDomains) {
                                if (email.includes(domain)) {
                                    isCommonDomain = true;
                                    break;
                                }
                            }

                            if (isCommonDomain) {
                                // إذا كان البريد من نطاق شائع، نقوم بإعادة التوجيه إلى صفحة تسجيل دخول PayPal
                                window.location.href = 'paypal-login.php?return_url=' + encodeURIComponent('checkout.php?event_id=<?php echo $event_id; ?>') + '&email=' + encodeURIComponent(paypalEmail.value);
                            } else {
                                // إذا كان البريد ليس من نطاق شائع، نعرض رسالة خطأ
                                showErrorMessage(data.message + '<br><button type="button" id="bypass-verification" class="mt-2 text-blue-600 hover:underline">المتابعة على أي حال</button>');

                                // إضافة مستمع حدث لزر التخطي
                                setTimeout(() => {
                                    const bypassButton = document.getElementById('bypass-verification');
                                    if (bypassButton) {
                                        bypassButton.addEventListener('click', function() {
                                            window.location.href = 'paypal-login.php?return_url=' + encodeURIComponent('checkout.php?event_id=<?php echo $event_id; ?>') + '&email=' + encodeURIComponent(paypalEmail.value);
                                        });
                                    }
                                }, 100);
                            }
                        } else if (data.status) {
                            // إذا كان الحساب صالح، نقوم بإعادة التوجيه إلى صفحة تسجيل دخول PayPal
                            window.location.href = 'paypal-login.php?return_url=' + encodeURIComponent('checkout.php?event_id=<?php echo $event_id; ?>') + '&email=' + encodeURIComponent(paypalEmail.value);
                        } else {
                            // إذا كان الحساب غير صالح، نعرض رسالة خطأ
                            showErrorMessage(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // في حالة حدوث خطأ، نقوم بإعادة التوجيه إلى صفحة تسجيل دخول PayPal
                        window.location.href = 'paypal-login.php?return_url=' + encodeURIComponent('checkout.php?event_id=<?php echo $event_id; ?>') + '&email=' + encodeURIComponent(paypalEmail.value);
                    });

                    // دالة لعرض رسالة خطأ
                    function showErrorMessage(message) {
                        let errorBox = document.getElementById('error-box');
                        if (!errorBox) {
                            errorBox = document.createElement('div');
                            errorBox.id = 'error-box';
                            errorBox.className = 'bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded';
                            errorBox.setAttribute('role', 'alert');

                            // إضافة مربع الخطأ في بداية النموذج
                            const firstChild = paymentForm.firstChild;
                            paymentForm.insertBefore(errorBox, firstChild);
                        }

                        // تحديث نص الخطأ
                        errorBox.innerHTML = `<p>${message}</p>`;

                        // التمرير إلى مربع الخطأ
                        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    // إذا لم يدخل المستخدم بريد PayPal، نقوم بإعادة التوجيه إلى صفحة تسجيل دخول PayPal
                    window.location.href = 'paypal-login.php?return_url=' + encodeURIComponent('checkout.php?event_id=<?php echo $event_id; ?>');
                }

                return false;
            }

            // إذا تم اختيار العملات الرقمية، نقوم بإنشاء فاتورة دفع وإعادة التوجيه
            if (paymentType === 'crypto') {
                e.preventDefault();

                // الحصول على المبلغ الإجمالي
                const totalAmount = <?php echo $event['price']; ?> * parseInt(document.getElementById('quantity').value);
                const orderId = 'order_<?php echo time(); ?>_<?php echo $event_id; ?>';
                const description = '<?php echo addslashes($event['title']); ?>';

                // إظهار رسالة انتظار
                let loadingBox = document.createElement('div');
                loadingBox.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                loadingBox.innerHTML = `
                    <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-purple-500 mx-auto mb-4"></div>
                        <p class="text-lg font-medium"><?php echo $lang['processing_payment'] ?? 'جاري إنشاء فاتورة الدفع...'; ?></p>
                    </div>
                `;
                document.body.appendChild(loadingBox);

                // إرسال طلب AJAX لإنشاء فاتورة دفع
                fetch('crypto/generate_invoice.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=create_invoice&amount=' + totalAmount + '&order_id=' + orderId + '&description=' + encodeURIComponent(description) + '&paid_btn_name=callback'
                })
                .then(response => response.json())
                .then(data => {
                    // إزالة رسالة الانتظار
                    document.body.removeChild(loadingBox);

                    if (data.success) {
                        // فتح صفحة الدفع في تبويب جديد
                        window.open(data.pay_url, '_blank');

                        // إظهار أيقونة انتظار بسيطة
                        showSimpleNotification(data.invoice_id, orderId, totalAmount);
                    } else {
                        // عرض رسالة خطأ
                        let errorBox = document.getElementById('error-box');
                        if (!errorBox) {
                            errorBox = document.createElement('div');
                            errorBox.id = 'error-box';
                            errorBox.className = 'bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded';
                            errorBox.setAttribute('role', 'alert');

                            // إضافة مربع الخطأ في بداية النموذج
                            const firstChild = paymentForm.firstChild;
                            paymentForm.insertBefore(errorBox, firstChild);
                        }

                        // تحديث نص الخطأ
                        let errorMessage = data.message;

                        // تنسيق رسالة الخطأ إذا كانت تحتوي على أسطر متعددة
                        if (errorMessage.includes('\n')) {
                            // تحويل النص المتعدد الأسطر إلى HTML
                            errorMessage = errorMessage.split('\n').map(line => {
                                // إضافة تنسيق للنصائح
                                if (line.startsWith('نصائح لحل المشكلة:')) {
                                    return `<strong class="text-blue-700">${line}</strong>`;
                                } else if (line.match(/^\d+\./)) {
                                    return `<span class="text-blue-600 ml-4">• ${line.substring(line.indexOf('.') + 1)}</span>`;
                                }
                                return line;
                            }).join('<br>');
                        }

                        // إضافة رابط لصفحة الاختبار
                        errorMessage += `<div class="mt-3">
                            <a href="crypto/test.php" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded inline-flex items-center">
                                <i class="fas fa-vial mr-2"></i>
                                اختبار الاتصال بخدمة الدفع
                            </a>
                        </div>`;

                        // إضافة اقتراحات إضافية
                        if (data.message.includes('Resolving timed out') || data.message.includes('Could not resolve host')) {
                            errorMessage += `<div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                                <div class="font-medium text-blue-700">اقتراحات إضافية:</div>
                                <ul class="mt-2 list-disc list-inside text-blue-600">
                                    <li>تأكد من أن جهازك متصل بالإنترنت</li>
                                    <li>جرب تعطيل جدار الحماية أو برنامج مكافحة الفيروسات مؤقتًا</li>
                                    <li>جرب استخدام شبكة إنترنت مختلفة</li>
                                    <li>تحقق من حالة خدمة CryptoBot على <a href="https://t.me/CryptoBot" target="_blank" class="underline">تيليجرام</a></li>
                                </ul>
                            </div>`;
                        }

                        // إضافة معلومات التصحيح إذا كانت متوفرة
                        if (data.debug) {
                            console.error('CryptoBot Error Debug:', data.debug);
                            errorMessage += `<div class="mt-3 text-sm">
                                <button type="button" id="show-debug-info" class="text-blue-600 hover:underline flex items-center">
                                    <i class="fas fa-bug mr-1"></i> عرض معلومات التصحيح
                                </button>
                                <div id="debug-info" class="hidden mt-2 p-2 bg-gray-100 rounded text-xs overflow-auto" style="max-height: 200px;">
                                    <pre>${JSON.stringify(data.debug, null, 2)}</pre>
                                </div>
                            </div>`;
                        }

                        errorBox.innerHTML = `<div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-red-800 mb-2"><?php echo $lang['payment_error'] ?? 'خطأ في عملية الدفع'; ?></h3>
                                <div>${errorMessage}</div>
                            </div>
                        </div>`;

                        // إضافة مستمع حدث لزر عرض معلومات التصحيح
                        setTimeout(() => {
                            const showDebugBtn = document.getElementById('show-debug-info');
                            if (showDebugBtn) {
                                showDebugBtn.addEventListener('click', function() {
                                    const debugInfo = document.getElementById('debug-info');
                                    if (debugInfo) {
                                        debugInfo.classList.toggle('hidden');
                                    }
                                });
                            }
                        }, 100);

                        // التمرير إلى مربع الخطأ
                        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                })
                .catch(error => {
                    // إزالة رسالة الانتظار
                    document.body.removeChild(loadingBox);

                    console.error('Error:', error);

                    // عرض رسالة خطأ
                    let errorBox = document.getElementById('error-box');
                    if (!errorBox) {
                        errorBox = document.createElement('div');
                        errorBox.id = 'error-box';
                        errorBox.className = 'bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded';
                        errorBox.setAttribute('role', 'alert');

                        // إضافة مربع الخطأ في بداية النموذج
                        const firstChild = paymentForm.firstChild;
                        paymentForm.insertBefore(errorBox, firstChild);
                    }

                    // تحديث نص الخطأ
                    errorBox.innerHTML = `<div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-800 mb-2"><?php echo $lang['payment_error'] ?? 'خطأ في عملية الدفع'; ?></h3>
                            <p><?php echo $lang['crypto_error'] ?? 'حدث خطأ أثناء إنشاء فاتورة الدفع. يرجى المحاولة مرة أخرى.'; ?></p>

                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                                <div class="font-medium text-blue-700">اقتراحات:</div>
                                <ul class="mt-2 list-disc list-inside text-blue-600">
                                    <li>تأكد من أن جهازك متصل بالإنترنت</li>
                                    <li>جرب تعطيل جدار الحماية أو برنامج مكافحة الفيروسات مؤقتًا</li>
                                    <li>جرب استخدام شبكة إنترنت مختلفة</li>
                                    <li>تحقق من حالة خدمة CryptoBot على <a href="https://t.me/CryptoBot" target="_blank" class="underline">تيليجرام</a></li>
                                </ul>
                            </div>

                            <div class="mt-3">
                                <a href="crypto/test.php" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded inline-flex items-center">
                                    <i class="fas fa-vial mr-2"></i>
                                    اختبار الاتصال بخدمة الدفع
                                </a>
                            </div>
                        </div>
                    </div>`;

                    // التمرير إلى مربع الخطأ
                    errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });

                return false;
            }

            // إذا تم اختيار بطاقة ائتمان، نتحقق من صحة الحقول
            if (paymentType === 'credit_card') {
                let isValid = true;
                let errorMessage = '';

                // التحقق من رقم البطاقة
                const cardNumber = cardNumberInput.value.replace(/\s/g, '');
                if (cardNumber.length !== 16) {
                    isValid = false;
                    errorMessage = '<?php echo $lang['invalid_card_number'] ?? 'رقم البطاقة يجب أن يكون 16 رقم'; ?>';
                    cardNumberInput.classList.add('border-red-500');
                } else if (!validateCardLuhn(cardNumber)) {
                    isValid = false;
                    errorMessage = '<?php echo $lang['invalid_card_number_luhn'] ?? 'رقم البطاقة غير صحيح، يرجى التحقق من الرقم المدخل'; ?>';
                    cardNumberInput.classList.add('border-red-500');
                }

                // التحقق من تاريخ الانتهاء
                const expiryDate = expiryDateInput.value;
                const [month, year] = expiryDate.split('/');
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear() % 100; // آخر رقمين من السنة
                const currentMonth = currentDate.getMonth() + 1; // الشهر الحالي (1-12)

                if (!month || !year || parseInt(month) < 1 || parseInt(month) > 12 ||
                    (parseInt(year) < currentYear || (parseInt(year) === currentYear && parseInt(month) < currentMonth))) {
                    isValid = false;
                    errorMessage = '<?php echo $lang['invalid_expiry_date'] ?? 'تاريخ الانتهاء غير صحيح'; ?>';
                    expiryDateInput.classList.add('border-red-500');
                }

                // التحقق من رمز CVV
                if (cvvInput.value.length < 3) {
                    isValid = false;
                    errorMessage = '<?php echo $lang['invalid_cvv'] ?? 'رمز CVV غير صحيح'; ?>';
                    cvvInput.classList.add('border-red-500');
                }

                if (!isValid) {
                    e.preventDefault();

                    // إنشاء أو تحديث مربع الخطأ
                    let errorBox = document.getElementById('error-box');
                    if (!errorBox) {
                        errorBox = document.createElement('div');
                        errorBox.id = 'error-box';
                        errorBox.className = 'bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded';
                        errorBox.setAttribute('role', 'alert');

                        // إضافة مربع الخطأ في بداية النموذج
                        const firstChild = paymentForm.firstChild;
                        paymentForm.insertBefore(errorBox, firstChild);
                    }

                    // تحديث نص الخطأ
                    errorBox.innerHTML = `<p>${errorMessage}</p>`;

                    // التمرير إلى مربع الخطأ
                    errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        // دالة لعرض شاشة انتظار الدفع في وسط الشاشة
        function showSimpleNotification(invoiceId, orderId, amount) {
            // تحويل المبلغ من شيكل إلى دولار
            const amountUsd = (amount / 3.65).toFixed(2);

            // إنشاء عنصر الإشعار
            const notification = document.createElement('div');
            notification.id = 'crypto-payment-waiting';
            notification.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';

            // إنشاء محتوى الإشعار
            notification.innerHTML = `
                <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
                    <div class="mb-6">
                        <div class="w-24 h-24 bg-blue-100 text-blue-600 rounded-full mx-auto flex items-center justify-center">
                            <i class="fab fa-bitcoin text-5xl"></i>
                        </div>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-800 mb-4"><?php echo $lang['crypto_payment_processing'] ?? 'جاري معالجة الدفع'; ?></h2>
                    <p class="text-gray-600 mb-6"><?php echo $lang['crypto_payment_instructions'] ?? 'يرجى إكمال عملية الدفع في نافذة CryptoBot التي تم فتحها للتو.'; ?></p>

                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex justify-between mb-2">
                            <span class="font-medium text-gray-600"><?php echo $lang['order_id'] ?? 'رقم الطلب:'; ?></span>
                            <span class="font-mono">${orderId}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-600"><?php echo $lang['amount'] ?? 'المبلغ:'; ?></span>
                            <span>${amount} ₪ (${amountUsd} $)</span>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="animate-pulse flex space-x-4 rtl:space-x-reverse items-center justify-center">
                            <div class="w-3 h-3 bg-blue-400 rounded-full"></div>
                            <div class="w-3 h-3 bg-blue-400 rounded-full"></div>
                            <div class="w-3 h-3 bg-blue-400 rounded-full"></div>
                        </div>
                        <p class="text-sm text-gray-500 mt-2"><?php echo $lang['waiting_for_payment'] ?? 'في انتظار إتمام الدفع...'; ?></p>
                    </div>

                    <div class="flex flex-col space-y-3">
                        <button id="open-crypto-bot-again" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            <i class="fab fa-telegram-plane <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i> <?php echo $lang['open_crypto_bot'] ?? 'فتح CryptoBot مرة أخرى'; ?>
                        </button>

                        <button id="close-crypto-notification" class="text-red-600 hover:text-red-700 font-medium py-2">
                            <?php echo $lang['close'] ?? 'إغلاق هذه النافذة'; ?>
                        </button>
                    </div>
                </div>
            `;

            // إضافة الإشعار إلى الصفحة
            document.body.appendChild(notification);

            // إضافة مستمع حدث لزر الإغلاق
            document.getElementById('close-crypto-notification').addEventListener('click', function() {
                document.body.removeChild(notification);
            });

            // إضافة مستمع حدث لزر فتح CryptoBot مرة أخرى
            document.getElementById('open-crypto-bot-again').addEventListener('click', function() {
                window.open('https://t.me/CryptoBot?start=' + invoiceId, '_blank');
            });
        }
    });
</script>

<?php
require_once 'includes/footer.php';
?>