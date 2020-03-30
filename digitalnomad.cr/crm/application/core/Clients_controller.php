<?php

defined('BASEPATH') or exit('No direct script access allowed');

define('CLIENTS_AREA', true);

class Clients_controller extends CRM_Controller
{
    public $template = [];

    public $data = [];

    public $use_footer = true;

    public $use_head = true;

    public $add_scripts = true;

    public $use_submenu = true;

    public $use_navigation = true;

    public function __construct()
    {
        parent::__construct();

        $language = load_client_language();

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<p class="text-danger alert-validation">', '</p>');

        $this->form_validation->set_message('required', _l('form_validation_required'));
        $this->form_validation->set_message('valid_email', _l('form_validation_valid_email'));
        $this->form_validation->set_message('matches', _l('form_validation_matches'));
        $this->form_validation->set_message('is_unique', _l('form_validation_is_unique'));

        $this->load->model('authentication_model');
        $this->authentication_model->autologin();

        $this->load->model('tickets_model');
        $this->load->model('departments_model');
        $this->load->model('currencies_model');
        $this->load->model('invoices_model');
        $this->load->model('estimates_model');
        $this->load->model('proposals_model');
        $this->load->model('projects_model');
        $this->load->model('announcements_model');
        $this->load->model('contracts_model');

        $GLOBALS['language'] = $language;
        $GLOBALS['locale']   = get_locale_key($language);

        init_customers_area_assets();

        hooks()->do_action('clients_init');

        $vars = [
            'departments'     => $this->departments_model->get(false, true),
            'priorities'      => $this->tickets_model->get_priority(),
            'ticket_statuses' => $this->tickets_model->get_ticket_status(),
            'currencies'      => $this->currencies_model->get(),
            'locale'          => $GLOBALS['locale'],
            'language'        => $language,
            'menu'            => $this->app_menu->get_theme_items(),
            'isRTL'           => (is_rtl(true) ? 'true' : 'false'),
            ];

        if (get_option('services') == 1) {
            $vars['services'] = $this->tickets_model->get_service();
        }

        $this->load->model('knowledge_base_model');

        if (is_client_logged_in()) {
            $contact            = $this->clients_model->get_contact(get_contact_user_id());
            $GLOBALS['contact'] = $contact;

            if (!$contact || $contact->active == 0) {
                $this->load->model('authentication_model');
                $this->authentication_model->logout(true);
                redirect(site_url());
            }

            $vars['total_undismissed_announcements'] = $this->announcements_model->get_total_undismissed_announcements();
            $vars['client']                          = $this->clients_model->get(get_client_user_id());
            $vars['contact']                         = $contact;
        }

        $vars = hooks()->apply_filters('customers_area_autoloaded_vars', $vars);

        $this->load->vars($vars);
    }

    public function layout($viewFromRoot = false)
    {
        /**
         * Navigation and submenu
         * @var boolean
         */
        $this->data['use_navigation'] = $this->use_navigation == true;
        $this->data['use_submenu']    = $this->use_submenu == true;

        /**
         * Theme head
         * @var string
         */
        $this->template['head'] = $this->use_head == true
        ? $this->load->view('themes/' . active_clients_theme() . '/head', $this->data, true)
        : '';
        $GLOBALS['customers_head'] = $this->template['head'];

        /**
         * Load the template view
         * @var string
         */
        $viewPath                  = !$viewFromRoot ? 'themes/' . active_clients_theme() . '/views/' . $this->view : $this->view;
        $this->template['view']    = $this->load->view($viewPath, $this->data, true);
        $GLOBALS['customers_view'] = $this->template['view'];

        /**
         * Theme footer
         * @var string
         */
        $this->template['footer'] = $this->use_footer == true
        ? $this->load->view('themes/' . active_clients_theme() . '/footer', $this->data, true)
        : '';
        $GLOBALS['customers_footer'] = $this->template['footer'];

        /**
         * @deprecated 2.3.0
         * Scripts is no longer used, add app_customers_footer() in themes/[theme]/index.php
         * @var string
         */
        $this->template['scripts'] = '';
        if (file_exists('themes/' . active_clients_theme() . '/scripts.php')) {
            $this->template['scripts'] = $this->add_scripts == true
            ? $this->load->view('themes/' . active_clients_theme() . '/scripts', $this->data, true)
            : '';
        }

        /**
         * Load the theme compiled template
         */
        $this->load->view('themes/' . active_clients_theme() . '/index', $this->template);
    }
}
