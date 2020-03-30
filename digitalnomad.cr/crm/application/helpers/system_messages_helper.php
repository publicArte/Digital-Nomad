<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!defined('DISABLE_APP_SYSTEM_HELP_MESSAGES') || (defined('DISABLE_APP_SYSTEM_HELP_MESSAGES') && DISABLE_APP_SYSTEM_HELP_MESSAGES)) {
    hooks()->add_action('ticket_created', '_system_popup_message_ticket_form');
    hooks()->add_action('lead_created', '_system_popup_message_web_to_lead_form');
    hooks()->add_action('new_tag_created', '_system_popup_message_tags_styling');
    hooks()->add_action('task_timer_started', '_system_popup_message_timers_with_no_task');
    hooks()->add_action('smtp_test_email_success', '_system_popup_message_email_configured');
    hooks()->add_action('task_checklist_item_created', '_system_popup_task_checklist_items_drag_ability');
}

function _maybe_system_setup_warnings()
{
    // Check for just updates message
    hooks()->add_action('before_start_render_dashboard_content', '_maybe_show_just_updated_message');
    // Check if there is index.html file in the root crm directory, on some servers if this file exists eq default server index.html file the authentication/login page may not work properly
    hooks()->add_action('before_start_render_dashboard_content', '_maybe_static_index_html_file_exists');
    // Show development message
    hooks()->add_action('before_start_render_dashboard_content', '_maybe_show_development_mode_message');
    // Check if cron is required to be configured for some features
    hooks()->add_action('before_start_render_dashboard_content', '_is_cron_setup_required');
    // Check if timezone is set
    hooks()->add_action('before_start_render_dashboard_content', '_maybe_timezone_not_set');
    // Notice for cloudflare rocket loader
    hooks()->add_action('before_start_render_dashboard_content', '_maybe_using_cloudflare_rocket_loader');
    // Notice for iconv extension
    hooks()->add_action('before_start_render_dashboard_content', '_maybe_iconv_needs_to_be_enabled');
    // Check if there is dot in database name, causing problem on upgrade
    hooks()->add_action('before_start_render_dashboard_content', '_maybe_dot_in_database_name');
    // Some hosting providers cast this file as a malicious and may be deleted
    hooks()->add_action('before_start_render_dashboard_content', '_maybe_tcpdf_file_is_missing');
    // Check for cron job running
    hooks()->add_action('before_start_render_dashboard_content', '_maybe_cron_job_is_not_working_properly');
}

/**
 * Check if there is dot in database name and throws warning message.
 * @return void
 */
function _maybe_dot_in_database_name()
{
    if (defined('APP_DB_NAME') && strpos(APP_DB_NAME, '.') !== false && is_admin()) {
        ?>
        <div class="col-md-12">
            <div class="alert alert-warning">
                <h4>Database name (<?php echo APP_DB_NAME; ?>) change required.</h4>
                The system indicated that your database name contains <b>. (dot)</b>, you can encounter upgrading errors when your database name contains dot, it's highly recommended to change your database name to be without dot as example: <?php echo str_replace('.', '', APP_DB_NAME); ?>
                <hr />
                <ul>
                    <li>1. Change the name to be without dot via cPanel/Command line or contact your hosting provider/server administrator to change the name. (use the best method that is suitable for you)</li>
                    <li>2. After the name is changed navigate via ftp or cPanel to application/config/app-config.php and change the database name config constant to your new database name.</li>
                    <li>3. Save the modified app-config.php file.</li>
                </ul>
                <br />
                <small>This message will disappear automatically once the database name won't contain dot.</small>
            </div>
        </div>
        <?php
    }
}

/**
 * Check if there is index.html file in the root crm directory eq default server index.html
 * on some servers if this file exists file, the http://yourdomain/crm/ page may not work properly
 */
