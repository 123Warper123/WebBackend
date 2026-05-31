// assets/js/main.js - обновлённый слайдер с ленивой загрузкой
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded');

    // ============================================
    // СЛАЙДЕР ОТЗЫВОВ
    // ============================================
    let currentSlide = 0;
    const slides = document.querySelectorAll('.testimonial-slide');
    const dots = document.querySelectorAll('.slider-dot');
    const prevBtn = document.querySelector('.slider-prev');
    const nextBtn = document.querySelector('.slider-next');

    // Функция для предзагрузки изображения
    function preloadImage(imgElement) {
        if (!imgElement) return;
        const src = imgElement.getAttribute('data-src');
        if (src && !imgElement.src) {
            imgElement.src = src;
            imgElement.removeAttribute('data-src');
        }
    }

    // Загружаем изображения для активного слайда и соседних
    function loadNearbyImages() {
        // Загружаем текущий, предыдущий и следующий слайды
        const indexes = [
            currentSlide,
            (currentSlide - 1 + slides.length) % slides.length,
            (currentSlide + 1) % slides.length
        ];

        indexes.forEach(index => {
            const slide = slides[index];
            if (slide) {
                const img = slide.querySelector('img');
                if (img && img.getAttribute('data-src')) {
                    preloadImage(img);
                }
            }
        });
    }

    function showSlide(index) {
        if (!slides.length) return;
        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;

        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));

        slides[index].classList.add('active');
        if (dots[index]) dots[index].classList.add('active');
        currentSlide = index;

        // Загружаем изображения для отображаемых слайдов
        loadNearbyImages();
    }

    // Функция плавной прокрутки для кнопок
    function smoothTransition() {
        const activeSlide = slides[currentSlide];
        if (activeSlide) {
            activeSlide.style.opacity = '0';
            setTimeout(() => {
                activeSlide.style.opacity = '1';
            }, 50);
        }
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            showSlide(currentSlide - 1);
            smoothTransition();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            showSlide(currentSlide + 1);
            smoothTransition();
        });
    }

    if (dots.length) {
        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                showSlide(i);
                smoothTransition();
            });
        });
    }

    // Автопрокрутка каждые 5 секунд
    let autoPlayInterval;

    function startAutoPlay() {
        if (autoPlayInterval) clearInterval(autoPlayInterval);
        autoPlayInterval = setInterval(() => {
            showSlide(currentSlide + 1);
        }, 5000);
    }

    function stopAutoPlay() {
        if (autoPlayInterval) clearInterval(autoPlayInterval);
    }

    startAutoPlay();

    // Останавливаем автопрокрутку при наведении на слайдер
    const sliderWrapper = document.querySelector('.testimonials-slider-wrapper');
    if (sliderWrapper) {
        sliderWrapper.addEventListener('mouseenter', stopAutoPlay);
        sliderWrapper.addEventListener('mouseleave', startAutoPlay);
    }

    // Загружаем первый слайд
    loadNearbyImages();

    // ============================================
    // ФОРМА ОБРАТНОЙ СВЯЗИ
    // ============================================
    const API_URL = '/project/api.php';

    const jsEnabledField = document.getElementById('js_enabled');
    if (jsEnabledField) jsEnabledField.value = '1';

    let generatedPassword = '';

    fetch(API_URL + '?action=csrf', { credentials: 'include' })
        .then(res => res.json())
        .then(data => {
            const csrfInput = document.getElementById('csrf_token');
            if (csrfInput) csrfInput.value = data.csrf_token;
            console.log('CSRF token loaded');
        })
        .catch(err => console.error('CSRF error:', err));

    const textarea = document.querySelector('textarea[name="message"]');
    const charCounter = document.querySelector('.char-counter');
    if (textarea && charCounter) {
        textarea.addEventListener('input', function () {
            const length = this.value.length;
            charCounter.textContent = length + '/1000 символов';
            if (length > 950) charCounter.style.color = 'orange';
            if (length > 990) charCounter.style.color = 'red';
        });
    }

    const form = document.getElementById('contactForm');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submit-btn');
            const btnText = submitBtn.querySelector('.btn-text');
            const spinner = submitBtn.querySelector('.spinner-border');
            const formMessage = document.getElementById('form-message');

            btnText.style.display = 'none';
            spinner.style.display = 'inline-block';
            submitBtn.disabled = true;
            formMessage.innerHTML = '';

            const formData = new FormData(form);
            const data = {
                name: formData.get('name'),
                phone: formData.get('phone'),
                email: formData.get('email'),
                message: formData.get('message'),
                csrf_token: document.getElementById('csrf_token').value,
                js_enabled: 1
            };

            try {
                const response = await fetch(API_URL + '?action=feedback', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                    credentials: 'include'
                });

                const result = await response.json();

                if (result.success) {
                    if (result.login && result.password) {
                        generatedPassword = result.password;
                        document.getElementById('modal-email').textContent = result.login;
                        document.getElementById('modal-password').textContent = generatedPassword;
                        const passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
                        passwordModal.show();
                    } else {
                        formMessage.innerHTML = '<div class="success-message">✅ ' + result.message + '</div>';
                        form.reset();
                        if (charCounter) charCounter.textContent = '0/1000 символов';
                        setTimeout(() => {
                            formMessage.innerHTML = '';
                        }, 5000);
                    }
                } else if (result.errors) {
                    let html = '<div class="error-message"><ul>';
                    for (const [field, error] of Object.entries(result.errors)) {
                        html += `<li><strong>${field}:</strong> ${error}</li>`;
                    }
                    html += '</ul></div>';
                    formMessage.innerHTML = html;
                } else {
                    formMessage.innerHTML = '<div class="error-message">❌ ' + (result.error || 'Ошибка отправки') + '</div>';
                }
            } catch (error) {
                console.error('Fetch error:', error);
                formMessage.innerHTML = '<div class="error-message">❌ Ошибка соединения. Попробуйте позже.</div>';
            } finally {
                btnText.style.display = 'inline-block';
                spinner.style.display = 'none';
                submitBtn.disabled = false;
            }
        });
    }

    async function updateAuthUI() {
        try {
            const response = await fetch(API_URL + '?action=check', { credentials: 'include' });
            const result = await response.json();

            const authLinks = document.querySelector('.auth-links');
            if (result.authenticated && authLinks) {
                authLinks.innerHTML = `
                    <a href="/project/profile.php" class="btn btn-outline-light btn-sm me-2">
                        <i class="fas fa-user"></i> ${escapeHtml(result.user?.name || result.user?.email)}
                    </a>
                    <button id="logout-btn" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </button>
                `;
                document.getElementById('logout-btn')?.addEventListener('click', async () => {
                    await fetch(API_URL + '?action=logout', { method: 'POST', credentials: 'include' });
                    window.location.href = '/project/index.php';
                });
            }
        } catch (error) {
            console.error('Auth check failed:', error);
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function (m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    updateAuthUI();
});