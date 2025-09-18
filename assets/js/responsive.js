// ملف JavaScript لتحسين التجاوب والتفاعل

document.addEventListener('DOMContentLoaded', function() {
    
    // تحسين القائمة المحمولة
    initMobileMenu();
    
    // تحسين القوائم المنسدلة للأجهزة المحمولة
    initMobileDropdowns();
    
    // تحسين النماذج للأجهزة المحمولة
    initMobileForms();
    
    // تحسين الصور للتجاوب
    initResponsiveImages();
    
    // تحسين التمرير السلس
    initSmoothScrolling();
    
    // مراقبة تغيير حجم الشاشة
    initResizeHandler();
    
    // تحسين اللمس للأجهزة المحمولة
    initTouchEnhancements();
});

// تهيئة القائمة المحمولة
function initMobileMenu() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuClose = document.getElementById('mobile-menu-close');
    
    if (!mobileMenuButton || !mobileMenu) return;
    
    function openMobileMenu() {
        mobileMenu.classList.add('open');
        mobileMenu.setAttribute('aria-hidden', 'false');
        document.body.classList.add('mobile-menu-open');
        
        // تركيز على أول عنصر قابل للتفاعل
        const firstFocusable = mobileMenu.querySelector('a, button');
        if (firstFocusable) {
            setTimeout(() => firstFocusable.focus(), 100);
        }
    }
    
    function closeMobileMenu() {
        mobileMenu.classList.remove('open');
        mobileMenu.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('mobile-menu-open');
        
        // إعادة التركيز لزر القائمة
        mobileMenuButton.focus();
    }
    
    // فتح القائمة
    mobileMenuButton.addEventListener('click', function(e) {
        e.stopPropagation();
        openMobileMenu();
    });
    
    // إغلاق القائمة
    if (mobileMenuClose) {
        mobileMenuClose.addEventListener('click', closeMobileMenu);
    }
    
    // إغلاق بالضغط على Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenu.classList.contains('open')) {
            closeMobileMenu();
        }
    });
    
    // إغلاق عند النقر خارج القائمة
    document.addEventListener('click', function(event) {
        if (!mobileMenu.contains(event.target) && 
            !mobileMenuButton.contains(event.target) && 
            mobileMenu.classList.contains('open')) {
            closeMobileMenu();
        }
    });
    
    // إغلاق عند تغيير حجم الشاشة للحاسوب
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768 && mobileMenu.classList.contains('open')) {
            closeMobileMenu();
        }
    });
}

// تحسين القوائم المنسدلة للأجهزة المحمولة
function initMobileDropdowns() {
    const dropdowns = document.querySelectorAll('.group');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('button');
        const menu = dropdown.querySelector('.hidden.group-hover\\:block');
        
        if (!trigger || !menu) return;
        
        // للأجهزة المحمولة، استخدم النقر بدلاً من التمرير
        if (window.innerWidth <= 768) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // إغلاق القوائم الأخرى
                document.querySelectorAll('.group .hidden.group-hover\\:block').forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        otherMenu.style.display = 'none';
                    }
                });
                
                // تبديل القائمة الحالية
                if (menu.style.display === 'block') {
                    menu.style.display = 'none';
                } else {
                    menu.style.display = 'block';
                    
                    // تحسين موضع القائمة للأجهزة المحمولة
                    const rect = trigger.getBoundingClientRect();
                    const menuRect = menu.getBoundingClientRect();
                    
                    if (rect.left + menuRect.width > window.innerWidth) {
                        menu.style.left = 'auto';
                        menu.style.right = '0';
                    }
                    
                    if (rect.bottom + menuRect.height > window.innerHeight) {
                        menu.style.top = 'auto';
                        menu.style.bottom = '100%';
                    }
                }
            });
            
            // إغلاق عند النقر خارج القائمة
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });
        }
    });
}

// تحسين النماذج للأجهزة المحمولة
function initMobileForms() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            // منع التكبير في iOS عند التركيز على الحقول
            if (input.type !== 'file') {
                input.style.fontSize = '16px';
            }
            
            // تحسين التركيز للأجهزة المحمولة
            input.addEventListener('focus', function() {
                if (window.innerWidth <= 768) {
                    setTimeout(() => {
                        this.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }, 300);
                }
            });
        });
        
        // تحسين إرسال النماذج
        form.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري الإرسال...';
                
                // إعادة تفعيل الزر بعد 5 ثوان في حالة عدم إعادة تحميل الصفحة
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton.getAttribute('data-original-text') || 'إرسال';
                }, 5000);
            }
        });
    });
}

// تحسين الصور للتجاوب
function initResponsiveImages() {
    const images = document.querySelectorAll('img');
    
    images.forEach(img => {
        // إضافة تحميل كسول للصور
        if (!img.hasAttribute('loading')) {
            img.setAttribute('loading', 'lazy');
        }
        
        // تحسين عرض الصور عند الخطأ
        img.addEventListener('error', function() {
            // منع الحلقة اللا نهائية للطلبات
            if (!this.hasAttribute('data-error-handled')) {
                this.setAttribute('data-error-handled', 'true');
                this.src = 'assets/img/placeholder.svg';
                this.alt = 'صورة غير متوفرة';
            }
        });
        
        // تحسين تحميل الصور
        img.addEventListener('load', function() {
            this.classList.add('loaded');
        });
    });
}

// تحسين التمرير السلس
function initSmoothScrolling() {
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
}

