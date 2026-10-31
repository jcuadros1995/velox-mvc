<?php
// app/Services/SettingsService.php

class SettingsService
{
    private SettingRepository $settingRepository;

    public function __construct()
    {
        $this->settingRepository = new SettingRepository();
    }

    /**
     * Obtiene todas las configuraciones agrupadas.
     */
    public function getGroupedSettings(?int $branchId): array
    {
        return $this->settingRepository->getAllGrouped(
            $branchId
        );
    }

    /**
     * Procesa y guarda las configuraciones recibidas.
     *
     * @return int Cantidad de configuraciones procesadas.
     */
    public function saveSettings(
        ?int $branchId,
        array $postData,
        array $files
    ): int {

        $definitions = $this->getDefinitions();

        $settingsToSave = [];

        foreach ($definitions as $definition) {

            $group = $definition['group'];
            $key   = $definition['key'];
            $type  = $definition['type'];

            $preserveIfBlank =
                (bool) ($definition['preserve_if_blank'] ?? false);

            $fieldName = $group . '__' . $key;

            /*
             * Los booleanos deben enviarse siempre.
             *
             * Un checkbox no marcado normalmente
             * no aparece en $_POST.
             */
            if ($type === 'boolean') {

                $value = isset($postData[$fieldName])
                    ? (int) ((bool) $postData[$fieldName])
                    : 0;

            } else {

                if (!array_key_exists($fieldName, $postData)) {
                    continue;
                }

                $value = $postData[$fieldName];

                /*
                 * Algunos valores sensibles, como la contraseña
                 * SMTP, pueden omitirse cuando el usuario no desea
                 * cambiarlos.
                 */
                if (
                    $preserveIfBlank &&
                    trim((string) $value) === ''
                ) {
                    continue;
                }
            }

            $value = $this->sanitizeValue(
                $value,
                $type,
                $key
            );

            $settingsToSave[] = [
                'group'  => $group,
                'key'    => $key,
                'value'  => $value,
                'type'   => $type,
                'public' => $this->isPublicSetting(
                    $group,
                    $key
                )
            ];
        }

        /*
         * Procesamiento de archivos.
         */
        $uploadedFiles = $this->processUploads($files);

        foreach ($uploadedFiles as $uploaded) {
            $settingsToSave[] = $uploaded;
        }

        if (empty($settingsToSave)) {
            return 0;
        }

        /*
         * Guardado mediante Repository.
         */
        $this->saveSettingsBatch(
            $settingsToSave,
            $branchId
        );

        return count($settingsToSave);
    }

    /**
     * Guarda múltiples configuraciones.
     */
    private function saveSettingsBatch(
        array $settings,
        ?int $branchId
    ): void {

        try {

            $this->settingRepository->beginTransaction();

            foreach ($settings as $setting) {

                $storedValue = $this->prepareValue(
                    $setting['value'],
                    $setting['type'] ?? 'string'
                );

                $existingId =
                    $this->settingRepository->find(
                        $setting['group'],
                        $setting['key'],
                        $branchId
                    );

                if ($existingId !== null) {

                    $this->settingRepository->update(
                        $existingId,
                        $storedValue,
                        $setting['type'] ?? 'string',
                        $setting['public'] ?? 0
                    );

                } else {

                    $this->settingRepository->insert(
                        $branchId,
                        $setting['group'],
                        $setting['key'],
                        $storedValue,
                        $setting['type'] ?? 'string',
                        $setting['public'] ?? 0
                    );
                }
            }

            $this->settingRepository->commit();

        } catch (Throwable $e) {

            $this->settingRepository->rollBack();

            throw $e;
        }
    }

