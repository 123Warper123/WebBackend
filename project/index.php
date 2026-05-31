<?php
require_once __DIR__ . '/config/database.php';
$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToolMaster - Профессиональные инструменты</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-tools me-2"></i>ToolMaster</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Главная</a></li>
                    <li class="nav-item"><a class="nav-link" href="#catalog">Каталог</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Услуги</a></li>
                    <li class="nav-item"><a class="nav-link" href="#testimonials">Отзывы</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contacts">Контакты</a></li>
                </ul>
                <div class="d-flex ms-lg-3 auth-links">
                    <a href="login.php" class="btn btn-primary btn-sm"><i class="fas fa-sign-in-alt"></i> Войти</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Видео секция -->
    <section id="home" class="video-background">
        <div class="video-container">
            <video controls loop autoplay muted>
                <source src="assets/video/main_video.mp4" type="video/mp4">
            </video>
        </div>
    </section>

    <main class="container py-5">
        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-number">5000+</div><div>Товаров</div></div>
            <div class="stat-card"><div class="stat-number">10+</div><div>Лет на рынке</div></div>
            <div class="stat-card"><div class="stat-number">1000+</div><div>Клиентов</div></div>
            <div class="stat-card"><div class="stat-number">24/7</div><div>Поддержка</div></div>
        </div>

        <!-- Каталог -->
        <section id="catalog" class="mb-5">
            <h2 class="text-center mb-4">Популярные инструменты</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow">
                        <div class="product-image"><img src="https://avatars.mds.yandex.net/get-mpic/11910286/2a00000196443fa51aea0987665c7aab5705/orig" class="card-img-top" alt="Дрель"></div>
                        <div class="card-body">
                            <h5 class="card-title">Дрель ударная Makita</h5>
                            <p class="h4 text-primary">12 990 ₽</p>
                            <button class="btn btn-primary w-100">В корзину</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow">
                        <div class="product-image"><img src="https://apelsin.ru/upload/iblock/88d/88d2e60ed4578e1f05b8cea4e27a5fbe.jpg" class="card-img-top" alt="Шуруповерт"></div>
                        <div class="card-body">
                            <h5 class="card-title">Шуруповерт DeWalt</h5>
                            <p class="h4 text-primary">8 490 ₽</p>
                            <button class="btn btn-primary w-100">В корзину</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow">
                        <div class="product-image"><img src="https://avatars.mds.yandex.net/i?id=6cef6805d3a6d0ccfc85facaff759ffd_l-5233258-images-thumbs&n=13" class="card-img-top" alt="Бензопила"></div>
                        <div class="card-body">
                            <h5 class="card-title">Бензопила Husqvarna</h5>
                            <p class="h4 text-primary">24 990 ₽</p>
                            <button class="btn btn-primary w-100">В корзину</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Услуги -->
        <section id="services" class="mb-5">
            <h2 class="text-center mb-4">Наши услуги</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow text-center p-4">
                        <i class="fas fa-wrench fa-3x text-primary mb-3"></i>
                        <h4>Ремонт инструментов</h4>
                        <p>Профессиональный ремонт любой сложности</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow text-center p-4">
                        <i class="fas fa-toolbox fa-3x text-primary mb-3"></i>
                        <h4>Аренда оборудования</h4>
                        <p>Посуточная аренда строительного оборудования</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow text-center p-4">
                        <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                        <h4>Обучение</h4>
                        <p>Мастер-классы по работе с инструментами</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Слайдер отзывов -->
        <section id="testimonials" class="testimonials-section">
            <div class="container">
                <div class="text-center mb-5">
                    <h2>Отзывы наших клиентов</h2>
                    <p class="text-muted">Реальные фотографии и отзывы от профессионалов</p>
                </div>
                
                <div class="testimonials-slider-wrapper">
                    <button class="slider-btn slider-prev"><i class="fas fa-chevron-left"></i></button>
                    <div class="testimonials-slider">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div class="testimonial-slide<?= $i === 1 ? ' active' : '' ?>">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <div class="testimonial-image">
                                        <img src="assets/images/slide<?= $i ?>.jpg" alt="Отзыв <?= $i ?>">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="testimonial-content p-4">
                                        <div class="testimonial-text mb-4">
                                            <?php
                                            $texts = [
                                                '"Отличный магазин! Купил дрель Makita - работает безупречно. Доставка быстрая, сервис на высшем уровне!"',
                                                '"Заказывал садовую технику. Консультант помог выбрать оптимальный вариант. Работает уже второй сезон без нареканий."',
                                                '"Пользуюсь инструментами от ToolMaster уже 3 года. Качество на высоте, сервисный центр работает оперативно. Рекомендую всем профессионалам!"',
                                                '"Ремонтировал у вас перфоратор. Сделали всё качественно и быстро. Цены адекватные, мастера вежливые. Буду обращаться ещё!"',
                                                '"Брала в аренду строительное оборудование на неделю. Всё работает исправно, оформление документов заняло 10 минут. Очень удобно!"'
                                            ];
                                            echo $texts[$i-1];
                                            ?>
                                        </div>
                                        <div class="testimonial-author">
                                            <?php
                                            $authors = ['Иван Петров', 'Олег Николаев', 'Алексей Ковалев', 'Глеб Сасавод', 'Мария Смирнова'];
                                            $professions = ['Профессиональный строитель', 'Владелец загородного дома', 'Частный мастер', 'Ремонтная бригада', 'Дизайнер интерьеров'];
                                            $dates = ['15 января 2024', '8 февраля 2024', '25 марта 2024', '10 апреля 2024', '5 мая 2024'];
                                            ?>
                                            <h4><?= $authors[$i-1] ?></h4>
                                            <p class="text-muted"><?= $professions[$i-1] ?></p>
                                            <div class="testimonial-date"><?= $dates[$i-1] ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                    <button class="slider-btn slider-next"><i class="fas fa-chevron-right"></i></button>
                    
                    <div class="slider-dots">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button class="slider-dot<?= $i === 1 ? ' active' : '' ?>" data-slide="<?= $i-1 ?>"></button>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Форма обратной связи -->
