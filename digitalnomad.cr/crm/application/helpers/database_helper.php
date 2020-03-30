<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Check if field is used in table
 * @param  string  $field column
 * @param  string  $table table name to check
 * @param  integer  $id   ID used
 * @return boolean
 */
function is_reference_in_table($field, $table, $id)
{
    $CI = & get_instance();
    $CI->db->where($field, $id);
    $row = $CI->db->get($table)->row();
    if ($row) {
        return true;
    }

    return false;
}

/**
 * Return last system activity id
 * @return mixed
 */
function get_last_system_activity_id()
{
    $CI = &get_instance();
    $CI->db->select('id');
    $CI->db->order_by('id', 'desc');
    $CI->db->limit(1);

    return $CI->db->get(db_prefix() . 'activity_log')->row();
}
/**
 * Function that add views tracking for proposals,estimates,invoices,knowledgebase article in database.
 * This function tracks activity only per hour
 * Eq customer viewed invoice at 15:00 and then 15:05 the activity will be tracked only once.
 * If customer view the invoice again in 16:01 there will be activity tracked.
 * @param string $rel_type
 * @param mixed $rel_id
 */
function add_views_tracking($rel_type, $rel_id)
{
    $CI = & get_instance();
    if (!is_staff_logged_in()) {
        $CI->db->where('rel_id', $rel_id);
        $CI->db->where('rel_type', $rel_type);
        $CI->db->order_by('id', 'DESC');
        $CI->db->limit(1);
        $row = $CI->db->get(db_prefix() . 'views_tracking')->row();
        if ($row) {
            $dateFromDatabase = strtotime($row->date);
            $date1HourAgo     = strtotime('-1 hours');
            if ($dateFromDatabase >= $date1HourAgo) {
                return false;
            }
        }
    } else {
        // Staff logged in, nothing to do here
        return false;
    }

    hooks()->do_action('before_insert_views_tracking', [
    'rel_id'   => $rel_id,
    'rel_type' => $rel_type,
    ]);

    $notifiedUsers     = [];
    $members           = [];
    $notification_data = [];
    if ($rel_type == 'invoice' || $rel_type == 'proposal' || $rel_type == 'estimate') {
        $responsible_column = 'sale_agent';

        if ($rel_type == 'invoice') {
            $table                    = db_prefix() . 'invoices';
            $notification_link        = 'invoices/list_invoices/' . $rel_id;
            $notification_description = 'not_customer_viewed_invoice';
            array_push($notification_data, format_invoice_number($rel_id));
        } elseif ($rel_type == 'estimate') {
            $table                    = db_prefix() . 'estimates';
            $notification_link        = 'estimates/list_estimates/' . $rel_id;
            $notification_description = 'not_customer_viewed_estimate';
            array_push($notification_data, format_estimate_number($rel_id));
        } else {
            $responsible_column       = 'assigned';
            $table                    = db_prefix() . 'proposals';
            $notification_description = 'not_customer_viewed_proposal';
            $notification_link        = 'proposals/list_proposals/' . $rel_id;
            array_push($notification_data, format_proposal_number($rel_id));
        }

        $notification_data = serialize($notification_data);

        $CI->db->select('addedfrom,' . $responsible_column)
    ->where('id', $rel_id);

        $rel = $CI->db->get($table)->row();

        $CI->db->select('staffid')
    ->where('staffid', $rel->addedfrom)
    ->or_where('staffid', $rel->{$responsible_column});

        $members = $CI->db->get(db_prefix() . 'staff')->result_array();
    }

    $CI->db->insert(db_prefix() . 'views_tracking', [
    'rel_id'   => $rel_id,
    'rel_type' => $rel_type,
    'date'     => date('Y-m-d H:i:s'),
    'view_ip'  => $CI->input->ip_address(),
    ]);

    $view_id = $CI->db->insert_id();
    if ($view_id) {
        foreach ($members as $member) {
            $notification = [
            'fromcompany'     => true,
            'touserid'        => $member['staffid'],
            'description'     => $notification_description,
            'link'            => $notification_link,
            'additional_data' => $notification_data,
            ];
            if (is_client_logged_in()) {
                unset($notification['fromcompany']);
            }
            $notified = add_notification($notification);
            if ($notified) {
                array_push($notifiedUsers, $member['staffid']);
            }
        }
        pusher_trigger_notification($notifiedUsers);
    }
}
/**
 * Get views tracking based on rel type and rel id
 * @param  string $rel_type
 * @param  mixed $rel_id
 * @return array
 */