    /**
     * Definiciones de configuraciones permitidas.
     *
     * Solo estas configuraciones pueden ser modificadas
     * desde el formulario administrativo.
     */
    private function getDefinitions(): array
    {
        return [
            // =====================================================
            // BRANDING
            // =====================================================

            [
                'group' => 'branding',
                'key' => 'platform_name',
                'type' => 'string'
            ],
            [
                'group' => 'branding',
                'key' => 'primary_color',
                'type' => 'string'
            ],
            [
                'group' => 'branding',
                'key' => 'secondary_color',
                'type' => 'string'
            ],
            [
                'group' => 'branding',
                'key' => 'description',
                'type' => 'string'
            ],
            [
                'group' => 'branding',
                'key' => 'logo',
                'type' => 'string'
            ],
            [
                'group' => 'branding',
                'key' => 'favicon',
                'type' => 'string'
            ],

            // =====================================================
            // WEBSITE
            // =====================================================

            [
                'group' => 'website',
                'key' => 'site_name',
                'type' => 'string'
            ],
            [
                'group' => 'website',
                'key' => 'site_description',
                'type' => 'string'
            ],
            [
                'group' => 'website',
                'key' => 'site_font',
                'type' => 'string'
            ],
            [
                'group' => 'website',
                'key' => 'site_status',
                'type' => 'string'
            ],
            [
                'group' => 'website',
                'key' => 'site_logo',
                'type' => 'string'
            ],
            [
                'group' => 'website',
                'key' => 'site_favicon',
                'type' => 'string'
            ],
            [
                'group' => 'website',
                'key' => 'social_facebook',
                'type' => 'string'
            ],
            [
                'group' => 'website',
                'key' => 'social_instagram',
                'type' => 'string'
            ],
            [
                'group' => 'website',
                'key' => 'social_tiktok',
                'type' => 'string'
            ],

            // =====================================================
            // COMPANY
            // =====================================================

            [
                'group' => 'company',
                'key' => 'legal_name',
                'type' => 'string'
            ],
            [
                'group' => 'company',
                'key' => 'tax_id',
                'type' => 'string'
            ],
            [
                'group' => 'company',
                'key' => 'address',
                'type' => 'string'
            ],
            [
                'group' => 'company',
                'key' => 'email',
                'type' => 'string'
            ],

            // =====================================================
            // BILLING
            // =====================================================

            [
                'group' => 'billing',
                'key' => 'currency_symbol',
                'type' => 'string'
            ],
            [
                'group' => 'billing',
                'key' => 'currency_code',
                'type' => 'string'
            ],
            [
                'group' => 'billing',
                'key' => 'decimals',
                'type' => 'integer'
            ],

            // =====================================================
            // WOMPI
            // =====================================================

            [
                'group' => 'payments',
                'key' => 'wompi_mode',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'wompi_status',
                'type' => 'boolean'
            ],
            [
                'group' => 'payments',
                'key' => 'wompi_public_key',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'wompi_private_key',
                'type' => 'string'
            ],

            // =====================================================
            // EPAYCO
            // =====================================================

            [
                'group' => 'payments',
                'key' => 'epayco_mode',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'epayco_status',
                'type' => 'boolean'
            ],
            [
                'group' => 'payments',
                'key' => 'epayco_public_key',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'epayco_p_key',
                'type' => 'string'
            ],

            // =====================================================
            // PAYU
            // =====================================================

            [
                'group' => 'payments',
                'key' => 'payu_mode',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'payu_status',
                'type' => 'boolean'
            ],
            [
                'group' => 'payments',
                'key' => 'payu_merchant_id',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'payu_api_key',
                'type' => 'string'
            ],

            // =====================================================
            // MERCADO PAGO
            // =====================================================

            [
                'group' => 'payments',
                'key' => 'mp_mode',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'mp_status',
                'type' => 'boolean'
            ],
            [
                'group' => 'payments',
                'key' => 'mp_public_key',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'mp_access_token',
                'type' => 'string'
            ],

            // =====================================================
            // STRIPE
            // =====================================================

            [
                'group' => 'payments',
                'key' => 'stripe_mode',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'stripe_status',
                'type' => 'boolean'
            ],
            [
                'group' => 'payments',
                'key' => 'stripe_public_key',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'stripe_secret_key',
                'type' => 'string'
            ],

            // =====================================================
            // OPENPAY
            // =====================================================

            [
                'group' => 'payments',
                'key' => 'openpay_mode',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'openpay_status',
                'type' => 'boolean'
            ],
            [
                'group' => 'payments',
                'key' => 'openpay_id',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'openpay_key',
                'type' => 'string'
            ],

            // =====================================================
            // ADDI
            // =====================================================

            [
                'group' => 'payments',
                'key' => 'addi_mode',
                'type' => 'string'
            ],
            [
                'group' => 'payments',
                'key' => 'addi_status',
                'type' => 'boolean'
            ],
            [
                'group' => 'payments',
                'key' => 'addi_client_id',
                'type' => 'string'
            ],

            // =====================================================
// MAIL
// =====================================================

            [
                'group' => 'mail',
                'key' => 'enabled',
                'type' => 'boolean'
            ],

            [
                'group' => 'mail',
                'key' => 'provider',
                'type' => 'string'
            ],

            [
                'group' => 'mail',
                'key' => 'host',
                'type' => 'string'
            ],

            [
                'group' => 'mail',
                'key' => 'port',
                'type' => 'integer'
            ],

            [
                'group' => 'mail',
                'key' => 'encryption',
                'type' => 'string'
            ],

            [
                'group' => 'mail',
                'key' => 'username',
                'type' => 'string'
            ],

            [
                'group' => 'mail',
                'key' => 'password',
                'type' => 'string',
                'preserve_if_blank' => true
            ],

            [
                'group' => 'mail',
                'key' => 'from_name',
                'type' => 'string'
            ],

            [
                'group' => 'mail',
                'key' => 'from_email',
                'type' => 'string'
            ],

            // =====================================================
            // SECURITY
            // =====================================================

            [
                'group' => 'security',
                'key' => 'max_attempts',
                'type' => 'integer'
            ],
            [
                'group' => 'security',
                'key' => 'lockout_time',
                'type' => 'integer'
            ],
            [
                'group' => 'security',
                'key' => 'session_timeout',
                'type' => 'integer'
            ],
            [
                'group' => 'security',
                'key' => 'password_expiry',
                'type' => 'integer'
            ],
            [
                'group' => 'security',
                'key' => 'force_logout',
                'type' => 'boolean'
            ]
        ];
    }

