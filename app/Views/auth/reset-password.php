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
                 PANEL DERECHO - RESTABLECER CONTRASEÑA
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
                            SEGURIDAD DE TU CUENTA
                        </span>

                        <h2>
                            Nueva contraseña
                        </h2>

                        <p>
                            Crea una nueva contraseña para recuperar
                            el acceso a tu cuenta administrativa.
                        </p>
                    </div>

                    <form id="resetPasswordForm" autocomplete="off">

                        <div class="login-field">
                            <label for="newPassword">
                                Nueva contraseña
                            </label>

                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <i class="fa-solid fa-lock"></i>
                                </div>

                                <input
                                    type="password"
                                    class="login-input"
                                    id="newPassword"
                                    placeholder="Mínimo 8 caracteres"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                />

                                <button
                                    type="button"
                                    class="btn-toggle-password"
                                    id="btnToggleNewPassword"
                                    aria-label="Mostrar contraseña"
                                >
                                    <i class="fa-regular fa-eye" id="newPasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="confirmPassword">
                                Confirmar contraseña
                            </label>

                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>

                                <input
                                    type="password"
                                    class="login-input"
                                    id="confirmPassword"
                                    placeholder="Repite tu contraseña"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                />

                                <button
                                    type="button"
                                    class="btn-toggle-password"
                                    id="btnToggleConfirmPassword"
                                    aria-label="Mostrar contraseña"
                                >
                                    <i class="fa-regular fa-eye" id="confirmPasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="login-security" style="margin-top:0;margin-bottom:24px;">
                            <div class="security-icon">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>

                            <div>
                                <strong>Requisito</strong>
                                <span>La contraseña debe tener mínimo 8 caracteres.</span>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn-login-submit"
                            id="btnResetPassword"
                        >
                            <span class="btn-login-content">
                                <span>Actualizar contraseña</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </span>

                            <span class="btn-login-loading">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                                Actualizando...
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
                            <strong>Enlace protegido</strong>
                            <span>
                                El enlace de recuperación es temporal y de un solo uso.
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