<!-- app/Views/auth/register.php -->

<div class="velox-login-page velox-register-page">

    <div class="login-background">
        <div class="bg-orb bg-orb-1"></div>
        <div class="bg-orb bg-orb-2"></div>
        <div class="bg-orb bg-orb-3"></div>
        <div class="bg-grid"></div>
    </div>

    <div class="container-fluid login-container">

        <div class="login-card register-card">

            <!-- PANEL DE PRESENTACIÓN -->
            <div class="login-showcase">

                <div class="showcase-glow"></div>

                <div class="showcase-content">

                    <div class="login-brand">
                        <div class="brand-icon">
                            <?php $registerLogo = SiteConfig::siteLogo(); ?>
                            <?php if ($registerLogo !== ''): ?>
                                <img
                                    src="<?= htmlspecialchars(rtrim(APP_URL, '/') . '/assets/img/settings/' . rawurlencode($registerLogo), ENT_QUOTES, 'UTF-8') ?>"
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

                    <div class="showcase-main">

                        <span class="showcase-badge">
                            <span class="badge-dot"></span>
                            CUENTA DE CLIENTE
                        </span>

                        <h1>
                            Tu experiencia.
                            <span>Más simple.</span>
                        </h1>

                        <p>
                            Crea tu cuenta para realizar tus pedidos en línea,
                            consultar su estado y tener tu información siempre
                            disponible.
                        </p>

                        <div class="showcase-features">

                            <div class="showcase-feature">
                                <div class="feature-icon">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </div>
                                <div>
                                    <strong>Pedidos en línea</strong>
                                    <span>Compra desde cualquier lugar</span>
                                </div>
                            </div>

                            <div class="showcase-feature">
                                <div class="feature-icon">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <div>
                                    <strong>Consulta tus pedidos</strong>
                                    <span>Ten a mano la información de tus compras</span>
                                </div>
                            </div>

                            <div class="showcase-feature">
                                <div class="feature-icon">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <strong>Cuenta protegida</strong>
                                    <span>Tus datos de acceso se almacenan de forma segura</span>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="showcase-bottom">
                        <div class="mini-stat">
                            <strong>24/7</strong>
                            <span>Acceso</span>
                        </div>

                        <div class="stat-divider"></div>

                        <div class="mini-stat">
                            <strong>100%</strong>
                            <span>En línea</span>
                        </div>

                        <div class="stat-divider"></div>

                        <div class="mini-stat">
                            <strong><i class="fa-solid fa-lock"></i></strong>
                            <span>Seguro</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- FORMULARIO DE REGISTRO -->
            <div class="login-form-panel register-form-panel">

                <div class="login-form-content register-form-content">

                    <div class="mobile-brand">
                        <div class="brand-icon">
                            <?php $registerLogo = SiteConfig::siteLogo(); ?>
                            <?php if ($registerLogo !== ''): ?>
                                <img
                                    src="<?= htmlspecialchars(rtrim(APP_URL, '/') . '/assets/img/settings/' . rawurlencode($registerLogo), ENT_QUOTES, 'UTF-8') ?>"
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

                    <div class="login-header register-header">
                        <span class="login-welcome">
                            CREA TU CUENTA
                        </span>

                        <h2>Regístrate</h2>

                        <p>
                            Completa tus datos para crear tu cuenta de cliente.
                            Los campos marcados son obligatorios.
                        </p>
                    </div>

                    <form id="registerForm" autocomplete="on" novalidate>

                        <div class="login-field">
                            <label for="registerFirstName">Nombre</label>
                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <i class="fa-regular fa-user"></i>
                                </div>
                                <input
                                    type="text"
                                    class="login-input"
                                    id="registerFirstName"
                                    name="first_name"
                                    placeholder="Tu nombre"
                                    autocomplete="given-name"
                                    maxlength="100"
                                    required
                                >
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="registerLastName">Apellido</label>
                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <i class="fa-regular fa-user"></i>
                                </div>
                                <input
                                    type="text"
                                    class="login-input"
                                    id="registerLastName"
                                    name="last_name"
                                    placeholder="Tus apellidos"
                                    autocomplete="family-name"
                                    maxlength="100"
                                    required
                                >
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="registerEmail">Correo electrónico</label>
                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>
                                <input
                                    type="email"
                                    class="login-input"
                                    id="registerEmail"
                                    name="email"
                                    placeholder="nombre@correo.com"
                                    autocomplete="email"
                                    maxlength="190"
                                    required
                                >
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="registerPhone">Teléfono <span class="register-optional">(opcional)</span></label>
                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                                <input
                                    type="tel"
                                    class="login-input"
                                    id="registerPhone"
                                    name="phone"
                                    placeholder="Tu número de contacto"
                                    autocomplete="tel"
                                    maxlength="30"
                                >
                            </div>
                        </div>

                        <div class="login-field">
                            <label for="registerPassword">Contraseña</label>
                            <div class="input-wrapper">
                                <div class="input-icon password-icon">
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                                <input
                                    type="password"
                                    class="login-input"
                                    id="registerPassword"
                                    name="password"
                                    placeholder="Crea una contraseña"
                                    autocomplete="new-password"
                                    minlength="8"
                                    maxlength="72"
                                    required
                                >
                                <button
                                    type="button"
                                    class="btn-toggle-password"
                                    id="btnToggleRegisterPassword"
                                    aria-label="Mostrar contraseña"
                                >
                                    <i class="fa-regular fa-eye" id="registerPasswordIcon"></i>
                                </button>
                            </div>
                            <span class="register-field-hint">Utiliza entre 8 y 72 caracteres.</span>
                        </div>

                        <div class="login-field">
                            <label for="registerPasswordConfirm">Confirmar contraseña</label>
                            <div class="input-wrapper">
                                <div class="input-icon password-icon">
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                                <input
                                    type="password"
                                    class="login-input"
                                    id="registerPasswordConfirm"
                                    name="password_confirmation"
                                    placeholder="Repite tu contraseña"
                                    autocomplete="new-password"
                                    minlength="8"
                                    maxlength="72"
                                    required
                                >
                                <button
                                    type="button"
                                    class="btn-toggle-password"
                                    id="btnToggleRegisterConfirm"
                                    aria-label="Mostrar confirmación de contraseña"
                                >
                                    <i class="fa-regular fa-eye" id="registerConfirmIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn-login-submit"
                            id="btnRegisterSubmit"
                        >
                            <span class="btn-login-content">
                                <span>Crear mi cuenta</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </span>

                            <span class="btn-login-loading">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                                Creando cuenta...
                            </span>
                        </button>

                    </form>

                    <div class="register-login-prompt">
                        ¿Ya tienes una cuenta?
                        <a href="<?= htmlspecialchars(rtrim(APP_URL, '/') . '/login', ENT_QUOTES, 'UTF-8') ?>">
                            Inicia sesión
                        </a>
                    </div>

                    <div class="login-security">
                        <div class="security-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <strong>Registro seguro</strong>
                            <span>Tu contraseña se protege mediante cifrado criptográfico.</span>
                        </div>
                    </div>

                    <div class="login-footer">
                        <span>
                            © <?= date('Y') ?>
                            <?= htmlspecialchars(SiteConfig::siteName(), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="footer-separator">•</span>
                        <span>Cuenta de cliente</span>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
