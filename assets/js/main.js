// تهيئة المكونات عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => {
  // تهيئة تأثيرات التمرير السلس
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        const headerHeight = document.querySelector('header')?.offsetHeight || 0;
        const targetPosition = target.offsetTop - headerHeight - 20;

        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });
      }
    });
  });

  // التحميل الكسول للصور المحسن
  const lazyImages = document.querySelectorAll('img[data-src], img[loading="lazy"]');
  const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;

        // للصور مع data-src
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
        }

        // إضافة فئة التحميل
        img.classList.add('loading');

        // عند اكتمال التحميل
        img.addEventListener('load', function() {
          this.classList.remove('loading');
          this.classList.add('loaded');
        });

        // معالجة أخطاء التحميل
        img.addEventListener('error', function() {
          this.src = 'assets/img/placeholder.svg';
          this.alt = 'صورة غير متوفرة';
          this.classList.remove('loading');
          this.classList.add('loaded');
        });

        imageObserver.unobserve(img);
      }
    });
  }, { rootMargin: '50px' });

  lazyImages.forEach(img => imageObserver.observe(img));

  // التحقق من النماذج المحسن
  document.querySelectorAll('form').forEach(form => {
    // تحسين حقول الإدخال للأجهزة المحمولة
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      // منع التكبير في iOS
      if (input.type !== 'file') {
        input.style.fontSize = '16px';
      }

      // تحسين التركيز
      input.addEventListener('focus', function() {
        this.classList.add('focused');

        // تمرير سلس للحقل على الأجهزة المحمولة
        if (window.innerWidth <= 768) {
          setTimeout(() => {
            this.scrollIntoView({
              behavior: 'smooth',
              block: 'center'
            });
          }, 300);
        }
      });

      input.addEventListener('blur', function() {
        this.classList.remove('focused');
      });
    });

    // التحقق من صحة النموذج
    form.addEventListener('submit', function(e) {
      const requiredFields = this.querySelectorAll('[required]');
      let isValid = true;
      let firstInvalidField = null;

      requiredFields.forEach(field => {
        field.classList.remove('is-invalid');

        if (!field.value.trim()) {
          isValid = false;
          field.classList.add('is-invalid');

          if (!firstInvalidField) {
            firstInvalidField = field;
          }
        }
      });

      if (!isValid) {
        e.preventDefault();

        // التركيز على أول حقل غير صحيح
        if (firstInvalidField) {
          firstInvalidField.focus();
          firstInvalidField.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
        }

        // عرض رسالة خطأ
        showToast('يرجى ملء جميع الحقول المطلوبة', 'error');
      } else {
        // تعطيل زر الإرسال لمنع الإرسال المتكرر
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) {
          submitButton.disabled = true;
          const originalText = submitButton.textContent;
          submitButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري الإرسال...';

          // إعادة تفعيل الزر بعد 5 ثوان
          setTimeout(() => {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
          }, 5000);
        }
      }
    });
  });

  // عرض الإشعارات المحسن
  window.showToast = (message, type = 'success', duration = 5000) => {
    // إنشاء حاوية الإشعارات إذا لم تكن موجودة
    let toastContainer = document.querySelector('#toast-container');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'toast-container';
      toastContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
      document.body.appendChild(toastContainer);
    }

    // تحديد الألوان والأيقونات حسب النوع
    const typeConfig = {
      success: { bg: 'bg-green-500', icon: 'fas fa-check-circle' },
      error: { bg: 'bg-red-500', icon: 'fas fa-exclamation-circle' },
      warning: { bg: 'bg-yellow-500', icon: 'fas fa-exclamation-triangle' },
      info: { bg: 'bg-blue-500', icon: 'fas fa-info-circle' }
    };

    const config = typeConfig[type] || typeConfig.success;

    // إنشاء الإشعار
    const toast = document.createElement('div');
    toast.className = `${config.bg} text-white px-4 py-3 rounded-lg shadow-lg flex items-center space-x-3 space-x-reverse transform translate-x-full transition-transform duration-300 max-w-sm`;
    toast.innerHTML = `
      <i class="${config.icon} flex-shrink-0"></i>
      <span class="flex-1 text-sm font-medium">${message}</span>
      <button type="button" class="flex-shrink-0 text-white hover:text-gray-200 transition-colors" onclick="this.parentElement.remove()">
        <i class="fas fa-times"></i>
      </button>
    `;

    // إضافة الإشعار للحاوية
    toastContainer.appendChild(toast);

    // تأثير الظهور
    setTimeout(() => {
      toast.classList.remove('translate-x-full');
    }, 100);

    // إزالة الإشعار تلقائياً
    setTimeout(() => {
      toast.classList.add('translate-x-full');
      setTimeout(() => {
        if (toast.parentElement) {
          toast.remove();
        }
      }, 300);
    }, duration);

    // إزالة الإشعار عند النقر عليه
    toast.addEventListener('click', function() {
      this.classList.add('translate-x-full');
      setTimeout(() => {
        if (this.parentElement) {
          this.remove();
        }
      }, 300);
    });
  };
});