// مراقبة تغيير حجم الشاشة
function initResizeHandler() {
    let resizeTimer;
    
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // إعادة تهيئة العناصر عند تغيير حجم الشاشة
            adjustLayoutForScreenSize();
            
            // إغلاق القوائم المفتوحة عند التغيير للحاسوب
            if (window.innerWidth >= 768) {
                document.querySelectorAll('.group .hidden.group-hover\\:block').forEach(menu => {
                    menu.style.display = '';
                });
            }
        }, 250);
    });
}

// تعديل التخطيط حسب حجم الشاشة
function adjustLayoutForScreenSize() {
    const screenWidth = window.innerWidth;
    
    // تحسين الشبكة حسب حجم الشاشة
    const grids = document.querySelectorAll('.grid');
    grids.forEach(grid => {
        if (screenWidth <= 480) {
            grid.style.gridTemplateColumns = '1fr';
        } else if (screenWidth <= 768) {
            grid.style.gridTemplateColumns = 'repeat(2, 1fr)';
        } else if (screenWidth <= 1024) {
            grid.style.gridTemplateColumns = 'repeat(3, 1fr)';
        }
    });
    
    // تحسين النصوص حسب حجم الشاشة
    const headings = document.querySelectorAll('h1, h2, h3');
    headings.forEach(heading => {
        if (screenWidth <= 480) {
            heading.style.fontSize = '1.25rem';
        } else if (screenWidth <= 768) {
            heading.style.fontSize = '1.5rem';
        }
    });
}

// تحسينات اللمس للأجهزة المحمولة
function initTouchEnhancements() {
    // تحسين النقر للأجهزة المحمولة
    const clickableElements = document.querySelectorAll('button, a, .hover\\:bg-gray-100');
    
    clickableElements.forEach(element => {
        // إضافة تأثير اللمس
        element.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        
        element.addEventListener('touchend', function() {
            this.style.transform = '';
        });
        
        // تحسين حجم منطقة اللمس
        if (window.innerWidth <= 768) {
            const computedStyle = window.getComputedStyle(element);
            const minTouchSize = 44; // الحد الأدنى الموصى به
            
            if (parseInt(computedStyle.height) < minTouchSize) {
                element.style.minHeight = minTouchSize + 'px';
                element.style.display = 'flex';
                element.style.alignItems = 'center';
                element.style.justifyContent = 'center';
            }
        }
    });
}

// وظائف مساعدة للتجاوب
window.ResponsiveUtils = {
    // فحص نوع الجهاز
    isMobile: () => window.innerWidth <= 768,
    isTablet: () => window.innerWidth > 768 && window.innerWidth <= 1024,
    isDesktop: () => window.innerWidth > 1024,
    
    // تحسين عرض المحتوى
    optimizeContent: function() {
        const isMobile = this.isMobile();
        
        // تحسين الصور
        document.querySelectorAll('img').forEach(img => {
            if (isMobile) {
                img.style.maxHeight = '40vh';
                img.style.objectFit = 'cover';
            }
        });
        
        // تحسين البطاقات
        document.querySelectorAll('.rounded-xl').forEach(card => {
            if (isMobile) {
                card.style.borderRadius = '0.5rem';
                card.style.margin = '0.5rem 0';
            }
        });
    },
    
    // تحسين النماذج
    optimizeForms: function() {
        const isMobile = this.isMobile();
        
        document.querySelectorAll('form').forEach(form => {
            if (isMobile) {
                form.style.width = '100%';
                
                form.querySelectorAll('input, select, textarea').forEach(field => {
                    field.style.width = '100%';
                    field.style.fontSize = '16px'; // منع التكبير في iOS
                });
                
                form.querySelectorAll('button').forEach(button => {
                    button.style.width = '100%';
                    button.style.minHeight = '44px';
                });
            }
        });
    }
};

// تشغيل التحسينات عند تحميل الصفحة
window.addEventListener('load', function() {
    window.ResponsiveUtils.optimizeContent();
    window.ResponsiveUtils.optimizeForms();
});

// إعادة تشغيل التحسينات عند تغيير حجم الشاشة
window.addEventListener('resize', function() {
    clearTimeout(window.resizeOptimizeTimer);
    window.resizeOptimizeTimer = setTimeout(function() {
        window.ResponsiveUtils.optimizeContent();
        window.ResponsiveUtils.optimizeForms();
    }, 250);
});

// تحسين الأداء للأجهزة المحمولة
if (window.innerWidth <= 768) {
    // تقليل تأثيرات الحركة للأجهزة المحمولة
    document.documentElement.style.setProperty('--animation-duration', '0.2s');
    
    // تحسين التمرير
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // تحسين اللمس
    document.documentElement.style.touchAction = 'manipulation';
}

// معالجة الأخطاء
window.addEventListener('error', function(e) {
    console.warn('خطأ في التجاوب:', e.message);
});

// تحسين الوصولية
document.addEventListener('keydown', function(e) {
    // تحسين التنقل بالكيبورد
    if (e.key === 'Tab') {
        document.body.classList.add('keyboard-navigation');
    }
});

document.addEventListener('mousedown', function() {
    document.body.classList.remove('keyboard-navigation');
});

// إضافة أنماط CSS للوصولية
const accessibilityStyles = document.createElement('style');
accessibilityStyles.textContent = `
    .keyboard-navigation *:focus {
        outline: 2px solid #8a2be2 !important;
        outline-offset: 2px !important;
    }
    
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
`;
document.head.appendChild(accessibilityStyles);
