// تهيئة المكونات عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => {
  // تهيئة تأثيرات التمرير السلس
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      document.querySelector(this.getAttribute('href')).scrollIntoView({
        behavior: 'smooth'
      });
    });
  });

  // التحميل الكسول للصور
  const lazyImages = document.querySelectorAll('img[data-src]');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.classList.add('fade-in');
        observer.unobserve(img);
      }
    });
  }, { rootMargin: '100px' });

  lazyImages.forEach(img => observer.observe(img));

  // التحقق من النماذب
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const requiredFields = this.querySelectorAll('[required]');
      let isValid = true;

      requiredFields.forEach(field => {
        if (!field.value.trim()) {
          isValid = false;
          field.classList.add('is-invalid');
          field.nextElementSibling?.classList.remove('d-none');
        }
      });

      if (!isValid) e.preventDefault();
    });
  });

  // عرض الإشعارات
  window.showToast = (message, type = 'success') => {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">
          ${message}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `;
    
    document.querySelector('#toast-container').appendChild(toast);
    new bootstrap.Toast(toast, { autohide: true }).show();
  };
});

// تأثيرات الحركة عند التمرير
window.addEventListener('scroll', () => {
  document.querySelectorAll('.animate-on-scroll').forEach(element => {
    const elementTop = element.getBoundingClientRect().top;
    const elementBottom = element.getBoundingClientRect().bottom;
    
    if (elementTop < window.innerHeight && elementBottom > 0) {
      element.classList.add('animated');
    }
  });
});