<section id="contacts" class="contact-form">
    <div class="container">
        <div class="form-wrapper">
            <div class="form-content">
                <h2>Остались вопросы?</h2>
                <p>Наши специалисты свяжутся с вами в течение 15 минут</p>

                <form id="contactForm" class="form">
                    <input type="hidden" name="csrf_token" id="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" name="js_enabled" id="js_enabled" value="0">
                    <div style="display:none">
                        <input type="text" name="_gotcha" tabindex="-1" autocomplete="off">
                    </div>

                    <input type="text" name="name" placeholder="Ваше имя" required>
                    <input type="tel" name="phone" placeholder="Ваш телефон" required>
                    <input type="email" name="email" placeholder="Ваш email" required>
                    <textarea name="message" placeholder="Ваше сообщение" rows="4" required></textarea>
                    <div class="char-counter">0/1000 символов</div>

                    <div id="form-message" class="form-message">
                        <?php if (isset($_SESSION['form_errors'])): ?>
                        <div class="error-message">
                            <ul><?php foreach ($_SESSION['form_errors'] as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                        </div>
                        <?php unset($_SESSION['form_errors']); ?>
                        <?php endif; ?>
                    </div>

                    <button type="submit" id="submit-btn" class="btn-primary">
                        <span class="btn-text">Отправить</span>
                        <span class="spinner-border spinner-border-sm" style="display: none;"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
    </main>

    <!-- Модальное окно для пароля -->
<div class="modal fade" id="passwordModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Регистрация успешна!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Ваш аккаунт создан автоматически.</p>
                <div class="alert alert-info">
                    <strong>Email для входа:</strong><br>
                    <code id="modal-email"></code>
                </div>
                <div class="alert alert-warning">
                    <strong>Сгенерированный пароль:</strong><br>
                    <code id="modal-password" style="font-size: 18px; user-select: all;"></code>
                    <div class="small text-muted mt-2">Выделите пароль и нажмите Ctrl+C для копирования</div>
                </div>
                <div class="alert alert-secondary">
                    <i class="fas fa-info-circle"></i>
                    <strong>Важно:</strong> Сохраните пароль в надёжном месте. При утере пароль невозможно восстановить.
                </div>
            </div>
            <div class="modal-footer">
                <a href="/project/profile.php" class="btn btn-primary">Перейти в профиль</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>