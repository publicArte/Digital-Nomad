<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If setss to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESCTRUCTIVE') OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/**
 * Used for phpass
 */
define('PHPASS_HASH_STRENGTH', 8);
define('PHPASS_HASH_PORTABLE', FALSE);

/**
 * Admin URL
 */
define('ADMIN_URL', 'admin');
/**
 * Admin URI
 * CUSTOM_ADMIN_URL is not yet tested well, don't define it
 */
define('ADMIN_URI', DEFINED('CUSTOM_ADMIN_URL') ? CUSTOM_ADMIN_URL : ADMIN_URL);

/**
 * CRM server update url
 */
define('UPDATE_URL','https://www.perfexcrm.com/perfex_updates/index.php');

/**
 * Get latest version info
 */
define('UPDATE_INFO_URL','https://www.perfexcrm.com/perfex_updates/update_info.php');

/**
 * Do not send sms to data eq. invoices, estimates older then X days.
 */
if(!defined('DO_NOT_SEND_SMS_ON_DATA_OLDER_THEN')){
    define('DO_NOT_SEND_SMS_ON_DATA_OLDER_THEN', 45);
}

if(!defined('CUSTOM_FIELD_TRANSFER_SIMILARITY')){
    define('CUSTOM_FIELD_TRANSFER_SIMILARITY', 85);
}

/**
 * CRM temporary path
 */
define('TEMP_FOLDER',FCPATH .'temp' . '/');

/**
 * Customer attachments folder from profile
 */
define('CLIENT_ATTACHMENTS_FOLDER',FCPATH.'uploads/clients'.'/');
/**
 * All tickets attachments
 */
define('TICKET_ATTACHMENTS_FOLDER',FCPATH .'uploads/ticket_attachments' . '/');
/**
 * Company attachments, favicon, logo etc..
 */
define('COMPANY_FILES_FOLDER',FCPATH .'uploads/company' . '/');
/**
 * Staff profile images
 */
define('STAFF_PROFILE_IMAGES_FOLDER',FCPATH .'uploads/staff_profile_images' . '/');
/**
 * Contact profile images
 */
define('CONTACT_PROFILE_IMAGES_FOLDER',FCPATH .'uploads/client_profile_images' . '/');
/**
 * Newsfeed attachments
 */
define('NEWSFEED_FOLDER',FCPATH . 'uploads/newsfeed' . '/');
/**
 * Contracts attachments
 */
define('CONTRACTS_UPLOADS_FOLDER',FCPATH . 'uploads/contracts' . '/');
/**
 * Tasks attachments
 */
define('TASKS_ATTACHMENTS_FOLDER',FCPATH . 'uploads/tasks' . '/');
/**
 * Invoice attachments
 */
define('INVOICE_ATTACHMENTS_FOLDER',FCPATH . 'uploads/invoices' . '/');
/**
 * Estimate attachments
 */
define('ESTIMATE_ATTACHMENTS_FOLDER',FCPATH . 'uploads/estimates' . '/');
/**
 * Proposal attachments
 */
define('PROPOSAL_ATTACHMENTS_FOLDER',FCPATH . 'uploads/proposals' . '/');
/**
 * Expenses receipts
 */
define('EXPENSE_ATTACHMENTS_FOLDER',FCPATH . 'uploads/expenses' . '/');
/**
 * Lead attachments
 */
define('LEAD_ATTACHMENTS_FOLDER',FCPATH . 'uploads/leads' . '/');
/**
 * Project files attachments
 */
define('PROJECT_ATTACHMENTS_FOLDER',FCPATH . 'uploads/projects' . '/');
/**
 * Project discussions attachments
 */
define('PROJECT_DISCUSSION_ATTACHMENT_FOLDER',FCPATH . 'uploads/discussions' . '/');
/**
 * Credit notes attachment folder
 */
define('CREDIT_NOTES_ATTACHMENTS_FOLDER',FCPATH.'uploads/credit_notes' . '/');
/**
 * Modules Path
 */
define('APP_MODULES_PATH', FCPATH . 'modules/');
/**
 * Helper libraries path
 */
define('LIBSPATH', APPPATH.'libraries/');
