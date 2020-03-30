<?php

defined('BASEPATH') or exit('No direct script access allowed');

function register_activation_hook($module, $function)
{
    hooks()->add_action('activate_' . $module . '_module', $function);
}

function register_deactivation_hook($module, $function)
{
    hooks()->add_action('deactivate_' . $module . '_module', $function);
}

function register_uninstall_hook($module, $function)
{
    hooks()->add_action('uninstall_' . $module . '_module', $function);
}

function register_merge_fields($for)
{
    get_instance()->app_merge_fields->register($for);
}

function modules_list_url()
{
    return admin_url('modules');
}

function register_payment_gateway($id, $module)
{
    $CI = &get_instance();

    if (!class_exists('payment_modes_model', false)) {
        $CI->load->model('payment_modes_model');
    }

    $CI->payment_modes_model->add_payment_gateway($id, $module);
}

/**
 * Register active customers area theme hook to initialize CSS/Javascript assets
 * This function should be called only once from the theme functions.php file
 * @param  string $function function to call
 * @return boolean
 */
function register_theme_assets_hook($function)
{
    if (hooks()->has_action('app_client_assets', $function)) {
        return false;
    }

    return hooks()->add_action('app_client_assets', $function, 1);
}

function module_views_path($module, $concat = '')
{
    return module_dir_path($module) . 'views/' . $concat;
}

function module_libs_path($module, $concat = '')
{
    return module_dir_path($module) . 'libraries/' . $concat;
}

function module_dir_path($module, $concat = '')
{
    return APP_MODULES_PATH . $module . '/' . $concat;
}

function module_dir_url($module, $segment = '')
{
    return site_url(basename(APP_MODULES_PATH) . '/' . $module . '/' . ltrim($segment, '/'));
}

function register_language_files($module, $languages = [])
{
    // To use like register_language_files(THEME_STYLE_MODULE_NAME);
    // Without passing the second parameter if it's one language file the same like the module name
    if (is_null($languages) || count($languages) === 0) {
        $languages = [$module];
    }

    $languageLoader = function ($language) use ($languages, $module) {
        $CI = &get_instance();

        $path = APP_MODULES_PATH . $module . '/language/' . $language . '/';
        foreach ($languages as $file_name) {
            $file_path = $path . $file_name . '_lang' . '.php';
            if (file_exists($file_path)) {
                $CI->lang->load($module . '/' . $file_name, $language);
            } elseif ($language != 'english' && !file_exists($file_path)) {
                /**
                 * The module language is not yet translated nor exists in the language that the customer is using
                 * For this reason we will load the english language
                 */
                $CI->lang->load($module . '/' . $file_name, 'english');
            }
        }
        if (file_exists($path . 'custom_lang.php')) {
            $CI->lang->load($module . '/custom', $language);
        }
    };

    hooks()->add_action('after_load_admin_language', $languageLoader);
    hooks()->add_action('after_load_client_language', $languageLoader);
}

/**
 * List of uninstallable modules
 * In most cases these are the default modules that comes with the installation
 * @return array
 */
function uninstallable_modules()
{
    return ['theme_style', 'menu_setup', 'backup', 'surveys', 'goals'];
}