// تأثيرات الحركة عند التمرير المحسنة
let scrollTimeout;
window.addEventListener('scroll', () => {
  // تحسين الأداء بتقليل عدد استدعاءات الدالة
  clearTimeout(scrollTimeout);
  scrollTimeout = setTimeout(() => {
    document.querySelectorAll('.animate-on-scroll').forEach(element => {
      const elementTop = element.getBoundingClientRect().top;
      const elementBottom = element.getBoundingClientRect().bottom;

      if (elementTop < window.innerHeight && elementBottom > 0) {
        element.classList.add('animated');
      }
    });
  }, 10);
});

// تحسينات إضافية للأداء والتجاوب
document.addEventListener('DOMContentLoaded', () => {
  // تحسين الأداء للأجهزة المحمولة
  if (window.innerWidth <= 768) {
    // تقليل تأثيرات الحركة
    document.documentElement.style.setProperty('--animation-duration', '0.2s');

    // تحسين التمرير
    document.documentElement.style.scrollBehavior = 'smooth';

    // تحسين اللمس
    document.documentElement.style.touchAction = 'manipulation';
  }

  // تحسين تحميل الخطوط
  if ('fonts' in document) {
    document.fonts.ready.then(() => {
      document.body.classList.add('fonts-loaded');
    });
  }

  // تحسين الصور
  document.querySelectorAll('img').forEach(img => {
    // إضافة تحميل كسول إذا لم يكن موجوداً
    if (!img.hasAttribute('loading')) {
      img.setAttribute('loading', 'lazy');
    }

    // تحسين عرض الصور عند الخطأ
    img.addEventListener('error', function() {
      if (this.src !== 'assets/img/placeholder.svg') {
        this.src = 'assets/img/placeholder.svg';
        this.alt = 'صورة غير متوفرة';
      }
    });
  });

  // تحسين الأزرار للمس
  document.querySelectorAll('button, a, .hover\\:bg-gray-100').forEach(element => {
    // إضافة تأثير اللمس
    element.addEventListener('touchstart', function() {
      this.style.transform = 'scale(0.98)';
    });

    element.addEventListener('touchend', function() {
      this.style.transform = '';
    });

    // تحسين حجم منطقة اللمس للأجهزة المحمولة
    if (window.innerWidth <= 768) {
      const computedStyle = window.getComputedStyle(element);
      const minTouchSize = 44;

      if (parseInt(computedStyle.height) < minTouchSize) {
        element.style.minHeight = minTouchSize + 'px';
        element.style.display = 'flex';
        element.style.alignItems = 'center';
        element.style.justifyContent = 'center';
      }
    }
  });
});

// مراقبة تغيير حجم الشاشة
let resizeTimeout;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(() => {
    // إعادة تهيئة العناصر عند تغيير حجم الشاشة
    const isMobile = window.innerWidth <= 768;

    // تحديث أنماط CSS حسب حجم الشاشة
    if (isMobile) {
      document.documentElement.style.setProperty('--animation-duration', '0.2s');
    } else {
      document.documentElement.style.setProperty('--animation-duration', '0.3s');
    }

    // إعادة حساب أحجام العناصر
    document.querySelectorAll('.card-responsive').forEach(card => {
      if (isMobile) {
        card.style.borderRadius = '0.5rem';
        card.style.margin = '0.5rem 0';
      } else {
        card.style.borderRadius = '';
        card.style.margin = '';
      }
    });
  }, 250);
});

// تحسين الوصولية
document.addEventListener('keydown', function(e) {
  if (e.key === 'Tab') {
    document.body.classList.add('keyboard-navigation');
  }
});

document.addEventListener('mousedown', function() {
  document.body.classList.remove('keyboard-navigation');
});

// معالجة الأخطاء
window.addEventListener('error', function(e) {
  console.warn('خطأ في JavaScript:', e.message);

  // عرض رسالة خطأ للمستخدم في حالة الأخطاء الحرجة
  if (typeof showToast === 'function') {
    showToast('حدث خطأ غير متوقع. يرجى إعادة تحميل الصفحة.', 'error');
  }
});

// تحسين الأداء العام
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(registration => {
        console.log('Service Worker مسجل بنجاح');
      })
      .catch(error => {
        console.log('فشل تسجيل Service Worker');
      });
  });
}

// إضافة أنماط CSS للوصولية
const accessibilityStyles = document.createElement('style');
accessibilityStyles.textContent = `
  .keyboard-navigation *:focus {
    outline: 2px solid #8a2be2 !important;
    outline-offset: 2px !important;
  }

  .fonts-loaded {
    font-display: swap;
  }

  @media (prefers-reduced-motion: reduce) {
    * {
      animation-duration: 0.01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.01ms !important;
    }
  }

  .loading {
    opacity: 0.5;
    transition: opacity 0.3s ease;
  }

  .loaded {
    opacity: 1;
  }

  .focused {
    box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.1) !important;
  }

  .is-invalid {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
  }
`;
document.head.appendChild(accessibilityStyles);