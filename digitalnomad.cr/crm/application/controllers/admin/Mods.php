<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Mods extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();

        /**
         * Modules are only accessible by administrators
         */
        if (!is_admin()) {
            redirect(admin_url());
        }
    }

    public function index()
    {
        $data['modules'] = $this->app_modules->get();
        $data['title']   = _l('modules');
        $this->load->view('admin/modules/list', $data);
    }

    public function activate($name)
    {
        $this->app_modules->activate($name);
        $this->to_modules();
    }

    public function deactivate($name)
    {
        $this->app_modules->deactivate($name);
        $this->to_modules();
    }

    public function uninstall($name)
    {
        $this->app_modules->uninstall($name);
        $this->to_modules();
    }

    public function upload()
    {
        $data = $this->app_modules->upload();

        if ($data['error']) {
            set_alert('danger', $data['error']);
        } else {
            set_alert('success', 'Module uploaded successfully');
        }

        $this->to_modules();
    }

    public function upgrade_database($name)
    {
        $result = $this->app_modules->upgrade_database($name);

        // Possible error
        if (is_string($result)) {
            set_alert('danger', $result);
        } else {
            set_alert('success', 'Database Upgraded Successfully');
        }

        $this->to_modules();
    }

    public function update_version($name)
    {
        if($this->app_modules->new_version_available($name)) {
            $this->app_modules->update_to_new_version($name);
        }

        $this->to_modules();
    }

    private function to_modules()
    {
        redirect(admin_url('modules'));
    }
}
