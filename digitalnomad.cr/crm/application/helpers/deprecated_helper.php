<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @deprecated 2.3.0 use starsWith instead
 */
if (!function_exists('_startsWith')) {
    function _startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
}

/**
 * @deprecated
 */
function get_table_items_html_and_taxes($items, $type, $admin_preview = false)
{
    return get_table_items_and_taxes($items, $type, $admin_preview);
}

/**
 * @deprecated
 */
function get_table_items_pdf_and_taxes($items, $type)
{
    return get_table_items_and_taxes($items, $type);
}

/**
 * @deprecated
 */
function get_project_label($id, $replace_default_by_muted = false)
{
    return project_status_color_class($id, $replace_default_by_muted);
}

/**
 * @deprecated
 */
function project_status_color_class($id, $replace_default_by_muted = false)
{
    if ($id == 1 || $id == 5) {
        $class = 'default';
        if ($replace_default_by_muted == true) {
            $class = 'muted';
        }
    } elseif ($id == 2) {
        $class = 'info';
    } elseif ($id == 3) {
        $class = 'warning';
    } else {
        // ID == 4 finished
        $class = 'success';
    }

    return hooks()->apply_filters('project_status_color_class', $class, $id);
}

/**
 * @deprecated
 * Return class based on task priority id
 * @param  mixed $id
 * @return string
 */
function get_task_priority_class($id)
{
    if ($id == 1) {
        $class = 'muted';
    } elseif ($id == 2) {
        $class = 'info';
    } elseif ($id == 3) {
        $class = 'warning';
    } else {
        $class = 'danger';
    }

    return $class;
}

/**
 * @deprecated
 */
function project_status_by_id($id)
{
    $label     = _l('project_status_' . $id);
    $hook_data = hooks()->apply_filters('project_status_label', ['id' => $id, 'label' => $label]);
    $label     = $hook_data['label'];

    return $label;
}

/**
 * @deprecated
 */
function format_seconds($seconds)
{
    $minutes = $seconds / 60;
    $hours   = $minutes / 60;
    if ($minutes >= 60) {
        return round($hours, 2) . ' ' . _l('hours');
    } elseif ($seconds > 60) {
        return round($minutes, 2) . ' ' . _l('minutes');
    }

    return $seconds . ' ' . _l('seconds');
}

/**
 * @deprecated
 */
