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
                            Burger Town.
                            <span>En un solo lugar.</span>
                        </h1>

                        <p>
                            Administra tus sedes, productos, reservas y operación
                            de Burger Town desde una plataforma centralizada.
                        </p>

                        <!-- Features -->
                        <div class="showcase-features">

                            <div class="showcase-feature">
                                <div class="feature-icon">
                                    <i class="fa-solid fa-store"></i>
                                </div>

                                <div>
                                    <strong>Gestiona tus sedes</strong>
                                    <span>Controla la operación de cada restaurante</span>
                                </div>
                            </div>

                            <div class="showcase-feature">
                                <div class="feature-icon">
                                    <i class="fa-solid fa-burger"></i>
                                </div>

                                <div>
                                    <strong>Administra tu menú</strong>
                                    <span>Productos, precios e imágenes</span>
                                </div>
                            </div>

                            <div class="showcase-feature">
                                <div class="feature-icon">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>

                                <div>
                                    <strong>Controla tus reservas</strong>
                                    <span>Clientes, mesas y disponibilidad</span>
                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- Bottom -->
                    <div class="showcase-bottom">

                        <div class="mini-stat">
                            <strong>7+</strong>
                            <span>Sedes</span>
                        </div>

                        <div class="stat-divider"></div>

                        <div class="mini-stat">
                            <strong>24/7</strong>
                            <span>Control</span>
                        </div>

                        <div class="stat-divider"></div>

                        <div class="mini-stat">
                            <strong>100%</strong>
                            <span>Burger Town</span>
                        </div>

                    </div>

                </div>
            </div>


            <!-- =====================================================
                 PANEL DERECHO - RECUPERACIÓN
            ====================================================== -->
            <div class="login-form-panel">

                <div class="login-form-content">

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
                                <?= htmlspecialchars(SiteConfig::siteName(), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    </div>

                    <div class="login-header">
                        <span class="login-welcome">
                            RECUPERA TU ACCESO
                        </span>

                        <h2>
                            ¿Olvidaste tu contraseña?
                        </h2>

                        <p>
                            Ingresa tu correo electrónico, teléfono
                            o número de documento y te enviaremos
                            las instrucciones para recuperar tu acceso.
                        </p>
                    </div>

                    <form id="forgotPasswordForm" autocomplete="off">

                        <div class="login-field">
                            <label for="recoveryIdentifier">
                                Correo, teléfono o documento
                            </label>

                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </div>

                                <input
                                    type="text"
                                    class="login-input"
                                    id="recoveryIdentifier"
                                    placeholder="Ingresa tu dato de acceso"
                                    autocomplete="off"
                                    required
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn-login-submit"
                            id="btnForgotPassword"
                        >
                            <span class="btn-login-content">
                                <span>Enviar instrucciones</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </span>

                            <span class="btn-login-loading">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                                Enviando...
                            </span>
                        </button>
                    </form>

                    <div style="margin-top:22px;text-align:center;">
                        <a
                            href="<?= htmlspecialchars(rtrim(APP_URL, '/') . '/login', ENT_QUOTES, 'UTF-8') ?>"
                            class="forgot-password-link"
                        >
                            <i class="fa-solid fa-arrow-left"></i>
                            Volver al inicio de sesión
                        </a>
                    </div>

                    <div class="login-security">
                        <div class="security-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>

                        <div>
                            <strong>Recuperación segura</strong>
                            <span>
                                El enlace tendrá una duración limitada
                                y solo podrá utilizarse una vez.
                            </span>
                        </div>
                    </div>

                    <div class="login-footer">
                        <span>
                            © <?= date('Y') ?>
                            <?= htmlspecialchars(SiteConfig::siteName(), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="footer-separator">•</span>
                        <span>Plataforma Administrativa</span>
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>