function _maybe_static_index_html_file_exists()
{
    if (file_exists(FCPATH . 'index.html') && is_admin()) {
        ?>
        <div class="col-md-12">
            <div class="alert alert-danger">
               <h4>Static <b>index.html</b> file detected in the CRM root directory!</h4>
               The system detected static <b>index.html</b> file in the CRM root directory (<?php echo FCPATH; ?>)
               <br />
               To prevent any unwanted results, you should delete the file <b>index.html</b> and leave only the core index.php file.
           </div>
       </div>
       <?php
    }
}
/**
 * Function that will check if iconv php extension is required based on the usage
 * @return mixed
 */
function _maybe_iconv_needs_to_be_enabled()
{
    if (!extension_loaded('iconv')) {
        ?>
            <div class="col-md-12">
                <div class="alert alert-danger">
                   A required PHP extension is not loaded. You must to enable the <b>iconv</b> php extension in order everything to work properly. You can enable the <b>iconv</b> extension via php.ini, cPanel PHP extensions area or contact your hosting provider to enable this extension.
               </div>
           </div>
           <?php
    }
}

/**
 * Notice for Cloudflare rocket loader usage
 * The application wont work good if cloudflare rocket loader is enabled
 * @return null
 */
function _maybe_using_cloudflare_rocket_loader()
{
    $CI     = &get_instance();
    $header = $CI->input->get_request_header('Cf-Ray');

    if ($header && !empty($header) && get_option('show_cloudflare_notice') == '1' && is_admin()) {
        ob_start(); ?>
        <div class="col-md-12">
            <div class="alert alert-warning font-medium">
                <div class="mtop15"></div>
                <h4><strong>Cloudflare usage detected</strong></h4><hr />
                <ul>
                    <li>When using Cloudflare with the application <strong>you must disable ROCKET LOADER</strong> feature from Cloudflare options in order everything to work properly. <br /><strong><small>NOTE: The script can't check if Rocket Loader is enabled/disabled in your Cloudflare account. If Rocket Loader is already disabled you can ignore this warning.</small></strong></li>
                    <li>
                        <br />
                        <ul>
                            <li><strong>&nbsp;&nbsp;- Disable Rocket Loader for whole domain name</strong></li>
                            <li>&nbsp;&nbsp;&nbsp;&nbsp;Login to your Cloudflare account and click on the <strong>Speed</strong> tab from the top dashboard, search for Rocket Loader and <strong>set to Off</strong>.</li>
                            <br />
                            <li><strong>&nbsp;&nbsp;- Disable Rocket Loader with page rule for application installation url</strong></li>
                            <li>
                                &nbsp;&nbsp;&nbsp;&nbsp;If you do not want to turn off Rocket Loader for the whole domain you can add <a href="https://support.cloudflare.com/hc/en-us/articles/200168306-Is-there-a-tutorial-for-Page-Rules-" target="_blank">page rule</a> that will disable the Rocket Loader only for the application, follow the steps below in order to achieve this.
                                <br /><br />
                                <p class="no-margin">&nbsp;&nbsp;- Login to your Cloudflare account and click on the <strong>Page Rules</strong> tab from the top dashboard</p>
                                <p class="no-margin">&nbsp;&nbsp;- Click on <strong>Create Page Rule</strong></p>
                                <p class="no-margin">&nbsp;&nbsp;- In the url field add the following url: <strong><?php echo rtrim(site_url(), '/') . '/'; ?>*</strong></p>
                                <p class="no-margin">&nbsp;&nbsp;- Click <strong>Add Setting</strong> and search for <strong>Rocket Loader</strong></p>
                                <p class="no-margin">&nbsp;&nbsp;- After you select Rocket Loader <strong>set value to Off</strong></p>
                                <p class="no-margin">&nbsp;&nbsp;- Click <strong>Save and Deploy</strong></p>
                            </li>
                        </ul>
                    </li>
                </ul>
                <br /><br /><a href="<?php echo admin_url('misc/dismiss_cloudflare_notice'); ?>" class="alert-link">Got it! Don't show this message again</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo admin_url('misc/dismiss_cloudflare_notice'); ?>" class="alert-link">Rocket loader is already disabled</a>
            </div>
        </div>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();
        echo $contents;
    }
}

