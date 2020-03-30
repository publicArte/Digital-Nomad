<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Register core merge fields builder classes
 * This function is used by filter in core_hooks_helper.php
 * @param  array $fields current registered fields
 * @return array
 */
function core_merge_fields($fields)
{
    $fields[] = 'merge_fields/staff_merge_fields';
    $fields[] = 'merge_fields/client_merge_fields';
    $fields[] = 'merge_fields/credit_note_merge_fields';
    $fields[] = 'merge_fields/subscriptions_merge_fields';
    $fields[] = 'merge_fields/ticket_merge_fields';
    $fields[] = 'merge_fields/contract_merge_fields';
    $fields[] = 'merge_fields/invoice_merge_fields';
    $fields[] = 'merge_fields/estimate_merge_fields';
    $fields[] = 'merge_fields/tasks_merge_fields';
    $fields[] = 'merge_fields/proposals_merge_fields';
    $fields[] = 'merge_fields/leads_merge_fields';
    $fields[] = 'merge_fields/projects_merge_fields';
    $fields[] = 'merge_fields/other_merge_fields';

    return $fields;
}

/**
 * All available merge fields for templates are defined here
 * @return array
 */
function get_available_merge_fields()
{
    $registered = get_instance()->app_merge_fields->all();

    return hooks()->apply_filters('available_merge_fields', $registered);
}

/**
 * General merge fields not linked to any features
 * @return array
 */
function get_other_merge_fields()
{
    $CI = &get_instance();

    if (!class_exists('other_merge_fields', false)) {
        $CI->load->library('merge_fields/other_merge_fields');
    }

    return $CI->other_merge_fields->format();
}
