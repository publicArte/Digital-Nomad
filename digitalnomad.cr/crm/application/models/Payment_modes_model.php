<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payment_modes_model extends CRM_Model
{
    private $payment_gateways = [];

    public function __construct()
    {
        parent::__construct();

        /**
         * @deprecated 2.3.0
         * @var array
         */
        $this->payment_gateways = hooks()->apply_filters('before_add_online_payment_modes', []);

        /**
         * New filter
         * @var array
         * When the deprecated filter is removed, change $this->payment_gateways to []
         */
        $this->payment_gateways = hooks()->apply_filters('before_add_payment_gateways', $this->payment_gateways);
    }

    /**
     * Get payment mode
     * @param  string  $id    payment mode id
     * @param  array   $where additional where only for offline modes
     * @param  boolean $include_inactive   whether to include inactive too
     * @param  boolean $force force if it's inactive to return it back
     * @return array
     */
    public function get($id = '', $where = [], $include_inactive = false, $force = false)
    {
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'payment_modes')->row();
        } elseif (!empty($id)) {
            foreach ($this->payment_gateways as $gateway) {
                if ($gateway['id'] == $id) {
                    if ($gateway['active'] == 0 && $force == false) {
                        continue;
                    }
                    $mode                      = new stdCLass();
                    $mode->id                  = $id;
                    $mode->name                = $gateway['name'];
                    $mode->description         = $gateway['description'];
                    $mode->selected_by_default = $gateway['selected_by_default'];
                    $mode->show_on_pdf         = 0;

                    return $mode;
                }
            }

            return false;
        }
        if ($include_inactive !== true) {
            $this->db->where('active', 1);
        }
        $modes = $this->db->get(db_prefix() . 'payment_modes')->result_array();
        $modes = array_merge($modes, $this->get_payment_gateways($include_inactive));

        return $modes;
    }

    /**
     * Add new payment mode
     * @param array $data payment mode $_POST data
     */
    public function add($data)
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }
        if (!isset($data['active'])) {
            $data['active'] = 0;
        } else {
            $data['active'] = 1;
        }

        if (!isset($data['invoices_only'])) {
            $data['invoices_only'] = 0;
        } else {
            $data['invoices_only'] = 1;
        }
        if (!isset($data['expenses_only'])) {
            $data['expenses_only'] = 0;
        } else {
            $data['expenses_only'] = 1;
        }

        if (!isset($data['show_on_pdf'])) {
            $data['show_on_pdf'] = 0;
        } else {
            $data['show_on_pdf'] = 1;
        }

        if (!isset($data['selected_by_default'])) {
            $data['selected_by_default'] = 0;
        } else {
            $data['selected_by_default'] = 1;
        }

        $this->db->insert(db_prefix() . 'payment_modes', [
            'name'                => $data['name'],
            'description'         => nl2br_save_html($data['description']),
            'active'              => $data['active'],
            'expenses_only'       => $data['expenses_only'],
            'invoices_only'       => $data['invoices_only'],
            'show_on_pdf'         => $data['show_on_pdf'],
            'selected_by_default' => $data['selected_by_default'],
        ]);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Payment Mode Added [ID: ' . $insert_id . ', Name:' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Update payment mode
     * @param  array $data payment mode $_POST data
     * @return boolean
     */
    public function edit($data)
    {
        $id = $data['paymentmodeid'];
        unset($data['paymentmodeid']);
        if (!isset($data['active'])) {
            $data['active'] = 0;
        } else {
            $data['active'] = 1;
        }


        if (!isset($data['show_on_pdf'])) {
            $data['show_on_pdf'] = 0;
        } else {
            $data['show_on_pdf'] = 1;
        }


        if (!isset($data['selected_by_default'])) {
            $data['selected_by_default'] = 0;
        } else {
            $data['selected_by_default'] = 1;
        }


        if (!isset($data['invoices_only'])) {
            $data['invoices_only'] = 0;
        } else {
            $data['invoices_only'] = 1;
        }
        if (!isset($data['expenses_only'])) {
            $data['expenses_only'] = 0;
        } else {
            $data['expenses_only'] = 1;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'payment_modes', [
            'name'                => $data['name'],
            'description'         => nl2br_save_html($data['description']),
            'active'              => $data['active'],
            'expenses_only'       => $data['expenses_only'],
            'invoices_only'       => $data['invoices_only'],
            'show_on_pdf'         => $data['show_on_pdf'],
            'selected_by_default' => $data['selected_by_default'],
        ]);

        if ($this->db->affected_rows() > 0) {
            logActivity('Payment Mode Updated [ID: ' . $id . ', Name:' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete payment mode from database
     * @param  mixed $id payment mode id
     * @return mixed / if referenced array else boolean
     */
    public function delete($id)
    {
        // Check if the payment mode is using in the invoiec payment records table.
        if (is_reference_in_table('paymentmode', db_prefix() . 'invoicepaymentrecords', $id)
            || is_reference_in_table('paymentmode', db_prefix() . 'expenses', $id)) {
            return [
                'referenced' => true,
            ];
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'payment_modes');
        if ($this->db->affected_rows() > 0) {
            logActivity('Payment Mode Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * @since  2.3.0
     * Get payment gateways
     * @param  boolean $include_inactive whether to include the inactive ones too
     * @return array
     */
    public function get_payment_gateways($include_inactive = false)
    {
        $modes = [];
        foreach ($this->payment_gateways as $mode) {
            if ($include_inactive !== true) {
                if ($mode['active'] == 0) {
                    continue;
                }
            }
            $modes[] = $mode;
        }

        return $modes;
    }

    /**
     * Get all online payment modes
     * @deprecated 2.3.0 use get_payment_gateways instead
     * @since   1.0.1
     * @return array payment modes
     */
    public function get_online_payment_modes($all = false)
    {
        return $this->get_payment_gateways($all);
    }

    /**
     * @since  Version 1.0.1
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update payment mode status Active/Inactive
     */
    public function change_payment_mode_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'payment_modes', [
            'active' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            logActivity('Payment Mode Status Changed [ModeID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }

    /**
     * @since  Version 1.0.1
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update payment mode show to client Active/Inactive
     */
    public function change_payment_mode_show_to_client_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'payment_modes', [
            'showtoclient' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            logActivity('Payment Mode Show to Client Changed [ModeID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }

    /**
     * Inject custom payment gateway into the payment gateways array
     * @param string $gateway_name payment gateway name, should equal like the libraries/classname
     * @param string $module       module name to load the gateway if not already loaded
     */
    public function add_payment_gateway($gateway_name, $module = null)
    {
        if (!class_exists($gateway_name, false) && $module) {
            $this->load->library($module . '/' . $gateway_name);
        }

        $this->payment_gateways = $this->{$gateway_name}->initMode($this->payment_gateways);
    }
}