/**
 * Check few timezones statements
 * @return void
 */
function _maybe_timezone_not_set()
{
    if (get_option('default_timezone') == '') {
        echo '<div class="col-md-12">';
        echo '<div class="alert alert-danger">';
        echo '<strong>Default timezone not set. Navigate to Setup->Settings->Localization to set default system timezone.</strong>';
        echo '</div>';
        echo '</div>';
    } else {
        if (!in_array(get_option('default_timezone'), array_flatten(get_timezones_list()))) {
            echo '<div class="col-md-12">';
            echo '<div class="alert alert-danger">';
            echo '<strong>We updated the timezone logic for the app. Seems like your previous timezone do not fit with the new logic. Navigate to Setup->Settings->Localization to set new proper timezone.</strong>';
            echo '</div>';
            echo '</div>';
        }
    }
}

/**
 * Check if there is usage of some features that requires cron job to be setup
 * If the script found results will output a message inside the admin area only for admins
 * @return void
 */
function _is_cron_setup_required()
{
    if (get_option('cron_has_run_from_cli') == 0) {
        if (is_admin()) {
            $used_features       = [];
            $using_cron_features = 0;
            $feature             = total_rows(db_prefix().'reminders');
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Reminders');
            }

            $feature = get_option('email_queue_enabled');
            $using_cron_features += $feature;
            if ($feature == 1) {
                array_push($used_features, 'Email Queue');
            }

            $feature = total_rows(db_prefix().'leads_email_integration', [
                'active' => 1,
            ]);
            $using_cron_features += $feature;

            if ($feature > 0) {
                array_push($used_features, 'Auto importing leads from email.');
            }
            $feature = total_rows(db_prefix().'invoices', [
                'recurring >' => 0,
            ]);
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Recurring Invoices');
            }
            $feature = total_rows(db_prefix().'expenses', [
                'recurring' => 1,
            ]);
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Recurring Expenses');
            }

            $feature = total_rows(db_prefix().'tasks', [
                'recurring' => 1,
            ]);
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Recurring Tasks');
            }

            $feature = total_rows(db_prefix().'events');
            $using_cron_features += $feature;

            if ($feature > 0) {
                array_push($used_features, 'Custom Calendar Events');
            }

            $feature = total_rows(db_prefix().'departments', [
                'host !='     => '',
                'password !=' => '',
                'email !='    => '',
            ]);
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Auto Import Tickets via method IMAP (Setup->Support->Departments)');
            }

            $using_cron_features = hooks()->apply_filters('numbers_of_features_using_cron_job', $using_cron_features);
            $used_features       = hooks()->apply_filters('used_cron_features', $used_features);

            if ($using_cron_features > 0 && get_option('hide_cron_is_required_message') == 0) {
                echo '<div class="col-md-12">';
                echo '<div class="alert alert-danger">';
                echo 'You are using some features that requires cron job setup to work properly.';
                echo '<br />Please follow the cron <a href="https://help.perfexcrm.com/setup-cron-job/" target="_blank">setup guide</a> in order all features to work well.';
                echo '<br /><br /><br />';
                echo '<p class="bold">You are using the following features that CRON Job setup is required:</p>';
                $i = 1;
                foreach ($used_features as $feature) {
                    echo '&nbsp;' . $i . '. ' . $feature . '<br />';
                    $i++;
                }
                echo '<br /><br /><a href="' . admin_url('misc/dismiss_cron_setup_message') . '" class="alert-link">Don\'t show this message again</a>';
                echo '</div>';
                echo '</div>';
            }
        }
    }
}

/**
 * Show message on dashboard when environment is set to development or testing
 * @return void
 */
function _maybe_show_development_mode_message()
{
    if (ENVIRONMENT == 'development' || ENVIRONMENT == 'testing') {
        if (is_admin()) {
            echo '<div class="col-md-12">';
            echo '<div class="alert alert-warning">';
            echo 'Environment set to <b>' . ENVIRONMENT . '</b>. Don\'t forget to set back to <b>production</b> in the main index.php file after finishing your tests.';
            echo '</div>';
            echo '</div>';
        }
    }
}

