<!-- app/Views/auth/login.php -->

<div class="velox-login-page">

    <!-- Fondo decorativo -->
    <div class="login-background">
        <div class="bg-orb bg-orb-1"></div>
        <div class="bg-orb bg-orb-2"></div>
        <div class="bg-orb bg-orb-3"></div>
        <div class="bg-grid"></div>
    </div>

    <div class="container-fluid login-container">

        <div class="login-card">

            <!-- =====================================================
                 PANEL IZQUIERDO
            ====================================================== -->
            <div class="login-showcase">

                <div class="showcase-glow"></div>

                <div class="showcase-content">

                    <!-- Brand -->
                    <div class="login-brand">
                        <div class="brand-icon">
                            <?php $loginLogo = SiteConfig::siteLogo(); ?>
                            <?php if ($loginLogo !== ''): ?>
                                <img
                                        src="<?= htmlspecialchars(rtrim(APP_URL, '/') . '/assets/img/settings/' . rawurlencode($loginLogo), ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars(SiteConfig::siteName(), ENT_QUOTES, 'UTF-8') ?>"
                                        class="brand-logo"
                                >
                            <?php else: ?>
                                <span>V</span>
                            <?php endif; ?>
                        </div>

                        <div class="brand-text">
                            <span class="brand-name">
                                <?= htmlspecialchars(
                                        SiteConfig::siteName(),
                                        ENT_QUOTES,
                                        'UTF-8'
                                ) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Hero -->
                    <div class="showcase-main">

                        <span class="showcase-badge">
                            <span class="badge-dot"></span>
                            PLATAFORMA ENTERPRISE
                        </span>

                        <h1>
                            Gestiona tu operación.
                            <span>Impulsa tu negocio.</span>
                        </h1>

                        <p>
                            Una plataforma moderna diseñada para centralizar,
                            automatizar y controlar tus procesos empresariales
                            desde un único lugar.
                        </p>

                        <!-- Features -->
                        <div class="showcase-features">

                            <div class="showcase-feature">
                                <div class="feature-icon">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>

                                <div>
                                    <strong>Alto rendimiento</strong>
                                    <span>Procesos rápidos y eficientes</span>
                                </div>
                            </div>

                            <div class="showcase-feature">
                                <div class="feature-icon">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>

                                <div>
                                    <strong>Seguridad avanzada</strong>
                                    <span>Protección y control de acceso</span>
                                </div>
                            </div>

                            <div class="showcase-feature">
                                <div class="feature-icon">
                                    <i class="fa-solid fa-chart-line"></i>
                                </div>

                                <div>
                                    <strong>Información en tiempo real</strong>
                                    <span>Decisiones basadas en datos</span>
                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- Bottom -->
                    <div class="showcase-bottom">

                        <div class="mini-stat">
                            <strong>99.9%</strong>
                            <span>Disponibilidad</span>
                        </div>

                        <div class="stat-divider"></div>

                        <div class="mini-stat">
                            <strong>24/7</strong>
                            <span>Operación</span>
                        </div>

                        <div class="stat-divider"></div>

                        <div class="mini-stat">
                            <strong>100%</strong>
                            <span>Control</span>
                        </div>

                    </div>

                </div>
            </div>


            <!-- =====================================================
                 PANEL DERECHO - LOGIN
            ====================================================== -->
            <div class="login-form-panel">

                <div class="login-form-content">

                    <!-- Mobile Brand -->
                    <div class="mobile-brand">

                        <div class="brand-icon">
                            <?php $loginLogo = SiteConfig::siteLogo(); ?>
                            <?php if ($loginLogo !== ''): ?>
                                <img
                                        src="<?= htmlspecialchars(rtrim(APP_URL, '/') . '/assets/img/settings/' . rawurlencode($loginLogo), ENT_QUOTES, 'UTF-8') ?>"
                                        alt="<?= htmlspecialchars(SiteConfig::siteName(), ENT_QUOTES, 'UTF-8') ?>"
                                        class="brand-logo"
                                >
                            <?php else: ?>
                                <span>V</span>
                            <?php endif; ?>
                        </div>

                        <div class="brand-text">
                            <span class="brand-name">
                                <?= htmlspecialchars(
                                    SiteConfig::siteName(),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </span>
                        </div>

                    </div>


                    <!-- Header -->
                    <div class="login-header">

                        <span class="login-welcome">
                            BIENVENIDO DE NUEVO
                        </span>

                        <h2>
                            Inicia sesión
                        </h2>

                        <p>
                            Ingresa tus credenciales para acceder
                            a tu panel de administración.
                        </p>

                    </div>


                    <!-- Formulario -->
                    <form id="loginForm" autocomplete="off">

                        <!-- Email -->
                        <div class="login-field">

                            <label for="email">
                                Correo electrónico
                            </label>

                            <div class="input-wrapper">

                                <div class="input-icon">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>

                                <input
                                        type="email"
                                        class="login-input"
                                        id="email"
                                        placeholder="nombre@empresa.com"
                                        autocomplete="username"
                                        required
                                />

                                <div class="input-status">
                                    <i class="fa-solid fa-check"></i>
                                </div>

                            </div>

                        </div>


                        <!-- Password -->
                        <div class="login-field">

                            <div class="field-header">

                                <label for="password">
                                    Contraseña
                                </label>

                                <a
                                        href="#"
                                        class="forgot-password-link"
                                >
                                    ¿Olvidaste tu contraseña?
                                </a>

                            </div>

                            <div class="input-wrapper">

                                <div class="input-icon password-icon">
                                    <i class="fa-solid fa-lock"></i>
                                </div>

                                <input
                                        type="password"
                                        class="login-input"
                                        id="password"
                                        placeholder="Ingresa tu contraseña"
                                        autocomplete="current-password"
                                        required
                                />

                                <button
                                        type="button"
                                        class="btn-toggle-password"
                                        id="btnTogglePassword"
                                        aria-label="Mostrar contraseña"
                                >
                                    <i
                                            class="fa-regular fa-eye"
                                            id="toggleIcon"
                                    ></i>
                                </button>

                            </div>

                        </div>


                        <!-- Remember -->
                        <div class="login-options">

                            <label class="remember-me">

                                <input
                                        type="checkbox"
                                        id="rememberSession"
                                />

                                <span class="custom-checkbox">
                                    <i class="fa-solid fa-check"></i>
                                </span>

                                <span>
                                    Mantener mi sesión iniciada
                                </span>

                            </label>

                        </div>


                        <!-- Submit -->
                        <button
                                type="submit"
                                class="btn-login-submit"
                                id="btnLoginSubmit"
                        >

                            <span class="btn-login-content">

                                <span>
                                    Ingresar al panel
                                </span>

                                <i class="fa-solid fa-arrow-right"></i>

                            </span>

                            <span class="btn-login-loading">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                                Verificando...
                            </span>

                        </button>

                    </form>


                    <!-- Security -->
                    <div class="login-security">

                        <div class="security-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>

                        <div>
                            <strong>Conexión segura</strong>
                            <span>
                                Tus credenciales están protegidas
                            </span>
                        </div>

                    </div>


                    <!-- Footer -->
                    <div class="login-footer">

                        <span>
                            © <?= date('Y') ?>
                            <?= htmlspecialchars(
                                SiteConfig::siteName(),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>

                        <span class="footer-separator">•</span>

                        <span>
                            Plataforma Administrativa
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>