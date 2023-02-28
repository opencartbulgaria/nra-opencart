<?php

class ControllerExtensionModuleNra extends Controller
{
    private $error = array();

    public function index()
    {

        $this->load->language('extension/module/nra');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_nra', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['module_nra_eik'])) {
            $data['error_nra_eik'] = $this->error['module_nra_eik'];
        } else {
            $data['error_nra_eik'] = '';
        }

        if (isset($this->error['module_nra_shop_id'])) {
            $data['error_nra_shop_id'] = $this->error['module_nra_shop_id'];
        } else {
            $data['error_nra_shop_id'] = '';
        }

        $data['text_edit'] = $this->language->get('text_edit');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/nra', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/nra', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['export'] = $this->url->link('extension/module/nra/export', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_nra_eik'])) {
            $data['module_nra_eik'] = $this->request->post['module_nra_eik'];
        } else {
            $data['module_nra_eik'] = $this->config->get('module_nra_eik');
        }

        if (isset($this->request->post['module_nra_shop_id'])) {
            $data['module_nra_shop_id'] = $this->request->post['module_nra_shop_id'];
        } else {
            $data['module_nra_shop_id'] = $this->config->get('module_nra_shop_id');
        }

        if (isset($this->request->post['module_nra_is_marketplace'])) {
            $data['module_nra_is_marketplace'] = $this->request->post['module_nra_is_marketplace'];
        } else {
            $data['module_nra_is_marketplace'] = $this->config->get('module_nra_is_marketplace');
        }

        if (isset($this->request->post['module_nra_status'])) {
            $data['module_nra_status'] = $this->request->post['module_nra_status'];
        } else {
            $data['module_nra_status'] = $this->config->get('module_nra_status');
        }

        if (isset($this->request->post['module_nra_payment_method'])) {
            $data['module_nra_payment_method'] = $this->request->post['module_nra_payment_method'];
        } else {
            $data['module_nra_payment_method'] = $this->config->get('module_nra_payment_method');
        }

        //Refunds
        $data['payments'] = $this->paymentMethods();
        // Payment Methods
        $data['payment_methods'] = array();
        $this->load->model('setting/extension');
        $payment_methods = $this->model_setting_extension->getInstalled('payment');
        foreach ($payment_methods as $method) {
            $this->load->language('extension/payment/' . $method);
            $data['payment_methods'][] = [
                'code' => $method,
                'name' => $this->language->get('heading_title'),
            ];
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['module_nra_status_completed'])) {
            $data['module_nra_status_completed'] = $this->request->post['module_nra_status_completed'];
        } else {
            $data['module_nra_status_completed'] = $this->config->get('module_nra_status_completed');
        }

        if (isset($this->request->post['module_nra_status_refund'])) {
            $data['module_nra_status_refund'] = $this->request->post['module_nra_status_refund'];
        } else {
            $data['module_nra_status_refund'] = $this->config->get('module_nra_status_refund');
        }


        //Refunds
        $data['refunds'] = $this->refundMethods();

        if (isset($this->request->post['module_nra_status_refund_default'])) {
            $data['module_nra_status_refund_default'] = $this->request->post['module_nra_status_refund_default'];
        } else {
            $data['module_nra_status_refund_default'] = $this->config->get('module_nra_status_refund_default');
        }

        if (isset($this->request->post['module_nra_with_tax'])) {
            $data['module_nra_with_tax'] = $this->request->post['module_nra_with_tax'];
        } else {
            $data['module_nra_with_tax'] = $this->config->get('module_nra_with_tax');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/nra/nra', $data));
    }

    public function export()
    {

        $this->load->language('extension/module/nra');

        $this->document->setTitle($this->language->get('heading_title'));

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['text_download'] = $this->language->get('text_download');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/nra', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/nra/download', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('extension/module/nra', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        //Refunds
        $data['months'] = $this->getMonths();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/nra/export', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/nra')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['module_nra_eik']) < 3) || (utf8_strlen($this->request->post['module_nra_eik']) > 13)) {
            $this->error['module_nra_eik'] = $this->language->get('error_module_nra_eik');
        }

        if ((utf8_strlen($this->request->post['module_nra_shop_id']) < 3) || (utf8_strlen($this->request->post['module_nra_shop_id']) > 10)) {
            $this->error['module_nra_shop_id'] = $this->language->get('error_module_nra_shop_id');
        }

        return !$this->error;
    }


    private function refundMethods()
    {

        $methods = [
            1 => $this->language->get('text_bank'),
            2 => $this->language->get('text_card'),
            3 => $this->language->get('text_cash'),
            4 => $this->language->get('text_other')
        ];

        return $methods;
    }

    private function paymentMethods()
    {

        $methods = [
            1 => $this->language->get('entry_without_post_payment'),
            2 => $this->language->get('entry_vpos'),
            3 => $this->language->get('entry_with_post_payment'),
            4 => $this->language->get('entry_with_post_payment_service'),
            6 => $this->language->get('text_reflected_with_receipt'),
            5 => $this->language->get('text_other'),
        ];

        return $methods;
    }

    private function getMonths()
    {
        $data = array();

        $months = [
            1 => $this->language->get('text_january'),
            2 => $this->language->get('text_february'),
            3 => $this->language->get('text_march'),
            4 => $this->language->get('text_april'),
            5 => $this->language->get('text_may'),
            6 => $this->language->get('text_june'),
            7 => $this->language->get('text_july'),
            8 => $this->language->get('text_august'),
            9 => $this->language->get('text_september'),
            10 => $this->language->get('text_october'),
            11 => $this->language->get('text_november'),
            12 => $this->language->get('text_december'),
        ];

        for ($i = 1; $i <= date('n'); $i++) {
            $data[$i] = $months[$i];
        }

        return $data;

    }

    public function install()
    {
        @mail('info@opencartbulgaria.com', 'Generated XML to NRA installed (303001)', HTTP_CATALOG . ' - ' . $this->config->get('config_name') . "\r\n" . 'version - ' . VERSION . "\r\n" . 'IP - ' . $this->request->server['REMOTE_ADDR'], 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n" . 'From: ' . $this->config->get('config_owner') . ' <' . $this->config->get('config_email') . '>' . "\r\n");

    }

    public function uninstall()
    {
        //
    }
}