    /**
     * Sanitiza y valida valores recibidos.
     */
    private function sanitizeValue(
        $value,
        string $type,
        string $key
    ) {
        switch ($type) {

            case 'integer':

                $value = filter_var(
                    $value,
                    FILTER_VALIDATE_INT
                );

                return $value === false ? 0 : $value;

            case 'boolean':

                return !empty($value) ? 1 : 0;

            case 'string':
            default:

                $value = trim((string) $value);

                if (str_contains($key, 'color')) {

                    if (
                        !preg_match(
                            '/^#[0-9A-Fa-f]{6}$/',
                            $value
                        )
                    ) {
                        return '';
                    }
                }

                if (
                    str_starts_with($key, 'social_')
                    && $value !== ''
                ) {

                    if (
                        !filter_var(
                            $value,
                            FILTER_VALIDATE_URL
                        )
                    ) {
                        return '';
                    }
                }

                if (
                    $key === 'email'
                    && $value !== ''
                ) {

                    if (
                        !filter_var(
                            $value,
                            FILTER_VALIDATE_EMAIL
                        )
                    ) {
                        return '';
                    }
                }

                return $value;
        }
    }

    /**
     * Determina qué configuraciones pueden ser públicas.
     */
    private function isPublicSetting(
        string $group,
        string $key
    ): int {

        $publicSettings = [
            'branding.platform_name',
            'branding.primary_color',
            'branding.secondary_color',
            'branding.description',
            'branding.logo',
            'branding.favicon',

            'website.site_name',
            'website.site_description',
            'website.site_font',
            'website.site_status',
            'website.site_logo',
            'website.site_favicon',

            'website.social_facebook',
            'website.social_instagram',
            'website.social_tiktok',

            'billing.currency_symbol',
            'billing.currency_code',
            'billing.decimals'
        ];

        return in_array(
            $group . '.' . $key,
            $publicSettings,
            true
        ) ? 1 : 0;
    }

