<?php

namespace App\Support;

class StaticPageRenderer
{
    protected const SOURCE_HOST = 'https://baxi-gasthermenwartung.at';

    public static function render(string $view): string
    {
        $viewPath = resource_path("views/{$view}.blade.php");

        abort_unless(is_file($viewPath), 404);

        $html = file_get_contents($viewPath);

        abort_if($html === false, 500, 'Unable to read the requested page.');

        return strtr($html, array_merge(
            self::replacements(),
            self::contactFormReplacements(),
        ));
    }

    protected static function replacements(): array
    {
        static $replacements;

        if ($replacements !== null) {
            return $replacements;
        }

        $assetMapPath = storage_path('app/static-page-assets.json');
        $assetMap = [];

        if (is_file($assetMapPath)) {
            $assetMapJson = file_get_contents($assetMapPath);

            if ($assetMapJson !== false) {
                $assetMapJson = preg_replace('/^\xEF\xBB\xBF/', '', $assetMapJson);
            }

            $decoded = json_decode($assetMapJson ?: '[]', true);

            if (is_array($decoded)) {
                $assetMap = $decoded;
            }
        }

        $routeMap = [
            'href="' . self::SOURCE_HOST . '/"' => 'href="' . route('home') . '"',
            "href='" . self::SOURCE_HOST . "/'" => "href='" . route('home') . "'",
            self::SOURCE_HOST . '/wp-admin/admin-ajax.php' => '#',
            self::SOURCE_HOST . '/wp-json/' => '#',
            self::SOURCE_HOST . '/xmlrpc.php?rsd' => '#',
            self::SOURCE_HOST . '/datenschutz/' => route('datenschutz'),
            self::SOURCE_HOST . '/impressum/' => route('impressum'),
            self::SOURCE_HOST . '/comments/feed/' => '#',
            self::SOURCE_HOST . '/feed/' => '#',
            self::SOURCE_HOST . '/home/feed/' => '#',
            self::SOURCE_HOST . '/datenschutz/feed/' => '#',
            self::SOURCE_HOST . '/impressum/feed/' => '#',
        ];

        $replacements = array_merge($assetMap, $routeMap);
        $replacements['https:\/\/baxi-gasthermenwartung.at\/wp-admin\/admin-ajax.php'] = '#';
        $replacements['https:\/\/baxi-gasthermenwartung.at\/wp-json\/'] = '#';
        $replacements['https:\/\/baxi-gasthermenwartung.at\/wp-content\/uploads'] = asset('wp-content/uploads');
        $replacements['https:\/\/baxi-gasthermenwartung.at\/wp-content\/plugins\/elementor\/assets\/'] = asset('wp-content/plugins/elementor/assets') . '/';
        $replacements['https:\/\/baxi-gasthermenwartung.at\/wp-content\/plugins\/elementor-pro\/assets\/'] = asset('wp-content/plugins/elementor-pro/assets') . '/';
        $replacements['https:\/\/baxi-gasthermenwartung.at\/wp-content\/themes\/hello-elementor\/assets\/'] = asset('wp-content/themes/hello-elementor/assets') . '/';

        return $replacements;
    }

    protected static function contactFormReplacements(): array
    {
        $old = session()->getOldInput();
        $errors = session('errors');
        $success = session('contact_success');

        return [
            '__CONTACT_FORM_ACTION__' => route('contact.submit') . '#wpcf7-f44-p6-o1',
            '__CONTACT_FORM_CSRF__' => csrf_field(),
            '__CONTACT_FORM_SUCCESS__' => $success
                ? '<div class="form-alert form-alert-success">' . e($success) . '</div>'
                : '',
            '__CONTACT_FORM_SUCCESS_ALERT__' => $success
                ? '<script>window.addEventListener("load", function () { alert("Vielen Dank! Ihre Anfrage wurde erfolgreich gesendet."); });</script>'
                : '',
            '__CONTACT_FORM_ERROR__' => $errors && $errors->any()
                ? '<div class="form-alert form-alert-error">Bitte pruefen Sie die eingegebenen Daten und versuchen Sie es erneut.</div>'
                : '',
            '__CONTACT_OLD_NAME__' => e($old['your-name'] ?? ''),
            '__CONTACT_OLD_EMAIL__' => e($old['your-email'] ?? ''),
            '__CONTACT_OLD_PHONE__' => e($old['your-phone'] ?? ''),
            '__CONTACT_OLD_COMPANY__' => e($old['your-company'] ?? ''),
            '__CONTACT_OLD_MESSAGE__' => e($old['your-message'] ?? ''),
            '__CONTACT_INVALID_NAME__' => $errors && $errors->has('your-name') ? 'true' : 'false',
            '__CONTACT_INVALID_EMAIL__' => $errors && $errors->has('your-email') ? 'true' : 'false',
            '__CONTACT_INVALID_PHONE__' => $errors && $errors->has('your-phone') ? 'true' : 'false',
            '__CONTACT_INVALID_COMPANY__' => $errors && $errors->has('your-company') ? 'true' : 'false',
            '__CONTACT_INVALID_MESSAGE__' => $errors && $errors->has('your-message') ? 'true' : 'false',
        ];
    }
}