function get_views_tracking($rel_type, $rel_id)
{
    $CI = & get_instance();
    $CI->db->where('rel_id', $rel_id);
    $CI->db->where('rel_type', $rel_type);
    $CI->db->order_by('date', 'DESC');

    return $CI->db->get(db_prefix() . 'views_tracking')->result_array();
}

/**
 * Log Activity for everything
 * @param  string $description Activity Description
 * @param  integer $staffid    Who done this activity
 */
function logActivity($description, $staffid = null)
{
    $CI  = & get_instance();
    $log = [
        'description' => $description,
        'date'        => date('Y-m-d H:i:s'),
        ];
    if (!DEFINED('CRON')) {
        if ($staffid != null && is_numeric($staffid)) {
            $log['staffid'] = get_staff_full_name($staffid);
        } else {
            if (!is_client_logged_in()) {
                if (is_staff_logged_in()) {
                    $log['staffid'] = get_staff_full_name(get_staff_user_id());
                } else {
                    $log['staffid'] = null;
                }
            } else {
                $log['staffid'] = get_contact_full_name(get_contact_user_id());
            }
        }
    } else {
        // manually invoked cron
        if (is_staff_logged_in()) {
            $log['staffid'] = get_staff_full_name(get_staff_user_id());
        } else {
            $log['staffid'] = '[CRON]';
        }
    }

    $CI->db->insert(db_prefix() . 'activity_log', $log);
}
/**
 * Add user notifications
 * @param array $values array of values [description,fromuserid,touserid,fromcompany,isread]
 */
function add_notification($values)
{
    $CI = & get_instance();
    foreach ($values as $key => $value) {
        $data[$key] = $value;
    }
    if (is_client_logged_in()) {
        $data['fromuserid']    = 0;
        $data['fromclientid']  = get_contact_user_id();
        $data['from_fullname'] = get_contact_full_name(get_contact_user_id());
    } else {
        $data['fromuserid']    = get_staff_user_id();
        $data['fromclientid']  = 0;
        $data['from_fullname'] = get_staff_full_name(get_staff_user_id());
    }

    if (isset($data['fromcompany'])) {
        unset($data['fromuserid']);
        unset($data['from_fullname']);
    }

    $data['date'] = date('Y-m-d H:i:s');
    $data         = hooks()->apply_filters('notification_data', $data);

    // Prevent sending notification to non active users.
    if (isset($data['touserid']) && $data['touserid'] != 0) {
        $CI->db->where('staffid', $data['touserid']);
        $user = $CI->db->get(db_prefix() . 'staff')->row();
        if (!$user || $user && $user->active == 0) {
            return false;
        }
    }

    $CI->db->insert(db_prefix() . 'notifications', $data);

    if ($notification_id = $CI->db->insert_id()) {
        hooks()->do_action('notification_created', $notification_id);
    }

    return true;
}
/**
 * Count total rows on table based on params
 * @param  string $table Table from where to count
 * @param  array  $where
 * @return mixed  Total rows
 */
function total_rows($table, $where = [])
{
    $CI = & get_instance();
    if (is_array($where)) {
        if (sizeof($where) > 0) {
            $CI->db->where($where);
        }
    } elseif (strlen($where) > 0) {
        $CI->db->where($where);
    }

    return $CI->db->count_all_results($table);
}
/**
 * Sum total from table
 * @param  string $table table name
 * @param  array  $attr  attributes
 * @return mixed
 */
function sum_from_table($table, $attr = [])
{
    if (!isset($attr['field'])) {
        show_error('sum_from_table(); function expect field to be passed.');
    }

    $CI = & get_instance();
    if (isset($attr['where']) && is_array($attr['where'])) {
        $i = 0;
        foreach ($attr['where'] as $key => $val) {
            if (is_numeric($key)) {
                $CI->db->where($val);
                unset($attr['where'][$key]);
            }
            $i++;
        }
        $CI->db->where($attr['where']);
    }
    $CI->db->select_sum($attr['field']);
    $CI->db->from($table);
    $result = $CI->db->get()->row();

    return $result->{$attr['field']};
}

/**
 * Prefix field name with table ex. table.column
 * @param  string $table
 * @param  string $alias
 * @param  string $field field to check
 * @return string
 */
function prefixed_table_fields_wildcard($table, $alias, $field)
{
    $CI          = & get_instance();
    $columns     = $CI->db->query("SHOW COLUMNS FROM $table")->result_array();
    $field_names = [];
    foreach ($columns as $column) {
        $field_names[] = $column['Field'];
    }
    $prefixed = [];
    foreach ($field_names as $field_name) {
        if ($field == $field_name) {
            $prefixed[] = "`{$alias}`.`{$field_name}` AS `{$alias}.{$field_name}`";
        }
    }

    return implode(', ', $prefixed);
}
/**
 * Prefix all columns from table with the table name
 * Used for select statements eq db_prefix().'clients.company'
 * @param  string $table table name
 * @param  array $exclude exclude fields from prefixing
 * @return array
 */
