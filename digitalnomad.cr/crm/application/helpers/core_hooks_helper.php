<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Filters
 */
hooks()->add_filter('check_vault_entries_visibility', '_check_vault_entries_visibility');
hooks()->add_filter('register_merge_fields', 'core_merge_fields');

/*
Actions
 */

hooks()->add_action('new_ticket_admin_page_loaded', 'ticket_message_save_as_predefined_reply_javascript');
hooks()->add_action('ticket_admin_single_page_loaded', 'ticket_message_save_as_predefined_reply_javascript');

hooks()->add_action('database_updated', 'app_set_update_message_info');
hooks()->add_action('before_update_database', 'app_set_pipe_php_permissions');
hooks()->add_action('admin_init', 'app_init_admin_sidebar_menu_items');
hooks()->add_action('admin_init', 'app_init_customer_profile_tabs');
hooks()->add_action('admin_init', 'app_init_project_tabs');
hooks()->add_action('admin_init', 'app_init_settings_tabs');

if (defined('APP_CSRF_PROTECTION') && APP_CSRF_PROTECTION) {
    hooks()->add_action('app_admin_head', 'csrf_jquery_token');
    hooks()->add_action('app_customers_head', 'csrf_jquery_token');
    hooks()->add_action('app_external_form_head', 'csrf_jquery_token');
    hooks()->add_action('elfinder_tinymce_head', 'csrf_jquery_token');
}
