<?php
class ControllerExtensionModuleNra extends Controller {
    private $error = array();

    public function index() {

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

        If(isset($this->request->post['module_nra_is_marketplace'])) {
            $data['module_nra_is_marketplace'] = $this->request->post['module_nra_is_marketplace'];
        } else {
            $data['module_nra_is_marketplace'] = $this->config->get('module_nra_is_marketplace');
        }

        if (isset($this->request->post['module_nra_status'])) {
            $data['module_nra_status'] = $this->request->post['module_nra_status'];
        } else {
            $data['module_nra_status'] = $this->config->get('module_nra_status');
        }

        if (isset($this->request->post['module_nra_without_post_payment'])) {
            $data['module_nra_without_post_payment'] = $this->request->post['module_nra_without_post_payment'];
        } else {
            $data['module_nra_without_post_payment'] = $this->config->get('module_nra_without_post_payment');
        }

        if (isset($this->request->post['module_nra_with_post_payment'])) {
            $data['module_nra_with_post_payment'] = $this->request->post['module_nra_with_post_payment'];
        } else {
            $data['module_nra_with_post_payment'] = $this->config->get('module_nra_with_post_payment');
        }

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

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/nra', $data));
    }

    public function install()
    {
        @mail('info@opencartbulgaria.com', 'Generated XML to NRA installed (303001)', HTTP_CATALOG . ' - ' . $this->config->get('config_name') . "\r\n" . 'version - ' . VERSION . "\r\n" . 'IP - ' . $this->request->server['REMOTE_ADDR'], 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n" . 'From: ' . $this->config->get('config_owner') . ' <' . $this->config->get('config_email') . '>' . "\r\n");

    }

    public function uninstall()
    {
        //
    }

    protected function validate() {
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


}