    /**
     * Procesa logos y favicons.
     */
    private function processUploads(array $files): array
    {
        $results = [];

        $uploadDirectory =
            __DIR__ .
            '/../../public/assets/img/settings/';

        if (!is_dir($uploadDirectory)) {
            if (!mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                throw new RuntimeException(
                    'No fue posible crear el directorio de archivos adjuntos.'
                );
            }
        }

        if (!is_writable($uploadDirectory)) {
            throw new RuntimeException(
                'El directorio de archivos adjuntos no tiene permisos de escritura.'
            );
        }

        $uploadDefinitions = [
            'branding__logo' => [
                'group' => 'branding',
                'key' => 'logo',
                'prefix' => 'admin-logo'
            ],
            'branding__favicon' => [
                'group' => 'branding',
                'key' => 'favicon',
                'prefix' => 'admin-favicon'
            ],
            'website__site_logo' => [
                'group' => 'website',
                'key' => 'site_logo',
                'prefix' => 'site-logo'
            ],
            'website__site_favicon' => [
                'group' => 'website',
                'key' => 'site_favicon',
                'prefix' => 'site-favicon'
            ]
        ];

        foreach ($uploadDefinitions as $fieldName => $definition) {
            if (!isset($files[$fieldName])) {
                continue;
            }

            $file = $files[$fieldName];
            $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($error !== UPLOAD_ERR_OK) {
                throw new RuntimeException(
                    $this->getUploadErrorMessage($error)
                );
            }

            $filename = $this->uploadImage(
                $file,
                $uploadDirectory,
                $definition['prefix']
            );

            $results[] = [
                'group' => $definition['group'],
                'key' => $definition['key'],
                'value' => $filename,
                'type' => 'string',
                'public' => 1
            ];
        }

        return $results;
    }

    /**
     * Devuelve un mensaje legible para un error de carga PHP.
     */
    private function getUploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE => 'El archivo adjunto supera el tamaño máximo permitido.',
            UPLOAD_ERR_PARTIAL => 'El archivo adjunto se cargó de forma incompleta. Inténtalo nuevamente.',
            UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene disponible el directorio temporal para archivos adjuntos.',
            UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo adjunto.',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la carga del archivo adjunto.',
            default => 'No fue posible cargar el archivo adjunto.'
        };
    }

    /**
     * Valida y almacena una imagen.
     */
    private function uploadImage(
        array $file,
        string $directory,
        string $prefix
    ): string {
        if (
            empty($file['tmp_name']) ||
            !is_uploaded_file($file['tmp_name'])
        ) {
            throw new RuntimeException(
                'El archivo adjunto no es una carga válida.'
            );
        }

        if (!isset($file['size']) || (int) $file['size'] <= 0) {
            throw new RuntimeException(
                'El archivo adjunto está vacío.'
            );
        }

        if ((int) $file['size'] > 2 * 1024 * 1024) {
            throw new RuntimeException(
                'La imagen no puede superar los 2 MB.'
            );
        }

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/x-icon' => 'ico',
            'image/vnd.microsoft.icon' => 'ico'
        ];

        $mimeType = null;

        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
        } elseif (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($file['tmp_name']);
        }

        if (!isset($allowedMimeTypes[$mimeType])) {
            throw new RuntimeException(
                'El formato del archivo adjunto no está permitido. Usa PNG, JPG, WEBP o ICO.'
            );
        }

        $extension = $allowedMimeTypes[$mimeType];

        $filename =
            $prefix .
            '-' .
            bin2hex(random_bytes(8)) .
            '.' .
            $extension;

        $destination =
            rtrim($directory, DIRECTORY_SEPARATOR) .
            DIRECTORY_SEPARATOR .
            $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException(
                'No fue posible guardar el archivo adjunto en el servidor.'
            );
        }

        return $filename;
    }

    /**
     * Prepara el valor antes de almacenarlo.
     */
    private function prepareValue(
        $value,
        string $type
    ): string {

        switch ($type) {

            case 'boolean':
                return !empty($value) ? '1' : '0';

            case 'integer':
                return (string) ((int) $value);

            case 'json':
                return json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE |
                    JSON_UNESCAPED_SLASHES
                );

            case 'string':
            default:
                return (string) ($value ?? '');
        }
    }
}