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

        if (empty($this->config->get('module_nra_eik')) && empty($this->config->get('module_nra_shop_id'))) {
            $this->response->redirect($this->url->link('extension/module/nra', 'user_token=' . $this->session->data['user_token'], true));
        }

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

    public function download()
    {

        if (!empty($this->request->post['filter_date_start']) && !empty($this->request->post['filter_date_end'])) {

            $orders = array();
            $refunded = array();

            if (isset($this->request->post['filter_date_start'])) {
                $filter_date_start = $this->request->post['filter_date_start'];
            } else {
                $filter_date_start = '';
            }

            if (isset($this->request->post['filter_date_end'])) {
                $filter_date_end = $this->request->post['filter_date_end'];
            } else {
                $filter_date_end = '';
            }

            $this->load->model('extension/module/nra');
            $filter_data = array(
                'filter_date_start' => $filter_date_start,
                'filter_date_end' => $filter_date_end,
            );

            $allOrders = $this->model_extension_module_nra->getOrders($filter_data);

            if (!empty($allOrders)) {
                $order_products = array();
                foreach ($allOrders as $or) {
                    $order = $this->model_extension_module_nra->getOrder($or['order_id']);
                    $products = $this->model_extension_module_nra->getOrderProducts($order['order_id']);
                    foreach ($products as $product) {
                        $price = $product['price'];
                        if ($this->config->get('module_nra_with_tax')) {
                            $price = $product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0);
                        }
                        $order_products[] = array(
                            'name' => $product['name'],
                            'quantity' => $product['quantity'],
                            'price' => $price,
//                            'total'      => $product['total'],
                            'vatRate' => $product['tax']
                        );
                    }

                    if ($order['order_status_id'] === $this->config->get('module_nra_status_completed')) {
                        $orders[] = [
                            'documentNumber' => sprintf('%s%s', $order['invoice_prefix'], $order['invoice_no']),
                            'documentDate' => $order['date_added'],
                            'orderDate' => $order['date_added'],
                            'items' => $order_products,
                            'orderUniqueNumber' => $order['order_id'],
                            'paymentProcessorIdentifier' => $this->config->get('module_nra_delivery_payment'),
                            'paymentType' => $this->getPaymentType($order['payment_code'], $this->config->get('module_nra_payment_method')),
                            'totalDiscount' => 0,
                            'transactionNumber' => null,
                            'virtualPosNumber' => $this->config->get('module_nra_vpos'),
                        ];
                    } elseif ($order['order_status_id'] === $this->config->get('module_nra_status_refund')) {
                        $refunded[] = [
                            'orderAmount' => $order['total'],
                            'orderDate' => $order['date_added'],
                            'orderNumber' => $order['order_id'],
                            'returnMethod' => $this->config->get('module_nra_status_refund_default'),
                        ];
                    }

                }

                if ($this->request->server['HTTPS']) {
                    $server = HTTPS_CATALOG;
                } else {
                    $server = HTTP_CATALOG;
                }

                $data = [
                    'domain' => $server,
                    'eik' => $this->config->get('module_nra_eik'),
                    'isMarketplace' => $this->config->get('module_nra_is_marketplace'),
                    'month' => date('n', strtotime($filter_date_start)),
                    'orders' => $orders,
                    'returned' => $refunded,
                    'shopUniqueNumber' => $this->config->get('module_nra_shop_id'),
                    'year' => date('Y')
                ];

                if (is_array($data)) {

                    $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"windows-1251\"?><audit></audit>");
                    $this->arraytoXML($data, $xml);
                    //$xml->asXML(DIR_STORAGE . 'download/nra-audit.xml');
                    header("Content-type: text/xml");
                    header('Content-Disposition: attachment; filename="nra-audit.xml"');
                    echo $xml->asXML();
                    exit();

                }
            }
        }

        $this->response->redirect($this->url->link('extension/module/nra/export', 'user_token=' . $this->session->data['user_token'], true));

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

    private function getPaymentType($paymentCode, array $paymentMethods)
    {
        if (!empty($paymentCode) && !empty($paymentMethods)) {

            foreach ($paymentMethods as $k => $method) {
                if (in_array($paymentCode, $method, true)) {
                    return $k;
                }
            }

        }

        return false;
    }

    private function arraytoXML($arr, &$xml)
    {
        foreach ($arr as $key => $value) {
            if (is_int($key)) {
                $key = 'Element' . $key;  //To avoid numeric tags like <0></0>
            }
            if (is_array($value)) {
                $label = $xml->addChild($key);
                $this->arrayToXml($value, $label);  //Adds nested elements.
            } else {
                $xml->addChild($key, $value);
            }
        }
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