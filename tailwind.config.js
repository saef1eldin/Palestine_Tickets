// تكوين Tailwind CSS المحسن للتجاوب
module.exports = {
  content: [
    "./*.php",
    "./includes/*.php",
    "./admin/*.php",
    "./transport/*.php",
    "./assets/js/*.js"
  ],
  theme: {
    extend: {
      // نقاط التوقف المحسنة للأجهزة العربية
      screens: {
        'xs': '375px',    // الهواتف الصغيرة
        'sm': '640px',    // الهواتف الكبيرة
        'md': '768px',    // الأجهزة اللوحية الصغيرة
        'lg': '1024px',   // الأجهزة اللوحية الكبيرة
        'xl': '1280px',   // أجهزة الحاسوب
        '2xl': '1536px',  // الشاشات الكبيرة
        
        // نقاط توقف مخصصة
        'mobile': {'max': '767px'},
        'tablet': {'min': '768px', 'max': '1023px'},
        'desktop': {'min': '1024px'},
        
        // نقاط توقف للاتجاه
        'landscape': {'raw': '(orientation: landscape)'},
        'portrait': {'raw': '(orientation: portrait)'},
        
        // نقاط توقف للكثافة
        'retina': {'raw': '(-webkit-min-device-pixel-ratio: 2)'},
      },
      
      // الألوان المحسنة
      colors: {
        purple: {
          50: '#faf5ff',
          100: '#f3e8ff',
          200: '#e9d5ff',
          300: '#d8b4fe',
          400: '#c084fc',
          500: '#a855f7',
          600: '#9333ea',
          700: '#7c3aed',
          800: '#6b21a8',
          900: '#581c87',
        },
        gray: {
          50: '#f9fafb',
          100: '#f3f4f6',
          200: '#e5e7eb',
          300: '#d1d5db',
          400: '#9ca3af',
          500: '#6b7280',
          600: '#4b5563',
          700: '#374151',
          800: '#1f2937',
          900: '#111827',
        }
      },
      
      // الخطوط المحسنة
      fontFamily: {
        'arabic': ['Tajawal', 'Arial', 'sans-serif'],
        'english': ['Poppins', 'Arial', 'sans-serif'],
        'sans': ['Tajawal', 'Poppins', 'Arial', 'sans-serif'],
      },
      
      // أحجام الخطوط المتجاوبة
      fontSize: {
        'xs': ['0.75rem', { lineHeight: '1rem' }],
        'sm': ['0.875rem', { lineHeight: '1.25rem' }],
        'base': ['1rem', { lineHeight: '1.5rem' }],
        'lg': ['1.125rem', { lineHeight: '1.75rem' }],
        'xl': ['1.25rem', { lineHeight: '1.75rem' }],
        '2xl': ['1.5rem', { lineHeight: '2rem' }],
        '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
        '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
        '5xl': ['3rem', { lineHeight: '1' }],
        '6xl': ['3.75rem', { lineHeight: '1' }],
        
        // أحجام متجاوبة
        'responsive-sm': 'clamp(0.875rem, 2.5vw, 1rem)',
        'responsive-base': 'clamp(1rem, 3vw, 1.125rem)',
        'responsive-lg': 'clamp(1.125rem, 3.5vw, 1.25rem)',
        'responsive-xl': 'clamp(1.25rem, 4vw, 1.5rem)',
        'responsive-2xl': 'clamp(1.5rem, 4.5vw, 2rem)',
        'responsive-3xl': 'clamp(1.875rem, 5vw, 2.5rem)',
        'responsive-4xl': 'clamp(2.25rem, 6vw, 3rem)',
      },
      
      // المسافات المحسنة
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
        
        // مسافات متجاوبة
        'responsive-xs': 'clamp(0.25rem, 1vw, 0.5rem)',
        'responsive-sm': 'clamp(0.5rem, 2vw, 1rem)',
        'responsive-md': 'clamp(1rem, 3vw, 1.5rem)',
        'responsive-lg': 'clamp(1.5rem, 4vw, 2rem)',
        'responsive-xl': 'clamp(2rem, 5vw, 3rem)',
        'responsive-2xl': 'clamp(3rem, 6vw, 4rem)',
      },
      
      // الحد الأقصى للعرض
      maxWidth: {
        'xs': '20rem',
        'sm': '24rem',
        'md': '28rem',
        'lg': '32rem',
        'xl': '36rem',
        '2xl': '42rem',
        '3xl': '48rem',
        '4xl': '56rem',
        '5xl': '64rem',
        '6xl': '72rem',
        '7xl': '80rem',
        'full': '100%',
        'min': 'min-content',
        'max': 'max-content',
        'fit': 'fit-content',
        'prose': '65ch',
        
        // عروض مخصصة للأجهزة المحمولة
        'mobile': '100vw',
        'tablet': '90vw',
        'desktop': '1200px',
      },
      
      // الحد الأدنى للارتفاع
      minHeight: {
        '0': '0',
        'full': '100%',
        'screen': '100vh',
        'touch': '44px', // الحد الأدنى للمس
      },
      
      // نسب العرض إلى الارتفاع
      aspectRatio: {
        auto: 'auto',
        square: '1 / 1',
        video: '16 / 9',
        portrait: '3 / 4',
        landscape: '4 / 3',
        ultrawide: '21 / 9',
      },
      
      // الظلال المحسنة
      boxShadow: {
        'xs': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        'sm': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
        'md': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        'lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
        'xl': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
        '2xl': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
        'inner': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)',
        'none': 'none',
        
        // ظلال مخصصة
        'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
        'card-hover': '0 8px 15px -3px rgba(0, 0, 0, 0.1)',
        'mobile': '0 2px 4px rgba(0, 0, 0, 0.1)',
      },
      
      // الانتقالات المحسنة
      transitionDuration: {
        '75': '75ms',
        '100': '100ms',
        '150': '150ms',
        '200': '200ms',
        '300': '300ms',
        '500': '500ms',
        '700': '700ms',
        '1000': '1000ms',
      },
      
      // منحنيات الانتقال
      transitionTimingFunction: {
        'bounce-in': 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
        'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
        'mobile': 'cubic-bezier(0.25, 0.46, 0.45, 0.94)',
      },
      
      // الحدود المدورة
      borderRadius: {
        'none': '0',
        'sm': '0.125rem',
        'md': '0.375rem',
        'lg': '0.5rem',
        'xl': '0.75rem',
        '2xl': '1rem',
        '3xl': '1.5rem',
        'full': '9999px',
        
        // حدود مخصصة للأجهزة المحمولة
        'mobile': '0.5rem',
        'card': '0.75rem',
      },
      
      // الشبكات المحسنة
      gridTemplateColumns: {
        'auto-fit': 'repeat(auto-fit, minmax(250px, 1fr))',
        'auto-fill': 'repeat(auto-fill, minmax(250px, 1fr))',
        'responsive': 'repeat(auto-fit, minmax(min(100%, 300px), 1fr))',
      },
      
      // الفجوات
      gap: {
        'responsive-sm': 'clamp(0.5rem, 2vw, 1rem)',
        'responsive-md': 'clamp(1rem, 3vw, 1.5rem)',
        'responsive-lg': 'clamp(1.5rem, 4vw, 2rem)',
      },
    },
  },
  plugins: [
    // إضافة فئات مخصصة للتجاوب
    function({ addUtilities, theme }) {
      const newUtilities = {
        // فئات التجاوب المخصصة
        '.text-responsive': {
          fontSize: 'clamp(0.875rem, 2.5vw, 1rem)',
          lineHeight: '1.6',
        },
        '.heading-responsive': {
          fontSize: 'clamp(1.25rem, 4vw, 2rem)',
          lineHeight: '1.3',
        },
        '.title-responsive': {
          fontSize: 'clamp(1.5rem, 5vw, 3rem)',
          lineHeight: '1.2',
        },
        
        // فئات المسافات المتجاوبة
        '.spacing-responsive': {
          padding: 'clamp(1rem, 4vw, 2rem)',
        },
        '.spacing-responsive-sm': {
          padding: 'clamp(0.5rem, 2vw, 1rem)',
        },
        '.spacing-responsive-lg': {
          padding: 'clamp(2rem, 6vw, 4rem)',
        },
        
        // فئات الشبكة المتجاوبة
        '.grid-responsive': {
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fit, minmax(min(100%, 300px), 1fr))',
          gap: 'clamp(1rem, 3vw, 2rem)',
        },
        
        // فئات الأزرار المحسنة للمس
        '.btn-touch': {
          minHeight: '44px',
          minWidth: '44px',
          display: 'inline-flex',
          alignItems: 'center',
          justifyContent: 'center',
          padding: '0.75rem 1.5rem',
          borderRadius: '0.5rem',
          fontWeight: '600',
          textDecoration: 'none',
          transition: 'all 0.2s ease',
          cursor: 'pointer',
          userSelect: 'none',
          WebkitTapHighlightColor: 'transparent',
        },
        
        // فئات البطاقات المتجاوبة
        '.card-responsive': {
          background: 'white',
          borderRadius: '0.75rem',
          boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
          overflow: 'hidden',
          transition: 'all 0.2s ease',
        },
        
        // فئات النماذج المحسنة
        '.form-responsive input, .form-responsive select, .form-responsive textarea': {
          width: '100%',
          padding: '0.75rem',
          border: '1px solid #d1d5db',
          borderRadius: '0.5rem',
          fontSize: '16px',
          transition: 'border-color 0.2s ease',
        },
        
        // فئات الصور المتجاوبة
        '.img-responsive': {
          width: '100%',
          height: 'auto',
          objectFit: 'cover',
          borderRadius: '0.5rem',
        },
        
        // فئات إخفاء التمرير
        '.hide-scrollbar': {
          '-ms-overflow-style': 'none',
          'scrollbar-width': 'none',
        },
        '.hide-scrollbar::-webkit-scrollbar': {
          display: 'none',
        },
        
        // فئات الوصولية
        '.sr-only': {
          position: 'absolute',
          width: '1px',
          height: '1px',
          padding: '0',
          margin: '-1px',
          overflow: 'hidden',
          clip: 'rect(0, 0, 0, 0)',
          whiteSpace: 'nowrap',
          border: '0',
        },
      }
      
      addUtilities(newUtilities)
    },
  ],
  // تحسين الأداء
  corePlugins: {
    preflight: true,
  },
  // متغيرات CSS مخصصة
  experimental: {
    optimizeUniversalDefaults: true,
  },
}