function prefixed_table_fields_array($table, $string = false, $exclude = [])
{
    $CI     = & get_instance();
    $fields = $CI->db->list_fields($table);

    foreach ($exclude as $field) {
        if (in_array($field, $fields)) {
            unset($fields[array_search($field, $fields)]);
        }
    }

    $fields = array_values($fields);

    $i = 0;
    foreach ($fields as $f) {
        $fields[$i] = $table . '.' . $f;
        $i++;
    }

    return $string == false ? $fields : implode(',', $fields);
}

/**
 * Prefix all columns from table with the table name
 * Used for select statements eq db_prefix().'clients.company'
 * @param  string $table table name
 * @param  array $exclude exclude fields from prefixing
 * @return string
 */
function prefixed_table_fields_string($table, $exclude = [])
{
    return prefixed_table_fields_array($table, true, $exclude);
}
/**
 * Get department email address
 * @param  mixed $id department id
 * @return mixed
 */
function get_department_email($id)
{
    $CI = & get_instance();
    $CI->db->select('email');
    $CI->db->where('departmentid', $id);

    return $CI->db->get(db_prefix() . 'departments')->row()->email;
}

if (! function_exists('add_foreign_key')) {
    /**
     * @param string $table       Table name
     * @param string $foreign_key Collumn name having the Foreign Key
     * @param string $references  Table and column reference. Ex: users(id)
     * @param string $on_delete   RESTRICT, NO ACTION, CASCADE, SET NULL, SET DEFAULT
     * @param string $on_update   RESTRICT, NO ACTION, CASCADE, SET NULL, SET DEFAULT
     *
     * @return string SQL command
     */
    function add_foreign_key($table, $foreign_key, $references, $on_delete = 'RESTRICT', $on_update = 'RESTRICT')
    {
        $references = explode('(', str_replace(')', '', str_replace('`', '', $references)));

        return "ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_{$foreign_key}_fk` FOREIGN KEY (`{$foreign_key}`) REFERENCES `{$references[0]}`(`{$references[1]}`) ON DELETE {$on_delete} ON UPDATE {$on_update}";
    }
}

if (! function_exists('drop_foreign_key')) {
    /**
     * @param string $table       Table name
     * @param string $foreign_key Collumn name having the Foreign Key
     *
     * @return string SQL command
     */
    function drop_foreign_key($table, $foreign_key)
    {
        return "ALTER TABLE `{$table}` DROP FOREIGN KEY `{$table}_{$foreign_key}_fk`";
    }
}

if (! function_exists('add_trigger')) {
    /**
     * @param string $trigger_name Trigger name
     * @param string $table        Table name
     * @param string $statement    Command to run
     * @param string $time         BEFORE or AFTER
     * @param string $event        INSERT, UPDATE or DELETE
     * @param string $type         FOR EACH ROW [FOLLOWS|PRECEDES]
     *
     * @return string SQL Command
     */
    function add_trigger($trigger_name, $table, $statement, $time = 'BEFORE', $event = 'INSERT', $type = 'FOR EACH ROW')
    {
        return 'DELIMITER ;;' . PHP_EOL . "CREATE TRIGGER `{$trigger_name}` {$time} {$event} ON `{$table}` {$type}" . PHP_EOL . 'BEGIN' . PHP_EOL . $statement . PHP_EOL . 'END;' . PHP_EOL . 'DELIMITER ;;';
    }
}

if (! function_exists('drop_trigger')) {
    /**
     * @param string $trigger_name Trigger name
     *
     * @return string SQL Command
     */
    function drop_trigger($trigger_name)
    {
        return "DROP TRIGGER {$trigger_name};";
    }
}

/**
 * Check whether table exists
 * Custom function because Codeigniter is caching the tables and this is causing issues in migrations
 * @param  string $table table name to check
 * @return boolean
 */
function table_exists($table)
{
    if (!startsWith($table, db_prefix())) {
        $table = db_prefix() . $table;
    }

    $result = get_instance()->db->query("SELECT *
            FROM information_schema.tables
            WHERE table_schema = '" . APP_DB_NAME . "'
                AND table_name = '" . $table . "'
            LIMIT 1;")->row();

    return (bool) $result;
}
