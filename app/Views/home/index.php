<?php
// app/Views/home/index.php
?>

<!-- =========================================================
     HOME PAGE - VELOX MVC
========================================================= -->


<!-- =========================================================
     HERO
========================================================= -->

<section id="hero" class="velox-hero">

    <div class="hero-grid"></div>

    <div class="hero-glow hero-glow-1"></div>
    <div class="hero-glow hero-glow-2"></div>

    <div class="floating-shape shape-one"></div>
    <div class="floating-shape shape-two"></div>
    <div class="floating-shape shape-three"></div>

    <div class="container position-relative">

        <div class="row align-items-center min-vh-100 py-5">

            <!-- HERO TEXT -->
            <div class="col-lg-7">

                <div
                        class="hero-badge"
                        data-aos="fade-down"
                >
                    <span class="pulse-dot"></span>

                    Ecosistema empresarial de nueva generación

                    <i class="fa-solid fa-arrow-up-right-from-square ms-2"></i>
                </div>

                <h1
                        class="hero-title"
                        data-aos="fade-up"
                        data-aos-delay="100"
                >
                    Convierte la complejidad
                    <span>en velocidad.</span>
                </h1>

                <p
                        class="hero-description"
                        data-aos="fade-up"
                        data-aos-delay="200"
                >
                    Una plataforma diseñada para centralizar operaciones,
                    automatizar procesos y convertir tus datos en decisiones
                    inteligentes.
                </p>

                <div
                        class="hero-actions"
                        data-aos="fade-up"
                        data-aos-delay="300"
                >

                    <a
                            href="#contacto"
                            class="btn btn-hero-primary"
                    >
                        Iniciar proyecto
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a
                            href="#servicios"
                            class="btn btn-hero-secondary"
                    >
                        Explorar plataforma
                        <i class="fa-solid fa-play"></i>
                    </a>

                </div>

                <div
                        class="hero-trust"
                        data-aos="fade-up"
                        data-aos-delay="400"
                >

                    <div class="trust-item">
                        <i class="fa-solid fa-shield-halved"></i>
                        Seguridad empresarial
                    </div>

                    <div class="trust-item">
                        <i class="fa-solid fa-bolt"></i>
                        Alto rendimiento
                    </div>

                    <div class="trust-item">
                        <i class="fa-solid fa-expand"></i>
                        Arquitectura escalable
                    </div>

                </div>

            </div>


            <!-- HERO VISUAL -->
            <div class="col-lg-5 mt-5 mt-lg-0">

                <div
                        class="hero-dashboard"
                        data-aos="zoom-in"
                        data-aos-delay="300"
                >

                    <div class="dashboard-glow"></div>

                    <!-- TOP BAR -->
                    <div class="dashboard-topbar">

                        <div class="dashboard-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>

                        <div class="dashboard-title">
                            <?= htmlspecialchars(
                                    SiteConfig::platformName(),
                                    ENT_QUOTES,
                                    'UTF-8'
                            ) ?>
                        </div>

                        <div class="dashboard-status">
                            <span></span>
                            Live
                        </div>

                    </div>

                    <!-- DASHBOARD -->
                    <div class="dashboard-body">

                        <div class="dashboard-welcome">
                            <small>Resumen operativo</small>

                            <h4>
                                Todo bajo control.
                            </h4>
                        </div>

                        <div class="dashboard-cards">

                            <div class="mini-card">
                                <div class="mini-card-icon">
                                    <i class="fa-solid fa-chart-line"></i>
                                </div>

                                <div>
                                    <small>Rendimiento</small>
                                    <strong>98.7%</strong>
                                </div>

                                <span class="positive">
                                    +12.4%
                                </span>
                            </div>

                            <div class="mini-card">
                                <div class="mini-card-icon dark">
                                    <i class="fa-solid fa-users"></i>
                                </div>

                                <div>
                                    <small>Usuarios</small>
                                    <strong>12,840</strong>
                                </div>

                                <span class="positive">
                                    +8.2%
                                </span>
                            </div>

                            <div class="mini-card">
                                <div class="mini-card-icon purple">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>

                                <div>
                                    <small>Automatizaciones</small>
                                    <strong>1,284</strong>
                                </div>

                                <span class="positive">
                                    +24%
                                </span>
                            </div>

                        </div>

                        <!-- CHART -->
                        <div class="dashboard-chart">

                            <div class="chart-header">
                                <div>
                                    <small>Actividad</small>
                                    <strong>+32.8%</strong>
                                </div>

                                <span>
                                    Últimos 7 días
                                </span>
                            </div>

                            <div class="fake-chart">

                                <div class="chart-line"></div>

                                <div class="chart-bar bar-1"></div>
                                <div class="chart-bar bar-2"></div>
                                <div class="chart-bar bar-3"></div>
                                <div class="chart-bar bar-4"></div>
                                <div class="chart-bar bar-5"></div>
                                <div class="chart-bar bar-6"></div>
                                <div class="chart-bar bar-7"></div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- SCROLL -->
    <a href="#servicios" class="scroll-indicator">
        <span></span>
        Scroll para explorar
    </a>