/**
 * On each update there is message/code inserted in the database
 */
function _maybe_show_just_updated_message()
{
    if (get_option('update_info_message') != '') {
        if (is_admin()) {
            $message = get_option('update_info_message');
            update_option('update_info_message', '');
            echo $message;
        }
    }
}

function _system_popup_message_ticket_form($ticket_id)
{
    if ($ticket_id == 1) {
        set_system_popup('First Ticket Created! <br /> <span style="font-size:26px;">Did you know that you can embed Ticket Form (Setup->Settings->Support->Ticket Form) directly in your websites?</span>');
    }
}

function _system_popup_message_web_to_lead_form($lead_id)
{
    if ($lead_id == 1) {
        set_system_popup('First Leads Created! <br /> <span style="font-size:26px;">You can use Web To Lead Forms (Setup->Leads->Web To Lead) to capture leads directly from your website.</span>');
    }
}
function _system_popup_message_tags_styling($tag_id)
{
    if ($tag_id == 1) {
        set_system_popup('Congrats! You created the first tags! <br /> Did you know that you can apply color to tags in Setup->Theme Style?');
    }
}

function _system_popup_message_email_configured()
{
    if (get_option('smtp_email') != '' && get_option('email_protocol') == 'smtp' && get_option('smtp_host') != '') {
        if (get_option('_smtp_test_email_success') === '') {
            set_system_popup('Congrats! You configured the email feature successfully! <br /> <span style="font-size:26px;">You can disable any emails that you don\'t want to be sent in Setup->Email Templates.</span>');
            add_option('_smtp_test_email_success', 1, 0);
        }
    }
}
function _maybe_tcpdf_file_is_missing()
{
    $path = APPPATH . 'vendor/tecnickcom/tcpdf/tcpdf.php';
    if (!file_exists($path) && is_admin()) {
        ?>
        <div class="col-md-12">
            <div class="alert alert-warning">
                <h4 style="margin-top:15px;"><b>Missing TCPDF core file.</b></h4>
                <hr />
                <p>The <b>file responsible for generating PDF documents is missing in your installation</b>. The system was unable to determine if this file really exists, the file should be located in: <b><?php echo $path; ?></b></p>
                <p style="margin-top:15px;">This can happen because of <b>2 reasons</b>:</p>
                <ul style="margin-top:15px;">
                  <li>
                    1. Your hosting provider/server firewall <b>removed</b> the <b>tcpdf.php</b> file located in <b><?php echo $path; ?></b> because the firewall think that is malicious file, mostly happens because of not properly configured firewall rules. <br />
                    You will need to contact your hosting provider to whitelist this file, after the file is whitelisted download the core files again and locate this file inside the zip folder, upload the file in: <b> <?php echo APPPATH . 'vendor/tecnickcom/tcpdf/'; ?></b>
                </li>
                <li><br />2. The file is not uploaded or is skipped during upload, you can download the core files again and locate this file inside the zip folder, after that upload the file in: <b> <?php echo APPPATH . 'vendor/tecnickcom/tcpdf/'; ?></b></li>
            </ul>
        </div>
    </div>
    <?php
    }
}
function show_pdf_unable_to_get_image_size_error()
{
    ?>
    <div style="font-size:17px;">
     <hr />
     <p>This error can be shown if the <b>PDF library can't read the image from your server</b>.</p>
     <p>Very often this is happening <b>when you are using custom PDF logo url in Setup -> Settings -> PDF</b>, first make sure that the url you added in Setup->Settings->PDF for the custom pdf logo is valid and the image exists if the problem still exists you will need to use a <b>direct path</b> to the image to include in the PDF documents. Follow the steps mentioned below:</p>
     <p><strong>Method 1 (easy)</strong></p>
     <ul>
        <li>Upload the logo image in the installation directory eq. <?php echo FCPATH; ?>mylogo.jpg</li>
        <li><a href="<?php echo admin_url('settings?group=pdf'); ?>" target="_blank">Navigate to Setup -> Settings -> PDF</a> -> Custom PDF Company Logo URL and only add the filename like: <b>mylogo.jpg</b>, now Custom PDF Company Logo URL should be only filename not full URL.</li>
        <li>Try to re-generate PDF document again.</li>
    </ul>
    <p><strong>Method 2 (advanced)</strong></p>
    <small>Try this method if method 1 is still not working.</small>
    <ul>
        <li>Consult with your hosting provider to confirm that the server is able to use PHP's <a href="http://php.net/manual/en/function.file-get-contents.php" target="_blank">file_get_contents</a> or <a href="http://php.net/manual/en/curl.examples-basic.php" target="_blank">cUrl</a> to download the file. </li>
        <li>Try to re-generate PDF document again.</li>
    </ul>
    <?php if (strpos($_SERVER['REQUEST_URI'], '/proposals') !== false) {
        ?>
        <hr />
        <p>Additionally, if this PDF document is proposal, you may need to re-check if any images added inside the proposal content are broken, make sure that the images URL are actually valid.</p>
        <?php
    } ?>
    </div>
    <?php
}

