// Service Worker لتحسين الأداء والتخزين المؤقت
const CACHE_NAME = 'tickets-palestine-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/assets/css/responsive.css',
  '/assets/css/performance.css',
  '/assets/js/main.js',
  '/assets/js/responsive.js',
  '/assets/img/tickets-hero-palestine.png',
  '/assets/img/tickets-search.svg',
  '/assets/img/placeholder.svg',
  'https://cdn.tailwindcss.com',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
  'https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&family=Poppins:wght@400;500;600;700&display=swap'
];

// تثبيت Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('فتح التخزين المؤقت');
        return cache.addAll(urlsToCache);
      })
  );
});

// تفعيل Service Worker
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('حذف التخزين المؤقت القديم:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// اعتراض الطلبات
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // إرجاع الملف من التخزين المؤقت إذا كان موجوداً
        if (response) {
          return response;
        }

        return fetch(event.request).then(response => {
          // التحقق من صحة الاستجابة
          if (!response || response.status !== 200 || response.type !== 'basic') {
            return response;
          }

          // نسخ الاستجابة
          const responseToCache = response.clone();

          caches.open(CACHE_NAME)
            .then(cache => {
              cache.put(event.request, responseToCache);
            });

          return response;
        }).catch(() => {
          // في حالة عدم توفر الاتصال، إرجاع صفحة offline
          if (event.request.destination === 'document') {
            return caches.match('/offline.html');
          }
          
          // للصور، إرجاع صورة placeholder
          if (event.request.destination === 'image') {
            return caches.match('/assets/img/placeholder.svg');
          }
        });
      })
  );
});

// معالجة الرسائل من الصفحة الرئيسية
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

// تحديث التخزين المؤقت في الخلفية
self.addEventListener('sync', event => {
  if (event.tag === 'background-sync') {
    event.waitUntil(doBackgroundSync());
  }
});

function doBackgroundSync() {
  return fetch('/api/sync')
    .then(response => response.json())
    .then(data => {
      console.log('تم تحديث البيانات في الخلفية');
    })
    .catch(error => {
      console.log('فشل التحديث في الخلفية:', error);
    });
}

// إشعارات push
self.addEventListener('push', event => {
  const options = {
    body: event.data ? event.data.text() : 'إشعار جديد من تذاكر فلسطين',
    icon: '/assets/img/tickets-icon.png',
    badge: '/assets/img/tickets-icon.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'استكشف الفعاليات',
        icon: '/assets/img/tickets-icon.png'
      },
      {
        action: 'close',
        title: 'إغلاق',
        icon: '/assets/img/tickets-icon.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('تذاكر فلسطين', options)
  );
});

// معالجة النقر على الإشعارات
self.addEventListener('notificationclick', event => {
  event.notification.close();

  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/events.php')
    );
  } else if (event.action === 'close') {
    // لا حاجة لفعل شيء، الإشعار مغلق بالفعل
  } else {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});

// تحسين الأداء للأجهزة المحمولة
self.addEventListener('fetch', event => {
  // ضغط الاستجابات للأجهزة المحمولة
  if (event.request.headers.get('User-Agent').includes('Mobile')) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          if (response.headers.get('Content-Type')?.includes('text/html')) {
            return response.text().then(html => {
              // تحسين HTML للأجهزة المحمولة
              const optimizedHtml = html
                .replace(/\s+/g, ' ') // إزالة المسافات الزائدة
                .replace(/<!--[\s\S]*?-->/g, ''); // إزالة التعليقات
              
              return new Response(optimizedHtml, {
                status: response.status,
                statusText: response.statusText,
                headers: response.headers
              });
            });
          }
          return response;
        })
        .catch(() => caches.match(event.request))
    );
  }
});

// تنظيف التخزين المؤقت القديم
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// معالجة الأخطاء
self.addEventListener('error', event => {
  console.error('خطأ في Service Worker:', event.error);
});

self.addEventListener('unhandledrejection', event => {
  console.error('Promise مرفوض في Service Worker:', event.reason);
  event.preventDefault();
});

// تحديث دوري للتخزين المؤقت
setInterval(() => {
  caches.open(CACHE_NAME).then(cache => {
    cache.keys().then(requests => {
      requests.forEach(request => {
        fetch(request).then(response => {
          if (response.status === 200) {
            cache.put(request, response);
          }
        }).catch(() => {
          // تجاهل الأخطاء في التحديث الدوري
        });
      });
    });
  });
}, 60000 * 30); // كل 30 دقيقة

console.log('Service Worker تم تحميله بنجاح');