function add_encryption_key_old()
{
    $CI          = & get_instance();
    $key         = generate_encryption_key();
    $config_path = APPPATH . 'config/config.php';
    $CI->load->helper('file');
    @chmod($config_path, FILE_WRITE_MODE);
    $config_file = read_file($config_path);
    $config_file = trim($config_file);
    $config_file = str_replace("\$config['encryption_key'] = '';", "\$config['encryption_key'] = '" . $key . "';", $config_file);
    if (!$fp = fopen($config_path, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
        return false;
    }
    flock($fp, LOCK_EX);
    fwrite($fp, $config_file, strlen($config_file));
    flock($fp, LOCK_UN);
    fclose($fp);
    @chmod($config_path, FILE_READ_MODE);

    return $key;
}

/**
* @deprecated
* Function moved in main.js
*/
function app_admin_ajax_search_function()
{
    ?>
<script>
  function init_ajax_search(type, selector, server_data, url){

    var ajaxSelector = $('body').find(selector);
    if(ajaxSelector.length){
      var options = {
        ajax: {
          url: (typeof(url) == 'undefined' ? admin_url + 'misc/get_relation_data' : url),
          data: function () {
            var data = {};
            data.type = type;
            data.rel_id = '';
            data.q = '{{{q}}}';
            if(typeof(server_data) != 'undefined'){
              jQuery.extend(data, server_data);
            }
            return data;
          }
        },
        locale: {
          emptyTitle: "<?php echo _l('search_ajax_empty'); ?>",
          statusInitialized: "<?php echo _l('search_ajax_initialized'); ?>",
          statusSearching:"<?php echo _l('search_ajax_searching'); ?>",
          statusNoResults:"<?php echo _l('not_results_found'); ?>",
          searchPlaceholder:"<?php echo _l('search_ajax_placeholder'); ?>",
          currentlySelected:"<?php echo _l('currently_selected'); ?>",
        },
        requestDelay:500,
        cache:false,
        preprocessData: function(processData){
          var bs_data = [];
          var len = processData.length;
          for(var i = 0; i < len; i++){
            var tmp_data =  {
              'value': processData[i].id,
              'text': processData[i].name,
            };
            if(processData[i].subtext){
              tmp_data.data = {subtext:processData[i].subtext}
            }
            bs_data.push(tmp_data);
          }
          return bs_data;
        },
        preserveSelectedPosition:'after',
        preserveSelected:true
      }
      if(ajaxSelector.data('empty-title')){
        options.locale.emptyTitle = ajaxSelector.data('empty-title');
      }
      ajaxSelector.selectpicker().ajaxSelectPicker(options);
    }
  }
 </script>
<?php
}

/**
 * @deprecated
 */
function number_unformat($number, $force_number = true)
{
    if ($force_number) {
        $number = preg_replace('/^[^\d]+/', '', $number);
    } elseif (preg_match('/^[^\d]+/', $number)) {
        return false;
    }
    $dec_point     = get_option('decimal_separator');
    $thousands_sep = get_option('thousand_separator');
    $type          = (strpos($number, $dec_point) === false) ? 'int' : 'float';
    $number        = str_replace([
        $dec_point,
        $thousands_sep,
    ], [
        '.',
        '',
    ], $number);
    settype($number, $type);

    return $number;
}


/**
 * Output the select plugin with locale
 * @param  string $locale current locale
 * @return mixed
 */
function app_select_plugin_js($locale = 'en')
{
    echo "<script src='" . base_url('assets/plugins/app-build/bootstrap-select.min.js?v=' . get_app_version()) . "'></script>" . PHP_EOL;

    if ($locale != 'en') {
        if (file_exists(FCPATH . 'assets/plugins/bootstrap-select/js/i18n/defaults-' . $locale . '.min.js')) {
            echo "<script src='" . base_url('assets/plugins/bootstrap-select/js/i18n/defaults-' . $locale . '.min.js') . "'></script>" . PHP_EOL;
        } elseif (file_exists(FCPATH . 'assets/plugins/bootstrap-select/js/i18n/defaults-' . $locale . '_' . strtoupper($locale) . '.min.js')) {
            echo "<script src='" . base_url('assets/plugins/bootstrap-select/js/i18n/defaults-' . $locale . '_' . strtoupper($locale) . '.min.js') . "'></script>" . PHP_EOL;
        }
    }
}

/**
 * Output the validation plugin with locale
 * @param  string $locale current locale
 * @return mixed
 */
function app_jquery_validation_plugin_js($locale = 'en')
{
    echo "<script src='" . base_url('assets/plugins/jquery-validation/jquery.validate.min.js?v=' . get_app_version()) . "'></script>" . PHP_EOL;
    if ($locale != 'en') {
        if (file_exists(FCPATH . 'assets/plugins/jquery-validation/localization/messages_' . $locale . '.min.js')) {
            echo "<script src='" . base_url('assets/plugins/jquery-validation/localization/messages_' . $locale . '.min.js') . "'></script>" . PHP_EOL;
        } elseif (file_exists(FCPATH . 'assets/plugins/jquery-validation/localization/messages_' . $locale . '_' . strtoupper($locale) . '.min.js')) {
            echo "<script src='" . base_url('assets/plugins/jquery-validation/localization/messages_' . $locale . '_' . strtoupper($locale) . '.min.js') . "'></script>" . PHP_EOL;
        }
    }
}

/**
 * Based on the template slug and email the function will fetch a template from database
 * The template will be fetched on the language that should be sent
 * @param  string $template_slug
 * @param  string $email
 * @return object
 */
function get_email_template_for_sending($template_slug, $email)
{
    $CI = & get_instance();

    $language = get_email_template_language($template_slug, $email);

    if (!is_dir(APPPATH . 'language/' . $language)) {
        $language = 'english';
    }

    if (!class_exists('emails_model', false)) {
        $CI->load->model('emails_model');
    }

    $template = $CI->emails_model->get(['language' => $language, 'slug' => $template_slug], 'row');

    // Template languages not yet inserted
    // Users needs to visit Setup->Email Templates->Any template to initialize all languages
    if (!$template) {
        $template = $CI->emails_model->get(['language' => 'english', 'slug' => $template_slug], 'row');
    } else {
        if ($template && $template->message == '') {
            // Template message blank use the active language default template
            $template = $CI->emails_model->get(['language' => get_option('active_language'), 'slug' => $template_slug], 'row');

            if ($template->message == '') {
                $template = $CI->emails_model->get(['language' => 'english', 'slug' => $template_slug], 'row');
            }
        }
    }

    return $template;
}

/**
 * @deprecated 2.3.0
 * This function will parse email template merge fields and replace with the corresponding merge fields passed before sending email
 * @param  object $template     template from database
 * @param  array $merge_fields available merge fields
 * @return object
 */
function _parse_email_template_merge_fields($template, $merge_fields)
{
    return parse_email_template_merge_fields($template, $merge_fields);
}



/**
 * All email client templates slugs used for sending the emails
 * If you create new email template you can and must add the slug here with action hook.
 * Those are used to identify in what language should the email template to be sent
 * @deprecated 2.3.0
 * @return array
 */
function get_client_email_templates_slugs()
{
    $templates = [
        'new-client-created',
        'client-statement',
        'invoice-send-to-client',
        'new-ticket-opened-admin',
        'ticket-reply',
        'ticket-autoresponse',
        'assigned-to-project',
        'credit-note-send-to-client',
        'invoice-payment-recorded',
        'invoice-overdue-notice',
        'invoice-already-send',
        'estimate-send-to-client',
        'contact-forgot-password',
        'contact-password-reseted',
        'contact-set-password',
        'estimate-already-send',
        'contract-expiration',
        'proposal-send-to-customer',
        'proposal-client-thank-you',
        'proposal-comment-to-client',
        'estimate-thank-you-to-customer',
        'send-contract',
        'contract-comment-to-client',
        'auto-close-ticket',
        'new-project-discussion-created-to-customer',
        'new-project-file-uploaded-to-customer',
        'new-project-discussion-comment-to-customer',
        'project-finished-to-customer',
        'estimate-expiry-reminder',
        'estimate-expiry-reminder',
        'task-status-change-to-contacts',
        'task-added-attachment-to-contacts',
        'task-commented-to-contacts',
        'send-subscription',
        'subscription-payment-failed',
        'subscription-payment-succeeded',
        'subscription-canceled',
        'client-registration-confirmed',
        'contact-verification-email',
    ];

    return hooks()->apply_filters('client_email_templates', $templates);
}
/**
 * All email staff templates slugs used for sending the emails
 * If you create new email template you can and must add the slug here with action hook.
 * Those are used to identify in what language should the email template to be sent
 * @deprecated 2.3.0
 * @return array
 */
function get_staff_email_templates_slugs()
{
    $templates = [
        'reminder-email-staff',
        'new-ticket-created-staff',
        'two-factor-authentication',
        'ticket-reply-to-admin',
        'ticket-assigned-to-admin',
        'task-assigned',
        'task-added-as-follower',
        'task-commented',
        'contract-comment-to-admin',
        'staff-password-reseted',
        'staff-forgot-password',
        'task-status-change-to-staff',
        'task-added-attachment',
        'estimate-declined-to-staff',
        'estimate-accepted-to-staff',
        'proposal-client-accepted',
        'proposal-client-declined',
        'proposal-comment-to-admin',
        'task-deadline-notification',
        'invoice-payment-recorded-to-staff',
        'new-project-discussion-created-to-staff',
        'new-project-file-uploaded-to-staff',
        'new-project-discussion-comment-to-staff',
        'staff-added-as-project-member',
        'new-staff-created',
        'new-client-registered-to-admin',
        'new-lead-assigned',
        'contract-expiration-to-staff',
        'gdpr-removal-request',
        'gdpr-removal-request-lead',
        'contract-signed-to-staff',
        'customer-subscribed-to-staff',
        'new-customer-profile-file-uploaded-to-staff',
    ];

    return hooks()->apply_filters('staff_email_templates', $templates);
}


/**
 * Function that will return in what language the email template should be sent
 * @param  string $template_slug the template slug
 * @param  string $email         email that this template will be sent
 * @deprecated 2.3.0
 * @return string
 */
function get_email_template_language($template_slug, $email)
{
    $CI       = & get_instance();
    $language = get_option('active_language');

    if (total_rows(db_prefix() . 'contacts', [
        'email' => $email,
    ]) > 0 && in_array($template_slug, get_client_email_templates_slugs())) {
        $CI->db->where('email', $email);

        $contact = $CI->db->get(db_prefix() . 'contacts')->row();
        $lang    = get_client_default_language($contact->userid);
        if ($lang != '') {
            $language = $lang;
        }
    } elseif (total_rows(db_prefix() . 'staff', [
            'email' => $email,
        ]) > 0 && in_array($template_slug, get_staff_email_templates_slugs())) {
        $CI->db->where('email', $email);
        $staff = $CI->db->get(db_prefix() . 'staff')->row();

        $lang = get_staff_default_language($staff->staffid);
        if ($lang != '') {
            $language = $lang;
        }
    } elseif (isset($GLOBALS['SENDING_EMAIL_TEMPLATE_CLASS']) || defined('EMAIL_TEMPLATE_PROPOSAL_ID_HELP')) {
        if (defined('EMAIL_TEMPLATE_PROPOSAL_ID_HELP')) {
            $CI->db->select('rel_type,rel_id')
            ->where('id', EMAIL_TEMPLATE_PROPOSAL_ID_HELP);
            $proposal = $CI->db->get(db_prefix() . 'proposals')->row();
        } else {
            $class = $GLOBALS['SENDING_EMAIL_TEMPLATE_CLASS'];

            // check for leads default language
            if ($class->get_rel_type() == 'proposal') {
                $CI->db->select('rel_type,rel_id')
            ->where('id', $class->get_rel_id());
                $proposal = $CI->db->get(db_prefix() . 'proposals')->row();
            } elseif ($class->get_rel_type() == 'lead') {
                $CI->db->select('id, default_language')
            ->where('id', $class->get_rel_id());
                $lead = $CI->db->get(db_prefix() . 'leads')->row();
            }
        }
        if (isset($proposal) && $proposal && $proposal->rel_type == 'lead') {
            $CI->db->select('default_language')
                ->where('id', $proposal->rel_id);

            $lead = $CI->db->get(db_prefix() . 'leads')->row();
        }

        if (isset($lead) && $lead && !empty($lead->default_language)) {
            $language = $lead->default_language;
        }
    }

    return hooks()->apply_filters('email_template_language', $language, ['template_slug' => $template_slug, 'email' => $email]);
}

/**
 * @deprecated 2.3.0
 * @return string
 */
function default_aside_menu_active()
{
    $menu                    = new stdClass();
    $menu->aside_menu_active = [];

    $item                      = new stdClass();
    $item->name                = 'als_dashboard';
    $item->url                 = '/';
    $item->permission          = '';
    $item->icon                = 'fa fa-home';
    $item->id                  = 'dashboard';
    $menu->aside_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'als_clients';
    $item->url                 = 'clients';
    $item->permission          = 'customers';
    $item->icon                = 'fa fa-user-o';
    $item->id                  = 'customers';
    $menu->aside_menu_active[] = $item;

    $item                  = new stdClass();
    $item->name            = 'als_sales';
    $item->url             = '#';
    $item->permission      = '';
    $item->icon            = 'fa fa-balance-scale';
    $item->id              = 'sales';
    $item->children        = [];
    $menuChild             = new stdClass();
    $menuChild->name       = 'proposals';
    $menuChild->url        = 'proposals';
    $menuChild->permission = 'proposals';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-proposals';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'estimates';
    $menuChild->url        = 'estimates/list_estimates';
    $menuChild->permission = 'estimates';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-estimates';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'invoices';
    $menuChild->url        = 'invoices/list_invoices';
    $menuChild->permission = 'invoices';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-invoices';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'payments';
    $menuChild->url        = 'payments';
    $menuChild->permission = 'payments';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-payments';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'credit_notes';
    $menuChild->url        = 'credit_notes';
    $menuChild->permission = 'credit_notes';
    $menuChild->icon       = '';
    $menuChild->id         = 'credit_notes';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'items';
    $menuChild->url        = 'invoice_items';
    $menuChild->permission = 'items';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-items';
    $item->children[]      = $menuChild;

    $menu->aside_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'subscriptions';
    $item->url                 = 'subscriptions';
    $item->permission          = 'subscriptions';
    $item->icon                = 'fa fa-repeat';
    $item->id                  = 'subscriptions';
    $menu->aside_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'als_expenses';
    $item->url                 = 'expenses/list_expenses';
    $item->permission          = 'expenses';
    $item->icon                = 'fa fa-file-text-o';
    $item->id                  = 'expenses';
    $menu->aside_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'als_contracts';
    $item->url                 = 'contracts';
    $item->permission          = 'contracts';
    $item->icon                = 'fa fa-file';
    $item->id                  = 'contracts';
    $menu->aside_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'projects';
    $item->url                 = 'projects';
    $item->permission          = '';
    $item->icon                = 'fa fa-bars';
    $item->id                  = 'projects';
    $menu->aside_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'als_tasks';
    $item->url                 = 'tasks/list_tasks';
    $item->permission          = '';
    $item->icon                = 'fa fa-tasks';
    $item->id                  = 'tasks';
    $menu->aside_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'support';
    $item->url                 = 'tickets';
    $item->permission          = '';
    $item->icon                = 'fa fa-ticket';
    $item->id                  = 'tickets';
    $menu->aside_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'als_leads';
    $item->url                 = 'leads';
    $item->permission          = 'is_staff_member';
    $item->icon                = 'fa fa-tty';
    $item->id                  = 'leads';
    $menu->aside_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'als_kb';
    $item->url                 = 'knowledge_base';
    $item->permission          = 'knowledge_base';
    $item->icon                = 'fa fa-folder-open-o';
    $item->id                  = 'knowledge-base';
    $menu->aside_menu_active[] = $item;

    $item                  = new stdClass();
    $item->name            = 'als_utilities';
    $item->url             = '#';
    $item->permission      = '';
    $item->icon            = 'fa fa-cogs';
    $item->id              = 'utilities';
    $item->children        = [];
    $menuChild             = new stdClass();
    $menuChild->name       = 'als_media';
    $menuChild->url        = 'utilities/media';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-media';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'bulk_pdf_exporter';
    $menuChild->url        = 'utilities/bulk_pdf_exporter';
    $menuChild->permission = 'bulk_pdf_exporter';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-bulk-pdf-exporter';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'als_calendar_submenu';
    $menuChild->url        = 'utilities/calendar';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-calendar';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'als_goals_tracking';
    $menuChild->url        = 'goals';
    $menuChild->permission = 'goals';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-goals-tracking';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'als_surveys';
    $menuChild->url        = 'surveys';
    $menuChild->permission = 'surveys';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-surveys';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'als_announcements_submenu';
    $menuChild->url        = 'announcements';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-announcements';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'utility_backup';
    $menuChild->url        = 'utilities/backup';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-database-backup';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'als_activity_log_submenu';
    $menuChild->url        = 'utilities/activity_log';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-activity-log';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'ticket_pipe_log';
    $menuChild->url        = 'utilities/pipe_log';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'ticket-pipe-log';
    $item->children[]      = $menuChild;

    $menu->aside_menu_active[] = $item;

    $item                  = new stdClass();
    $item->name            = 'als_reports';
    $item->url             = '#';
    $item->permission      = 'reports';
    $item->icon            = 'fa fa-area-chart';
    $item->id              = 'reports';
    $item->children        = [];
    $menuChild             = new stdClass();
    $menuChild->name       = 'als_reports_sales_submenu';
    $menuChild->url        = 'reports/sales';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-sales';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'als_reports_expenses';
    $menuChild->url        = 'reports/expenses';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-expenses';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'als_expenses_vs_income';
    $menuChild->url        = 'reports/expenses_vs_income';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-expenses-vs-income';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'als_reports_leads_submenu';
    $menuChild->url        = 'reports/leads';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-leads';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'timesheets_overview';
    $menuChild->url        = 'staff/timesheets?view=all';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'reports_timesheets_overview';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'als_kb_articles_submenu';
    $menuChild->url        = 'reports/knowledge_base_articles';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'child-kb-articles';
    $item->children[]      = $menuChild;

    $menu->aside_menu_active[] = $item;

    $menu = hooks()->apply_filters('default_admin_main_menu', $menu);

    return json_encode($menu);
}

/**
 * @deprecated 2.3.0
 * @return string
 */
function add_main_menu_item($options = [], $parent = '')
{
    return false;

    $default_options = [
        'name',
        'permission',
        'icon',
        'url',
        'id',
        ];
    $order = '';
    if (isset($options['order'])) {
        $order = $options['order'];
        unset($options['order']);
    }
    $data = [];
    for ($i = 0; $i < count($default_options); $i++) {
        if (isset($options[$default_options[$i]])) {
            $data[$default_options[$i]] = $options[$default_options[$i]];
        } else {
            $data[$default_options[$i]] = '';
        }
    }

    $CI = &get_instance();
    $CI->db->where('name', 'aside_menu_active');
    $menu = $CI->db->get(db_prefix() . 'options')->row()->value;

    $menu = json_decode($menu);
    // check if the id exists
    if ($data['id'] == '') {
        $data['id'] = slug_it($data['name']);
    }
    $total_exists = 0;
    foreach ($menu->aside_menu_active as $item) {
        if ($item->id == $data['id']) {
            $total_exists++;
        }
    }
    if ($total_exists > 0) {
        return false;
    }
    $_data = new stdClass();
    foreach ($data as $key => $val) {
        $_data->{$key} = $val;
    }

    $data = $_data;
    if ($parent == '') {
        if ($order == '') {
            array_push($menu->aside_menu_active, $data);
        } else {
            if ($order == 1) {
                array_unshift($menu->aside_menu_active, []);
            } else {
                $order = $order - 1;
                array_splice($menu->aside_menu_active, $order, 0, [
                    '',
                    ]);
            }
            $menu->aside_menu_active[$order] = $_data;
        }
    } else {
        $i            = 0;
        $parent_found = false;
        foreach ($menu->aside_menu_active as $item) {
            if ($item->id == $parent) {
                $parent_found = true;
                if (!isset($item->children)) {
                    $menu->aside_menu_active[$i]->children   = [];
                    $menu->aside_menu_active[$i]->children[] = $data;

                    break;
                }
                if ($order == '') {
                    $menu->aside_menu_active[$i]->children[] = $data;
                } else {
                    if ($order == 1) {
                        array_unshift($menu->aside_menu_active[$i]->children, []);
                    } else {
                        $order = $order - 1;
                        array_splice($menu->aside_menu_active[$i]->children, $order, 0, [
                                '',
                                ]);
                    }
                    $menu->aside_menu_active[$i]->children[$order] = $data;
                }

                break;
            }
            $i++;
        }
        if ($parent_found == false) {
            $data = (array) $data;
            add_main_menu_item($data);

            return true;
        }
    }
    if (update_option('aside_menu_active', json_encode($menu))) {
        return true;
    }

    return false;
}


/**
 * @deprecated 2.3.0
 * @return string
 */
function add_setup_menu_item($options = [], $parent = '')
{
    $default_options = [
        'name',
        'permission',
        'icon',
        'url',
        'id',
        ];
    $order = '';
    if (isset($options['order'])) {
        $order = $options['order'];
        unset($options['order']);
    }
    $data = [];
    for ($i = 0; $i < count($default_options); $i++) {
        if (isset($options[$default_options[$i]])) {
            $data[$default_options[$i]] = $options[$default_options[$i]];
        } else {
            $data[$default_options[$i]] = '';
        }
    }
    if ($data['id'] == '') {
        $data['id'] = slug_it($data['name']);
    }

    $menu = get_option('setup_menu_active');
    $menu = json_decode($menu);
    // check if the id exists
    if ($data['id'] == '') {
        $data['id'] = slug_it($data['name']);
    }
    $total_exists = 0;
    foreach ($menu->setup_menu_active as $item) {
        if ($item->id == $data['id']) {
            $total_exists++;
        }
    }
    if ($total_exists > 0) {
        return false;
    }
    $_data = new stdClass();
    foreach ($data as $key => $val) {
        $_data->{$key} = $val;
    }
    $data = $_data;
    if ($parent == '') {
        if ($order == 1) {
            array_unshift($menu->setup_menu_active, []);
        } else {
            $order = $order - 1;
            array_splice($menu->setup_menu_active, $order, 0, [
                '',
                ]);
        }
        $menu->setup_menu_active[$order] = $_data;
    } else {
        $i = 0;
        foreach ($menu->setup_menu_active as $item) {
            if ($item->id == $parent) {
                if (!isset($item->children)) {
                    $menu->setup_menu_active[$i]->children   = [];
                    $menu->setup_menu_active[$i]->children[] = $data;

                    break;
                }
                $menu->setup_menu_active[$i]->children[] = $data;

                break;
            }
            $i++;
        }
    }
    if (update_option('setup_menu_active', json_encode($menu))) {
        return true;
    }

    return false;
}

/**
 * @deprecated 2.3.0
 * @return string
 */
function default_setup_menu_active()
{
    $menu                      = new stdClass();
    $menu->setup_menu_active   = [];
    $item                      = new stdClass();
    $item->name                = 'als_staff';
    $item->url                 = 'staff';
    $item->permission          = 'staff';
    $item->icon                = '';
    $item->id                  = 'staff';
    $menu->setup_menu_active[] = $item;

    $item                  = new stdClass();
    $item->name            = 'clients';
    $item->url             = '#';
    $item->permission      = 'is_admin';
    $item->icon            = '';
    $item->id              = 'customers';
    $item->children        = [];
    $menuChild             = new stdClass();
    $menuChild->name       = 'customer_groups';
    $menuChild->url        = 'clients/groups';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'groups';
    $item->children[]      = $menuChild;

    $menu->setup_menu_active[] = $item;

    $item                  = new stdClass();
    $item->name            = 'support';
    $item->url             = '#';
    $item->permission      = '';
    $item->icon            = '';
    $item->id              = 'tickets';
    $item->children        = [];
    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_departments';
    $menuChild->url        = 'departments';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'departments';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_ticket_predefined_replies_submenu';
    $menuChild->url        = 'tickets/predefined_replies';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'predefined-replies';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_ticket_priority_submenu';
    $menuChild->url        = 'tickets/priorities';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'ticket-priority';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_ticket_statuses_submenu';
    $menuChild->url        = 'tickets/statuses';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'ticket-statuses';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_ticket_services_submenu';
    $menuChild->url        = 'tickets/services';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'services';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'spam_filters';
    $menuChild->url        = 'spam_filters/view/tickets';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'spam-filters';
    $item->children[]      = $menuChild;

    $menu->setup_menu_active[] = $item;

    $item                  = new stdClass();
    $item->name            = 'acs_leads';
    $item->url             = '#';
    $item->permission      = 'is_admin';
    $item->icon            = '';
    $item->id              = 'leads';
    $item->children        = [];
    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_leads_sources_submenu';
    $menuChild->url        = 'leads/sources';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'sources';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_leads_statuses_submenu';
    $menuChild->url        = 'leads/statuses';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'statuses';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'leads_email_integration';
    $menuChild->url        = 'leads/email_integration';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'email-integration';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'web_to_lead';
    $menuChild->url        = 'leads/forms';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'web-to-lead';
    $item->children[]      = $menuChild;

    $menu->setup_menu_active[] = $item;

    $item                  = new stdClass();
    $item->name            = 'acs_finance';
    $item->url             = '#';
    $item->permission      = 'is_admin';
    $item->icon            = '';
    $item->id              = 'finance';
    $item->children        = [];
    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_sales_taxes_submenu';
    $menuChild->url        = 'taxes';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'taxes';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_sales_currencies_submenu';
    $menuChild->url        = 'currencies';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'currencies';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_sales_payment_modes_submenu';
    $menuChild->url        = 'paymentmodes';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'payment-modes';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_expense_categories';
    $menuChild->url        = 'expenses/categories';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'expenses-categories';
    $item->children[]      = $menuChild;

    $menu->setup_menu_active[] = $item;

    $item                  = new stdClass();
    $item->name            = 'acs_contracts';
    $item->url             = '#';
    $item->permission      = 'is_admin';
    $item->icon            = '';
    $item->id              = 'contracts';
    $item->children        = [];
    $menuChild             = new stdClass();
    $menuChild->name       = 'acs_contract_types';
    $menuChild->url        = 'contracts/types';
    $menuChild->permission = '';
    $menuChild->icon       = '';
    $menuChild->id         = 'contract-types';
    $item->children[]      = $menuChild;

    $menu->setup_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'modules';
    $item->url                 = 'modules';
    $item->permission          = 'is_admin';
    $item->icon                = '';
    $item->id                  = 'modules';
    $menu->setup_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'acs_email_templates';
    $item->url                 = 'emails';
    $item->permission          = 'email_templates';
    $item->icon                = '';
    $item->id                  = 'email-templates';
    $menu->setup_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'asc_custom_fields';
    $item->url                 = 'custom_fields';
    $item->permission          = 'is_admin';
    $item->icon                = '';
    $item->id                  = 'custom-fields';
    $menu->setup_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'gdpr_short';
    $item->url                 = 'gdpr';
    $item->permission          = 'is_admin';
    $item->icon                = '';
    $item->id                  = 'gdpr';
    $menu->setup_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'acs_roles';
    $item->url                 = 'roles';
    $item->permission          = 'roles';
    $item->icon                = '';
    $item->id                  = 'roles';
    $menu->setup_menu_active[] = $item;

    $item                  = new stdClass();
    $item->name            = 'menu_builder';
    $item->url             = '#';
    $item->permission      = 'is_admin';
    $item->icon            = '';
    $item->id              = 'menu-builder';
    $item->children        = [];
    $menuChild             = new stdClass();
    $menuChild->name       = 'main_menu';
    $menuChild->url        = 'utilities/main_menu';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'organize-sidebar';
    $item->children[]      = $menuChild;

    $menuChild             = new stdClass();
    $menuChild->name       = 'setup_menu';
    $menuChild->url        = 'utilities/setup_menu';
    $menuChild->permission = 'is_admin';
    $menuChild->icon       = '';
    $menuChild->id         = 'setup-menu';
    $item->children[]      = $menuChild;

    $menu->setup_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'theme_style';
    $item->url                 = 'utilities/theme_style';
    $item->permission          = 'is_admin';
    $item->icon                = '';
    $item->id                  = 'theme-style';
    $menu->setup_menu_active[] = $item;

    $item                      = new stdClass();
    $item->name                = 'acs_settings';
    $item->url                 = 'settings';
    $item->permission          = 'settings';
    $item->icon                = '';
    $item->id                  = 'settings';
    $menu->setup_menu_active[] = $item;

    $menu = hooks()->apply_filters('default_admin_setup_menu', $menu);

    return json_encode($menu);
}

if (!function_exists('get_table_items_and_taxes')) {
    /**
     * Function for all table items HTML and PDF
     * @deprecated 2.3.0 use get_items_table_data instead
     * @param  array  $items         all items
     * @param  string  $type          where do items come form, eq invoice,estimate,proposal etc..
     * @param  boolean $admin_preview in admin preview add additional sortable classes
     * @return array
     */
    function get_table_items_and_taxes($items, $type, $admin_preview = false)
    {
        $cf = count($items) > 0 ? get_items_custom_fields_for_table_html($items[0]['rel_id'], $type) : [];

        static $rel_data = null;

        $result['html']    = '';
        $result['taxes']   = [];
        $_calculated_taxes = [];
        $i                 = 1;
        foreach ($items as $item) {

              // No relation data on preview becuase taxes are not saved in database
            if (!defined('INVOICE_PREVIEW_SUBSCRIPTION')) {
                if (!$rel_data) {
                    $rel_data = get_relation_data($item['rel_type'], $item['rel_id']);
                }
            } else {
                $rel_data = $GLOBALS['items_preview_transaction'];
            }

            $item_taxes = [];

            // Separate functions exists to get item taxes for Invoice, Estimate, Proposal, Credit Note
            $func_taxes = 'get_' . $type . '_item_taxes';
            if (function_exists($func_taxes)) {
                $item_taxes = call_user_func($func_taxes, $item['id']);
            }

            $itemHTML        = '';
            $trAttributes    = '';
            $tdFirstSortable = '';

            if ($admin_preview == true) {
                $trAttributes    = ' class="sortable" data-item-id="' . $item['id'] . '"';
                $tdFirstSortable = ' class="dragger item_no"';
            }

            if (class_exists('pdf', false) || class_exists('app_pdf', false)) {
                $font_size = get_option('pdf_font_size');
                if ($font_size == '') {
                    $font_size = 10;
                }

                $trAttributes .= ' style="font-size:' . ($font_size + 4) . 'px;"';
            }

            $itemHTML .= '<tr nobr="true"' . $trAttributes . '>';
            $itemHTML .= '<td' . $tdFirstSortable . ' align="center">' . $i . '</td>';

            $itemHTML .= '<td class="description" align="left;">';
            if (!empty($item['description'])) {
                $itemHTML .= '<span style="font-size:' . (isset($font_size) ? $font_size + 4 : '') . 'px;"><strong>' . $item['description'] . '</strong></span>';

                if (!empty($item['long_description'])) {
                    $itemHTML .= '<br />';
                }
            }
            if (!empty($item['long_description'])) {
                $itemHTML .= '<span style="color:#424242;">' . $item['long_description'] . '</span>';
            }

            $itemHTML .= '</td>';

            foreach ($cf as $custom_field) {
                $itemHTML .= '<td align="left">' . get_custom_field_value($item['id'], $custom_field['id'], 'items') . '</td>';
            }

            $itemHTML .= '<td align="right">' . floatVal($item['qty']);
            if ($item['unit']) {
                $itemHTML .= ' ' . $item['unit'];
            }

            $rate = hooks()->apply_filters(
                'item_preview_rate',
                app_format_number($item['rate']),
                ['item' => $item, 'relation' => $rel_data, 'taxes' => $item_taxes]
            );

            $itemHTML .= '</td>';
            $itemHTML .= '<td align="right">' . $rate . '</td>';
            if (get_option('show_tax_per_item') == 1) {
                $itemHTML .= '<td align="right">';
            }

            if (defined('INVOICE_PREVIEW_SUBSCRIPTION')) {
                $item_taxes = $item['taxname'];
            }

            if (count($item_taxes) > 0) {
                foreach ($item_taxes as $tax) {
                    $calc_tax     = 0;
                    $tax_not_calc = false;

                    if (!in_array($tax['taxname'], $_calculated_taxes)) {
                        array_push($_calculated_taxes, $tax['taxname']);
                        $tax_not_calc = true;
                    }
                    if ($tax_not_calc == true) {
                        $result['taxes'][$tax['taxname']]          = [];
                        $result['taxes'][$tax['taxname']]['total'] = [];
                        array_push($result['taxes'][$tax['taxname']]['total'], (($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
                        $result['taxes'][$tax['taxname']]['tax_name'] = $tax['taxname'];
                        $result['taxes'][$tax['taxname']]['taxrate']  = $tax['taxrate'];
                    } else {
                        array_push($result['taxes'][$tax['taxname']]['total'], (($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
                    }
                    if (get_option('show_tax_per_item') == 1) {
                        $item_tax = '';
                        if ((count($item_taxes) > 1 && get_option('remove_tax_name_from_item_table') == false) || get_option('remove_tax_name_from_item_table') == false || multiple_taxes_found_for_item($item_taxes)) {
                            $tmp      = explode('|', $tax['taxname']);
                            $item_tax = $tmp[0] . ' ' . app_format_number($tmp[1]) . '%<br />';
                        } else {
                            $item_tax .= app_format_number($tax['taxrate']) . '%';
                        }

                        $itemHTML .= hooks()->apply_filters('item_tax_table_row', $item_tax, [
                            'item_taxes' => $item_taxes,
                            'item_id'    => $item['id'],
                        ]);
                    }
                }
            } else {
                if (get_option('show_tax_per_item') == 1) {
                    $itemHTML .= hooks()->apply_filters('item_tax_table_row', '0%', [
                            'item_taxes' => $item_taxes,
                            'item_id'    => $item['id'],
                        ]);
                }
            }

            if (get_option('show_tax_per_item') == 1) {
                $itemHTML .= '</td>';
            }

            /**
             * Possible action hook user to include tax in item total amount calculated with the quantiy
             * eq Rate * QTY + TAXES APPLIED
             */

            $item_amount_with_quantity = hooks()->apply_filters(
                'item_preview_amount_with_currency',
            app_format_number(($item['qty'] * $item['rate'])),
            [
                'item'       => $item,
                'item_taxes' => $item_taxes,
            ]
            );

            $itemHTML .= '<td class="amount" align="right">' . $item_amount_with_quantity . '</td>';
            $itemHTML .= '</tr>';
            $result['html'] .= $itemHTML;
            $i++;
        }

        if ($rel_data) {
            foreach ($result['taxes'] as $tax) {
                $total_tax = array_sum($tax['total']);
                if ($rel_data->discount_percent != 0 && $rel_data->discount_type == 'before_tax') {
                    $total_tax_tax_calculated = ($total_tax * $rel_data->discount_percent) / 100;
                    $total_tax                = ($total_tax - $total_tax_tax_calculated);
                } elseif ($rel_data->discount_total != 0 && $rel_data->discount_type == 'before_tax') {
                    $t         = ($rel_data->discount_total / $rel_data->subtotal) * 100;
                    $total_tax = ($total_tax - $total_tax * $t / 100);
                }

                $result['taxes'][$tax['tax_name']]['total_tax'] = $total_tax;
                // Tax name is in format NAME|PERCENT
                $tax_name_array                               = explode('|', $tax['tax_name']);
                $result['taxes'][$tax['tax_name']]['taxname'] = $tax_name_array[0];
            }
        }

        // Order taxes by taxrate
        // Lowest tax rate will be on top (if multiple)
        usort($result['taxes'], function ($a, $b) {
            return $a['taxrate'] - $b['taxrate'];
        });

        $rel_data = null;

        return hooks()->apply_filters('before_return_table_items_html_and_taxes', $result, [
            'items'         => $items,
            'type'          => $type,
            'admin_preview' => $admin_preview,
        ]);
    }
}

/**
 * Custom format number function for the app
 * @deprecated 2.3.0 use app_format_number instead
 * @param  mixed  $total
 * @param  boolean $foce_check_zero_decimals whether to force check
 * @return mixed
 */
function _format_number($total, $foce_check_zero_decimals = false)
{
    return app_format_number($total, $foce_check_zero_decimals);
}

/**
 * Function that will loop through taxes and will check if there is 1 tax or multiple
 * @deprecated 2.3.0 because of typo, use multiple_taxes_found_for_item
 * @param  array $taxes
 * @return boolean
 */
function mutiple_taxes_found_for_item($taxes)
{
    $names = [];
    foreach ($taxes as $t) {
        array_push($names, $t['taxname']);
    }
    $names = array_map('unserialize', array_unique(array_map('serialize', $names)));
    if (count($names) == 1) {
        return false;
    }

    return true;
}

/**
 * @deprecated 2.3.0
 * Use theme_assets_url instead
 * Get current template assets url
 * @return string Assets url
 */
function template_assets_url()
{
    return theme_assets_url();
}
/**
 * @deprecated 2.3.0 Use theme_assets_path instead
 * Return active template asset path
 * @return string
 */
function template_assets_path()
{
    return theme_assets_path();
}

if (!function_exists('render_custom_styles')) {
    /**
     * @deprecated
     * Only for backward compatibility in case some old themes are still using this function e.q. in the head
     * This will help to not throw 404 errors
     */
    function render_custom_styles($type)
    {
        return '';
    }
}