</section>


<!-- =========================================================
     SERVICIOS
========================================================= -->

<section id="servicios" class="section-light services-section">

    <div class="container">

        <div
                class="section-heading text-center"
                data-aos="fade-up"
        >

            <span class="section-eyebrow">
                <i class="fa-solid fa-layer-group"></i>
                Plataforma
            </span>

            <h2>
                Todo lo que necesitas.
                <span>En un solo ecosistema.</span>
            </h2>

            <p>
                Diseñamos herramientas que eliminan procesos manuales,
                reducen errores y permiten que tu equipo se concentre
                en lo realmente importante.
            </p>

        </div>


        <div class="row g-4 mt-4">

            <!-- CARD -->
            <div
                    class="col-md-6 col-lg-4"
                    data-aos="fade-up"
                    data-aos-delay="100"
            >

                <div class="service-card">

                    <div class="service-number">
                        01
                    </div>

                    <div class="service-icon purple">
                        <i class="fa-solid fa-database"></i>
                    </div>

                    <h4>
                        Gestión inteligente de datos
                    </h4>

                    <p>
                        Centraliza información y procesos mediante una
                        arquitectura optimizada para trabajar con grandes
                        volúmenes de datos.
                    </p>

                    <a href="#contacto">
                        Conocer más
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>


            <!-- CARD -->
            <div
                    class="col-md-6 col-lg-4"
                    data-aos="fade-up"
                    data-aos-delay="200"
            >

                <div class="service-card">

                    <div class="service-number">
                        02
                    </div>

                    <div class="service-icon blue">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <h4>
                        Seguridad empresarial
                    </h4>

                    <p>
                        Control de accesos, sesiones, roles y permisos
                        construidos bajo principios de seguridad desde
                        el diseño.
                    </p>

                    <a href="#contacto">
                        Conocer más
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>


            <!-- CARD -->
            <div
                    class="col-md-6 col-lg-4"
                    data-aos="fade-up"
                    data-aos-delay="300"
            >

                <div class="service-card">

                    <div class="service-number">
                        03
                    </div>

                    <div class="service-icon green">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>

                    <h4>
                        Analytics & métricas
                    </h4>

                    <p>
                        Convierte tus datos en indicadores claros para
                        comprender el comportamiento de tu operación
                        en tiempo real.
                    </p>

                    <a href="#contacto">
                        Conocer más
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>


            <!-- CARD -->
            <div
                    class="col-md-6 col-lg-4"
                    data-aos="fade-up"
                    data-aos-delay="100"
            >

                <div class="service-card">

                    <div class="service-number">
                        04
                    </div>

                    <div class="service-icon orange">
                        <i class="fa-solid fa-gears"></i>
                    </div>

                    <h4>
                        Automatización
                    </h4>

                    <p>
                        Reduce tareas repetitivas y permite que tus
                        procesos evolucionen automáticamente.
                    </p>

                    <a href="#contacto">
                        Conocer más
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>


            <!-- CARD -->
            <div
                    class="col-md-6 col-lg-4"
                    data-aos="fade-up"
                    data-aos-delay="200"
            >

                <div class="service-card">

                    <div class="service-number">
                        05
                    </div>

                    <div class="service-icon cyan">
                        <i class="fa-solid fa-plug"></i>
                    </div>

                    <h4>
                        Integraciones
                    </h4>

                    <p>
                        Conecta APIs, servicios externos y sistemas
                        existentes sin comprometer la arquitectura.
                    </p>

                    <a href="#contacto">
                        Conocer más
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>


            <!-- CARD -->
            <div
                    class="col-md-6 col-lg-4"
                    data-aos="fade-up"
                    data-aos-delay="300"
            >

                <div class="service-card">

                    <div class="service-number">
                        06
                    </div>

                    <div class="service-icon pink">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>

                    <h4>
                        Experiencia responsive
                    </h4>

                    <p>
                        Interfaces diseñadas para funcionar de forma
                        fluida en escritorio, tablet y dispositivos móviles.
                    </p>

                    <a href="#contacto">
                        Conocer más
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     PROPUESTA DE VALOR
========================================================= -->