function _maybe_cron_job_is_not_working_properly()
{
    $last_cron_run = get_option('last_cron_run');
    $fromCli       = get_option('cron_has_run_from_cli');
    $hoursCheck    = 48;
    if ($last_cron_run != '' && $fromCli == '1' && is_admin()) {
        if ($last_cron_run <= strtotime('-' . $hoursCheck . ' hours')) {

            // Check and clean locks for all cases if the cron somehow is stuck or locked
            if (file_exists(get_temp_dir() . 'pcrm-cron-lock')) {
                @unlink(get_temp_dir() . 'pcrm-cron-lock');
            }

            if (file_exists(TEMP_FOLDER . 'pcrm-cron-lock')) {
                @unlink(TEMP_FOLDER . 'pcrm-cron-lock');
            } ?>
                <div class="col-md-12">
                        <div class="alert alert-warning">
                            <h4><b>Cron Job Warning</b></h4>
                            <hr class="hr-10" />
                            <p>
                                 <b>Seems like your cron job hasn't run in the last <?php echo $hoursCheck; ?> hours</b>, you should re-check if your cron job is properly configured, this message will auto disappear after 5 minutes after the cron job starts working properly again.
                            </p>
                    </div>
                </div>
           <?php
        }
    }
}

function _system_popup_message_timers_with_no_task($data)
{
    $task_id  = $data['task_id'];
    $timer_id = $data['timer_id'];
    if ($task_id != '0' && $timer_id == 1) {
        set_system_popup('First Timer Started!<br />
            <span style="font-size:26px;">Did you know that you can start a timer without task and assign the timer to task afterward?</span><br /><br /><img alt="timer-start" class="img-responsive center-block" src="data:image/png;base64,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">');
    }
}


function _system_popup_task_checklist_items_drag_ability($data)
{
    $item_id = $data['checklist_id'];
    $task_id = $data['task_id'];
    if (($task_id == 1 || $task_id == 2 || $task_id == 3 || $task_id == 4 || $task_id == 5) && $item_id == 8) {
        set_system_popup('Seems like that you are enjoying creating tasks checklist items, did you know that you can easily re-order the items by dragging them above or below? <br /><br />
         <img alt="checklist-items-drag" class="img-responsive center-block" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAAClCAYAAACqcw9sAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1Nzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjI3ODFDQjEyQkNCMzExRTdBQTA1OEI3NTE4MzhCRDlDIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjI3ODFDQjEzQkNCMzExRTdBQTA1OEI3NTE4MzhCRDlDIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6Mjc4MUNCMTBCQ0IzMTFFN0FBMDU4Qjc1MTgzOEJEOUMiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6Mjc4MUNCMTFCQ0IzMTFFN0FBMDU4Qjc1MTgzOEJEOUMiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz51tX63AAAeSklEQVR42uzd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">');
    }
}
