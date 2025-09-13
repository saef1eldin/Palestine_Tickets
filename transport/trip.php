<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رحلات السفر المتاحة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#faf5ff',
                            100: '#f3e8ff',
                            200: '#e9d5ff',
                            300: '#d8b4fe',
                            400: '#c084fc',
                            500: '#a855f7',
                            600: '#9333ea',
                            700: '#7e22ce',
                            800: '#6b21a8',
                            900: '#581c87',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap');
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f3ff;
        }
        
        .trip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .trip-card {
            transition: all 0.3s ease;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-gradient-to-r from-primary-700 to-primary-900 text-white shadow-lg">
            <div class="container mx-auto px-4 py-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold">رحلاتي</h1>
                    <div class="flex items-center space-x-4">
                        <button class="bg-white text-primary-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition">
                            <i class="fas fa-user mr-2"></i>حسابي
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <!-- Search Section -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <h2 class="text-2xl font-bold text-primary-800 mb-6">ابحث عن رحلتك</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">نقطة الانطلاق</label>
                        <select id="departure" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">اختر نقطة الانطلاق</option>
                            <option value="riyadh">الرياض</option>
                            <option value="jeddah">جدة</option>
                            <option value="dammam">الدمام</option>
                            <option value="medina">المدينة المنورة</option>
                            <option value="taif">الطائف</option>
                        </select>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">الوجهة</label>
                        <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" disabled>
                            <option>مهرجان الرياض</option>
                        </select>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ السفر</label>
                        <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div class="col-span-1 flex items-end">
                        <button class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg font-medium transition">
                            <i class="fas fa-search mr-2"></i>بحث
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div id="results" class="hidden">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-primary-800">الرحلات المتاحة</h2>
                    <div class="flex space-x-2">
                        <button id="card-view" class="bg-primary-100 text-primary-700 p-2 rounded-lg active">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button id="table-view" class="bg-gray-200 text-gray-700 p-2 rounded-lg">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>

                <!-- Cards View -->
                <div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Trip cards will be inserted here by JavaScript -->
                </div>

                <!-- Table View -->
                <div id="table-container" class="hidden overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-primary-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-primary-700 uppercase tracking-wider">وقت الانطلاق</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-primary-700 uppercase tracking-wider">وقت الوصول</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-primary-700 uppercase tracking-wider">وسيلة النقل</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-primary-700 uppercase tracking-wider">المقاعد</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-primary-700 uppercase tracking-wider">السعر</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-primary-700 uppercase tracking-wider">الوصف</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-primary-700 uppercase tracking-wider">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="bg-white divide-y divide-gray-200">
                            <!-- Table rows will be inserted here by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Sample trip data
        const trips = {
            riyadh: [
                {
                    id: 1,
                    departureTime: "08:00 ص",
                    arrivalTime: "10:30 ص",
                    transportType: "باص فاخر",
                    seatsLeft: 12,
                    price: "75 ر.س",
                    description: "مكيف، واي فاي مجاني، كراسي مريحة، مشروبات مجانية",
                    features: ["wifi", "ac", "drinks", "comfort"]
                },
                {
                    id: 2,
                    departureTime: "10:00 ص",
                    arrivalTime: "12:30 م",
                    transportType: "فان",
                    seatsLeft: 6,
                    price: "60 ر.س",
                    description: "مكيف، خدمة توصيل للمنزل، مساحة للأمتعة",
                    features: ["ac", "luggage", "pickup"]
                },
                {
                    id: 3,
                    departureTime: "02:00 م",
                    arrivalTime: "04:30 م",
                    transportType: "سيارة خاصة",
                    seatsLeft: 3,
                    price: "150 ر.س",
                    description: "سيارة خاصة مع سائق، مكيف، مساحة شخصية",
                    features: ["private", "ac", "flexible"]
                }
            ],
            jeddah: [
                {
                    id: 4,
                    departureTime: "07:00 ص",
                    arrivalTime: "12:30 م",
                    transportType: "باص فاخر",
                    seatsLeft: 8,
                    price: "120 ر.س",
                    description: "مكيف، واي فاي، شاشات ترفيه، وجبة خفيفة",
                    features: ["wifi", "ac", "entertainment", "snack"]
                },
                {
                    id: 5,
                    departureTime: "09:00 ص",
                    arrivalTime: "02:30 م",
                    transportType: "قطار",
                    seatsLeft: 25,
                    price: "180 ر.س",
                    description: "قطار سريع، مقاعد واسعة، مكيف، كافتيريا",
                    features: ["fast", "spacious", "ac", "cafeteria"]
                }
            ],
            dammam: [
                {
                    id: 6,
                    departureTime: "06:00 ص",
                    arrivalTime: "01:00 م",
                    transportType: "باص فاخر",
                    seatsLeft: 15,
                    price: "150 ر.س",
                    description: "مكيف، واي فاي، مقاعد قابلة للتمديد، وجبة ساخنة",
                    features: ["wifi", "ac", "reclining", "meal"]
                }
            ],
            medina: [
                {
                    id: 7,
                    departureTime: "08:30 ص",
                    arrivalTime: "11:30 ص",
                    transportType: "فان",
                    seatsLeft: 5,
                    price: "65 ر.س",
                    description: "مكيف، خدمة توصيل، مساحة للأمتعة",
                    features: ["ac", "luggage", "pickup"]
                },
                {
                    id: 8,
                    departureTime: "11:00 ص",
                    arrivalTime: "02:00 م",
                    transportType: "باص",
                    seatsLeft: 20,
                    price: "55 ر.س",
                    description: "مكيف، خدمة أساسية",
                    features: ["ac", "basic"]
                }
            ],
            taif: [
                {
                    id: 9,
                    departureTime: "09:30 ص",
                    arrivalTime: "11:00 ص",
                    transportType: "سيارة خاصة",
                    seatsLeft: 2,
                    price: "90 ر.س",
                    description: "سيارة خاصة مع سائق، مكيف، مساحة شخصية",
                    features: ["private", "ac", "flexible"]
                }
            ]
        };

        // DOM elements
        const departureSelect = document.getElementById('departure');
        const resultsSection = document.getElementById('results');
        const cardsContainer = document.getElementById('cards-container');
        const tableContainer = document.getElementById('table-container');
        const tableBody = document.getElementById('table-body');
        const cardViewBtn = document.getElementById('card-view');
        const tableViewBtn = document.getElementById('table-view');

        // Event listeners
        departureSelect.addEventListener('change', function() {
            const selectedCity = this.value;
            if (selectedCity) {
                displayTrips(selectedCity);
                resultsSection.classList.remove('hidden');
            } else {
                resultsSection.classList.add('hidden');
            }
        });

        cardViewBtn.addEventListener('click', function() {
            cardsContainer.classList.remove('hidden');
            tableContainer.classList.add('hidden');
            cardViewBtn.classList.add('bg-primary-100', 'text-primary-700');
            cardViewBtn.classList.remove('bg-gray-200', 'text-gray-700');
            tableViewBtn.classList.add('bg-gray-200', 'text-gray-700');
            tableViewBtn.classList.remove('bg-primary-100', 'text-primary-700');
        });

        tableViewBtn.addEventListener('click', function() {
            cardsContainer.classList.add('hidden');
            tableContainer.classList.remove('hidden');
            tableViewBtn.classList.add('bg-primary-100', 'text-primary-700');
            tableViewBtn.classList.remove('bg-gray-200', 'text-gray-700');
            cardViewBtn.classList.add('bg-gray-200', 'text-gray-700');
            cardViewBtn.classList.remove('bg-primary-100', 'text-primary-700');
        });

        // Display trips function
        function displayTrips(city) {
            const cityTrips = trips[city];
            
            // Clear previous results
            cardsContainer.innerHTML = '';
            tableBody.innerHTML = '';
            
            if (!cityTrips || cityTrips.length === 0) {
                cardsContainer.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-exclamation-circle text-4xl text-primary-500 mb-4"></i>
                        <h3 class="text-xl font-medium text-gray-700">لا توجد رحلات متاحة</h3>
                        <p class="text-gray-500 mt-2">عذراً، لا توجد رحلات متاحة من ${getCityName(city)} حالياً.</p>
                    </div>
                `;
                return;
            }
            
            // Create cards
            cityTrips.forEach(trip => {
                const card = createTripCard(trip);
                cardsContainer.appendChild(card);
                
                const tableRow = createTableRow(trip);
                tableBody.appendChild(tableRow);
            });
        }
        
        function createTripCard(trip) {
            const card = document.createElement('div');
            card.className = 'trip-card bg-white rounded-xl shadow-md overflow-hidden border border-gray-100';
            
            // Features badges
            const featuresBadges = trip.features.map(feature => {
                const icons = {
                    wifi: { icon: 'wifi', color: 'bg-blue-100 text-blue-800' },
                    ac: { icon: 'snowflake', color: 'bg-cyan-100 text-cyan-800' },
                    drinks: { icon: 'wine-glass', color: 'bg-purple-100 text-purple-800' },
                    comfort: { icon: 'couch', color: 'bg-green-100 text-green-800' },
                    luggage: { icon: 'suitcase', color: 'bg-yellow-100 text-yellow-800' },
                    pickup: { icon: 'home', color: 'bg-indigo-100 text-indigo-800' },
                    private: { icon: 'user-shield', color: 'bg-red-100 text-red-800' },
                    flexible: { icon: 'clock', color: 'bg-orange-100 text-orange-800' },
                    entertainment: { icon: 'tv', color: 'bg-pink-100 text-pink-800' },
                    snack: { icon: 'cookie-bite', color: 'bg-amber-100 text-amber-800' },
                    fast: { icon: 'bolt', color: 'bg-emerald-100 text-emerald-800' },
                    spacious: { icon: 'expand', color: 'bg-teal-100 text-teal-800' },
                    cafeteria: { icon: 'utensils', color: 'bg-rose-100 text-rose-800' },
                    reclining: { icon: 'chair', color: 'bg-lime-100 text-lime-800' },
                    meal: { icon: 'drumstick-bite', color: 'bg-fuchsia-100 text-fuchsia-800' },
                    basic: { icon: 'check-circle', color: 'bg-gray-100 text-gray-800' }
                };
                
                const featureData = icons[feature] || { icon: 'check', color: 'bg-gray-100 text-gray-800' };
                return `
                    <span class="${featureData.color} badge mr-1 mb-1">
                        <i class="fas fa-${featureData.icon} mr-1"></i>
                    </span>
                `;
            }).join('');
            
            card.innerHTML = `
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-primary-800">${trip.transportType}</h3>
                            <div class="text-sm text-primary-600">${trip.description}</div>
                        </div>
                        <span class="bg-primary-100 text-primary-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                            ${trip.seatsLeft} مقاعد متبقية
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center my-4">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">وقت الانطلاق</div>
                            <div class="text-lg font-bold text-primary-700">${trip.departureTime}</div>
                        </div>
                        <div class="text-primary-500 mx-2">
                            <i class="fas fa-arrow-left"></i>
                        </div>
                        <div class="text-center">
                            <div class="text-sm text-gray-500">وقت الوصول</div>
                            <div class="text-lg font-bold text-primary-700">${trip.arrivalTime}</div>
                        </div>
                    </div>
                    
                    <div class="my-4">
                        <div class="text-sm text-gray-500 mb-1">المميزات:</div>
                        <div class="flex flex-wrap">
                            ${featuresBadges}
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-100">
                        <div class="text-2xl font-bold text-primary-700">${trip.price}</div>
                        <button class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg font-medium transition">
                            <i class="fas fa-ticket-alt mr-2"></i>احجز الآن
                        </button>
                    </div>
                </div>
            `;
            
            return card;
        }
        
        function createTableRow(trip) {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            
            // Features icons
            const featuresIcons = trip.features.map(feature => {
                const icons = {
                    wifi: { icon: 'wifi', color: 'text-blue-500' },
                    ac: { icon: 'snowflake', color: 'text-cyan-500' },
                    drinks: { icon: 'wine-glass', color: 'text-purple-500' },
                    comfort: { icon: 'couch', color: 'text-green-500' },
                    luggage: { icon: 'suitcase', color: 'text-yellow-500' },
                    pickup: { icon: 'home', color: 'text-indigo-500' },
                    private: { icon: 'user-shield', color: 'text-red-500' },
                    flexible: { icon: 'clock', color: 'text-orange-500' },
                    entertainment: { icon: 'tv', color: 'text-pink-500' },
                    snack: { icon: 'cookie-bite', color: 'text-amber-500' },
                    fast: { icon: 'bolt', color: 'text-emerald-500' },
                    spacious: { icon: 'expand', color: 'text-teal-500' },
                    cafeteria: { icon: 'utensils', color: 'text-rose-500' },
                    reclining: { icon: 'chair', color: 'text-lime-500' },
                    meal: { icon: 'drumstick-bite', color: 'text-fuchsia-500' },
                    basic: { icon: 'check-circle', color: 'text-gray-500' }
                };
                
                const featureData = icons[feature] || { icon: 'check', color: 'text-gray-500' };
                return `<i class="fas fa-${featureData.icon} ${featureData.color} mx-1" title="${feature}"></i>`;
            }).join('');
            
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${trip.departureTime}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${trip.arrivalTime}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${trip.transportType}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${trip.seatsLeft > 5 ? 'green' : 'red'}-100 text-${trip.seatsLeft > 5 ? 'green' : 'red'}-800">
                        ${trip.seatsLeft} مقاعد
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-primary-700">${trip.price}</td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    <div>${trip.description}</div>
                    <div class="mt-1">${featuresIcons}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-primary-600 hover:text-primary-900 mr-2">
                        <i class="fas fa-info-circle"></i>
                    </button>
                    <button class="text-white bg-primary-600 hover:bg-primary-700 px-3 py-1 rounded-md text-sm">
                        <i class="fas fa-ticket-alt mr-1"></i>حجز
                    </button>
                </td>
            `;
            
            return row;
        }
        
        function getCityName(cityCode) {
            const cities = {
                riyadh: 'الرياض',
                jeddah: 'جدة',
                dammam: 'الدمام',
                medina: 'المدينة المنورة',
                taif: 'الطائف'
            };
            return cities[cityCode] || cityCode;
        }
    </script>
</body>
</html>