<section id="nosotros" class="value-section">

    <div class="value-grid"></div>

    <div class="container position-relative">

        <div class="row align-items-center g-5">

            <div
                    class="col-lg-6"
                    data-aos="fade-right"
            >

                <span class="section-eyebrow light">
                    <i class="fa-solid fa-sparkles"></i>
                    Nuestra propuesta
                </span>

                <h2 class="value-title">
                    Menos fricción.
                    <br>
                    Más <span>velocidad.</span>
                </h2>

                <p class="value-description">
                    <?= htmlspecialchars(
                        SiteConfig::platformName(),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?> nace con una idea sencilla: la tecnología debe
                    simplificar la operación, no hacerla más complicada.
                </p>

                <div class="value-list">

                    <div class="value-item">

                        <div class="value-check">
                            <i class="fa-solid fa-check"></i>
                        </div>

                        <div>
                            <strong>Arquitectura desacoplada</strong>

                            <p>
                                Componentes preparados para crecer
                                sin comprometer estabilidad.
                            </p>
                        </div>

                    </div>

                    <div class="value-item">

                        <div class="value-check">
                            <i class="fa-solid fa-check"></i>
                        </div>

                        <div>
                            <strong>Diseño orientado al usuario</strong>

                            <p>
                                Interfaces intuitivas que reducen la
                                curva de aprendizaje.
                            </p>
                        </div>

                    </div>

                    <div class="value-item">

                        <div class="value-check">
                            <i class="fa-solid fa-check"></i>
                        </div>

                        <div>
                            <strong>Preparado para escalar</strong>

                            <p>
                                Una base tecnológica preparada para
                                nuevas funcionalidades.
                            </p>
                        </div>

                    </div>

                </div>

            </div>


            <div
                    class="col-lg-6"
                    data-aos="fade-left"
            >

                <div class="value-visual">

                    <div class="value-orbit orbit-one"></div>
                    <div class="value-orbit orbit-two"></div>

                    <div class="value-center">
                        <i class="fa-solid fa-bolt"></i>
                    </div>

                    <div class="value-floating value-floating-1">
                        <i class="fa-solid fa-database"></i>
                        <span>Data</span>
                    </div>

                    <div class="value-floating value-floating-2">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Security</span>
                    </div>

                    <div class="value-floating value-floating-3">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Analytics</span>
                    </div>

                    <div class="value-floating value-floating-4">
                        <i class="fa-solid fa-gears"></i>
                        <span>Automation</span>
                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     MÉTRICAS
========================================================= -->

<section id="estadisticas" class="metrics-section">

    <div class="container">

        <div
                class="section-heading text-center"
                data-aos="fade-up"
        >

            <span class="section-eyebrow">
                <i class="fa-solid fa-chart-simple"></i>
                Rendimiento
            </span>

            <h2>
                Construido para
                <span>rendir.</span>
            </h2>

        </div>


        <div class="row g-4 mt-4">

            <div
                    class="col-6 col-lg-3"
                    data-aos="zoom-in"
                    data-aos-delay="100"
            >

                <div class="metric-card">

                    <div class="metric-icon">
                        <i class="fa-solid fa-server"></i>
                    </div>

                    <div class="metric-value">
                        <span data-counter="99.9">0</span>%
                    </div>

                    <div class="metric-label">
                        Disponibilidad
                    </div>

                </div>

            </div>


            <div
                    class="col-6 col-lg-3"
                    data-aos="zoom-in"
                    data-aos-delay="200"
            >

                <div class="metric-card">

                    <div class="metric-icon">
                        <i class="fa-solid fa-bolt"></i>
                    </div>

                    <div class="metric-value">
                        <span data-counter="150">0</span>K+
                    </div>

                    <div class="metric-label">
                        Peticiones diarias
                    </div>

                </div>

            </div>


            <div
                    class="col-6 col-lg-3"
                    data-aos="zoom-in"
                    data-aos-delay="300"
            >

                <div class="metric-card">

                    <div class="metric-icon">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>

                    <div class="metric-value">
                        <span data-counter="100">0</span>ms
                    </div>

                    <div class="metric-label">
                        Tiempo de respuesta
                    </div>

                </div>

            </div>


            <div
                    class="col-6 col-lg-3"
                    data-aos="zoom-in"
                    data-aos-delay="400"
            >

                <div class="metric-card">

                    <div class="metric-icon">
                        <i class="fa-solid fa-lock"></i>
                    </div>

                    <div class="metric-value">
                        <span data-counter="100">0</span>%
                    </div>

                    <div class="metric-label">
                        Procesos protegidos
                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     TESTIMONIOS
========================================================= -->

<section id="testimonios" class="testimonials-section">

    <div class="container">

        <div
                class="section-heading text-center"
                data-aos="fade-up"
        >

            <span class="section-eyebrow">
                <i class="fa-solid fa-message"></i>
                Experiencias
            </span>

            <h2>
                Diseñado para
                <span>personas.</span>
            </h2>

            <p>
                Una buena plataforma no solo debe funcionar.
                También debe sentirse bien al utilizarla.
            </p>

        </div>


        <div class="row g-4 mt-4">

            <div
                    class="col-lg-6"
                    data-aos="fade-up"
                    data-aos-delay="100"
            >

                <div class="testimonial-card">

                    <div class="testimonial-stars">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>

                    <blockquote>
                        “La velocidad del panel y la claridad de la
                        arquitectura nos permitieron desplegar nuestros
                        módulos en tiempo récord.”
                    </blockquote>

                    <div class="testimonial-author">

                        <div class="author-avatar">
                            CD
                        </div>

                        <div>
                            <strong>
                                Carlos Dev
                            </strong>

                            <small>
                                Lead Developer
                            </small>
                        </div>

                    </div>

                </div>

            </div>


            <div
                    class="col-lg-6"
                    data-aos="fade-up"
                    data-aos-delay="200"
            >

                <div class="testimonial-card">

                    <div class="testimonial-stars">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>

                    <blockquote>
                        “El control de sesiones y las alertas estandarizadas
                        hacen que el uso diario sea mucho más fluido
                        y confiable.”
                    </blockquote>

                    <div class="testimonial-author">

                        <div class="author-avatar purple">
                            AG
                        </div>

                        <div>
                            <strong>
                                Ana Gómez
                            </strong>

                            <small>
                                Admin Operations
                            </small>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     CTA
========================================================= -->

<section class="cta-section">

    <div class="cta-glow"></div>

    <div class="container position-relative">

        <div
                class="cta-card"
                data-aos="zoom-in"
        >

            <div class="cta-content">

                <span class="section-eyebrow light">
                    <i class="fa-solid fa-rocket"></i>
                    Comienza ahora
                </span>

                <h2>
                    Tu próxima gran operación
                    <span>comienza aquí.</span>
                </h2>

                <p>
                    Cuéntanos qué necesitas y construyamos juntos
                    una solución preparada para crecer.
                </p>

                <a
                        href="#contacto"
                        class="btn btn-hero-primary"
                >
                    Hablar con nosotros
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     CONTACTO
========================================================= -->

<section id="contacto" class="contact-section">

    <div class="container">

        <div
                class="section-heading text-center"
                data-aos="fade-up"
        >

            <span class="section-eyebrow">
                <i class="fa-solid fa-paper-plane"></i>
                Contacto
            </span>

            <h2>
                Hablemos de tu
                <span>proyecto.</span>
            </h2>

            <p>
                Estamos listos para ayudarte a transformar tus procesos.
            </p>

        </div>


        <div class="row g-5 mt-3 align-items-stretch">

            <!-- INFO -->
            <div
                    class="col-lg-5"
                    data-aos="fade-right"
            >

                <div class="contact-info">

                    <h3>
                        Construyamos algo
                        <span>extraordinario.</span>
                    </h3>

                    <p>
                        Escríbenos y cuéntanos qué problema quieres
                        solucionar. Nuestro equipo estará encantado
                        de escucharte.
                    </p>


                    <div class="contact-detail">

                        <div class="contact-icon">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>

                        <div>
                            <small>Ubicación</small>
                            <strong>
                                Bogotá, Colombia
                            </strong>
                        </div>

                    </div>


                    <div class="contact-detail">

                        <div class="contact-icon">
                            <i class="fa-solid fa-envelope"></i>
                        </div>

                        <div>
                            <small>Correo</small>
                            <strong>
                                contacto@veloxmvc.com
                            </strong>
                        </div>

                    </div>


                    <div class="contact-detail">

                        <div class="contact-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>

                        <div>
                            <small>Atención</small>
                            <strong>
                                Lunes — Viernes
                            </strong>
                        </div>

                    </div>


                    <div class="contact-socials">

                        <a href="#">
                            <i class="fa-brands fa-github"></i>
                        </a>

                        <a href="#">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>

                        <a href="#">
                            <i class="fa-brands fa-x-twitter"></i>
                        </a>

                        <a href="#">
                            <i class="fa-brands fa-instagram"></i>
                        </a>

                    </div>

                </div>

            </div>


            <!-- FORM -->
            <div
                    class="col-lg-7"
                    data-aos="fade-left"
            >

                <form
                        id="contactForm"
                        class="contact-form"
                >

                    <div class="row g-4">

                        <div class="col-md-6">

                            <label for="contactName">
                                Nombre completo
                            </label>

                            <div class="input-wrapper">

                                <i class="fa-regular fa-user"></i>

                                <input
                                        type="text"
                                        id="contactName"
                                        placeholder="Tu nombre"
                                        required
                                >

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label for="contactEmail">
                                Correo electrónico
                            </label>

                            <div class="input-wrapper">

                                <i class="fa-regular fa-envelope"></i>

                                <input
                                        type="email"
                                        id="contactEmail"
                                        placeholder="tu@correo.com"
                                        required
                                >

                            </div>

                        </div>


                        <div class="col-12">

                            <label for="contactSubject">
                                Asunto
                            </label>

                            <div class="input-wrapper">

                                <i class="fa-regular fa-comment"></i>

                                <input
                                        type="text"
                                        id="contactSubject"
                                        placeholder="¿En qué podemos ayudarte?"
                                        required
                                >

                            </div>

                        </div>


                        <div class="col-12">

                            <label for="contactMessage">
                                Mensaje
                            </label>

                            <textarea
                                    id="contactMessage"
                                    rows="5"
                                    placeholder="Cuéntanos sobre tu proyecto..."
                                    required
                            ></textarea>

                        </div>


                        <div class="col-12">

                            <button
                                    type="submit"
                                    class="btn btn-submit"
                            >
                                Enviar mensaje

                                <i class="fa-solid fa-arrow-right"></i>

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="velox-footer">

    <div class="container">

        <div class="footer-main">

            <div>

                <a
                        href="<?= APP_URL ?>"
                        class="velox-brand footer-brand"
                >

                    <span class="brand-mark">
                        <?php $homeLogo = SiteConfig::siteLogo(); ?>
                        <?php if ($homeLogo !== ''): ?>
                            <img
                                    src="<?= htmlspecialchars(rtrim(APP_URL, '/') . '/assets/img/settings/' . rawurlencode($homeLogo), ENT_QUOTES, 'UTF-8') ?>"
                                    alt="<?= htmlspecialchars(SiteConfig::siteName(), ENT_QUOTES, 'UTF-8') ?>"
                                    class="brand-logo"
                            >
                        <?php else: ?>
                            <i class="fa-solid fa-bolt"></i>
                        <?php endif; ?>
                    </span>

                    <span>
                        <?= htmlspecialchars(
                                SiteConfig::siteName(),
                                ENT_QUOTES,
                                'UTF-8'
                        ) ?>
                    </span>

                </a>

            </div>


            <div class="footer-links">

                <a href="#servicios">
                    Servicios
                </a>

                <a href="#nosotros">
                    Propuesta
                </a>

                <a href="#estadisticas">
                    Métricas
                </a>

                <a href="#contacto">
                    Contacto
                </a>

                <a href="<?= APP_URL ?>/login">
                    Acceso
                </a>

            </div>

        </div>


        <div class="footer-bottom">

            <span>
                <?= htmlspecialchars(
                        SiteConfig::siteName(),
                        ENT_QUOTES,
                        'UTF-8'
                ) ?>
                &copy; <?= date('Y') ?>
            </span>

            <span>
                Construido con tecnología y propósito.
            </span>

        </div>

    </div>

</footer>