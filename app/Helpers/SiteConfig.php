<?php

class SiteConfig
{
    private static ?array $settings = null;

    private static function load(): void
    {
        if (self::$settings !== null) {
            return;
        }

        $branchId = null;

        if (
            isset($_SESSION['branch_id']) &&
            $_SESSION['branch_id'] !== null &&
            $_SESSION['branch_id'] !== ''
        ) {
            $branchId = (int) $_SESSION['branch_id'];
        }

        $service = new SettingsService();

        self::$settings = $service->getGroupedSettings($branchId);
    }

    public static function get(
        string $group,
        string $key,
               $default = ''
    ) {
        self::load();

        return self::$settings[$group][$key] ?? $default;
    }

    public static function platformName(): string
    {
        return (string) self::get(
            'branding',
            'platform_name',
            'Burger Town'
        );
    }

    public static function siteName(): string
    {
        $value = self::get(
            'website',
            'site_name',
            ''
        );

        return $value !== ''
            ? (string) $value
            : self::platformName();
    }

    public static function siteDescription(): string
    {
        $value = self::get(
            'website',
            'site_description',
            ''
        );

        if ($value !== '') {
            return (string) $value;
        }

        return (string) self::get(
            'branding',
            'description',
            'Plataforma de gestión empresarial'
        );
    }

    public static function font(): string
    {
        return (string) self::get(
            'website',
            'site_font',
            "'Chillax', 'Helvetica Neue', Arial, sans-serif"
        );
    }

    public static function primaryColor(): string
    {
        return (string) self::get(
            'branding',
            'primary_color',
            '#2563eb'
        );
    }

    public static function secondaryColor(): string
    {
        return (string) self::get(
            'branding',
            'secondary_color',
            '#0ea5e9'
        );
    }

    public static function siteLogo(): string
    {
        $value = self::get(
            'website',
            'site_logo',
            ''
        );

        if ($value !== '') {
            return (string) $value;
        }

        return (string) self::get(
            'branding',
            'logo',
            ''
        );
    }

    public static function siteFavicon(): string
    {
        $value = self::get(
            'website',
            'site_favicon',
            ''
        );

        if ($value !== '') {
            return (string) $value;
        }

        return (string) self::get(
            'branding',
            'favicon',
            ''
        );
    }
}