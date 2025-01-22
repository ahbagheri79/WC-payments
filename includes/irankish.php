<?php
if (!defined('ABSPATH')) exit;
function IRK_Gateway()
{
    if( ! is_dir( ABSPATH . 'iksplugin-logs' ) ) {
        mkdir( ABSPATH . 'iksplugin-logs', 0755, true );
    }

    if (class_exists('WC_Payment_Gateway') && !class_exists('IranKish_Class') && !function_exists('Woocommerce_Add_IranKish_Gateway')) {

        add_filter('woocommerce_payment_gateways', 'Woocommerce_Add_IranKish_Gateway');
        function Woocommerce_Add_IranKish_Gateway($methods)
        {
            $methods[] = 'IranKish_Class';
            return $methods;
        }
        class IranKish_Class extends WC_Payment_Gateway
        {
            public $pluginFolderName;
            public $hookname;
            public $id;
            public $method_title;
            public $method_description;
            public $icon;
            public $has_fields;
            public $title;
            public $description;
            public $terminalID;
            public $password;
            public $acceptOrId;
            public $pubKey;
            public $success_massage;
            public $failed_massage;
            public $cancelled_massage;
            public $wc_vertsion;

            public function __construct()
            {
                $this->wc_vertsion = get_option('woocommerce_version');
                $this->pluginFolderName = plugin_dir_url(__DIR__);
                $this->hookname = 'irankish_pay';
                $this->id = 'irankish_pay';
                $this->method_title = 'درگاه پرداخت ایران کیش';
                $this->method_description = 'پیکره بندی درگاه پرداخت ایران کیش';
                $this->icon = apply_filters('WC_IranKish_logo', $this->pluginFolderName . '/assets/logo.webp');
                $this->has_fields = false;

                $this->init_form_fields();
                $this->init_settings();

                $this->title = $this->settings['title'];
                $this->description = $this->settings['description'];

                $this->terminalID = $this->settings['terminalID'];
                $this->password = $this->settings['password'];
                $this->acceptOrId = $this->settings['acceptorId'];
                $this->pubKey = $this->settings['pub_key'];


                $this->success_massage = $this->settings['success_massage'];
                $this->failed_massage = $this->settings['failed_massage'];
                $this->cancelled_massage = $this->settings['cancelled_massage'];

                if (version_compare($this->wc_vertsion , '2.0.0', '>='))
                    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
                else
                    add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));

                add_action('woocommerce_receipt_' . $this->id . '', array($this, 'send_to_gateway'));
                add_action('woocommerce_before_thankyou', array($this, 'thankyou_hook'), 99, 1);
                add_filter('woocommerce_thankyou_order_received_text', array($this, 'get_order_status'), 99, 2);
                add_action('woocommerce_api_' . strtolower(get_class($this)) . '', array($this, 'return_from_gateway'));
            }

            public function admin_options()
            {
                $action = $this->hookname;
                do_action('WC_Gateway_Payment_Actions', $action);
                parent::admin_options();
            }

            public function init_form_fields()
            {
                $this->form_fields = apply_filters(
                    'WC_IranKish_Config',
                    array(
                        'base_confing' => array(
                            'title'       => 'تنظیمات پایه',
                            'type'        => 'title',
                            'description' => '',
                        ),
                        'enabled' => array(
                            'title'       => 'فعال‌سازی/غیرفعال‌سازی',
                            'type'        => 'checkbox',
                            'label'       => 'فعال‌سازی درگاه ایران‌کیش',
                            'description' => 'برای فعال‌سازی درگاه پرداخت ایران‌کیش، این گزینه را انتخاب کنید.',
                            'default'     => 'yes',
                            'desc_tip'    => true,
                        ),
                        'title' => array(
                            'title'       => 'عنوان درگاه',
                            'type'        => 'text',
                            'description' => 'این عنوان در طول فرآیند خرید به مشتری نمایش داده می‌شود.',
                            'default'     => 'ایران‌کیش',
                            'desc_tip'    => true,
                        ),
                        'description' => array(
                            'title'       => 'توضیحات درگاه',
                            'type'        => 'text',
                            'desc_tip'    => true,
                            'description' => 'توضیحاتی که هنگام انجام پرداخت از طریق این درگاه نمایش داده می‌شود.',
                            'default'     => 'پرداخت امن با تمامی کارت‌های عضو شبکه شتاب از طریق درگاه ایران‌کیش.',
                        ),
                        'account_confing' => array(
                            'title'       => 'تنظیمات حساب ایران‌کیش',
                            'type'        => 'title',
                            'description' => '',
                        ),
                        'terminalID' => array(
                            'title'       => 'شماره پایانه',
                            'type'        => 'text',
                            'description' => 'شماره پایانه مرتبط با درگاه ایران‌کیش.',
                            'default'     => '',
                            'desc_tip'    => true,
                        ),
                        'password' => array(
                            'title'       => 'کلمه عبور',
                            'type'        => 'text',
                            'description' => 'کلمه عبور مربوط به درگاه ایران‌کیش.',
                            'default'     => '',
                            'desc_tip'    => true,
                        ),
                        'acceptorId' => array(
                            'title'       => 'شماره پذیرنده',
                            'type'        => 'text',
                            'description' => 'شماره پذیرنده مربوط به درگاه ایران‌کیش.',
                            'default'     => '',
                            'desc_tip'    => true,
                        ),
                        'pub_key' => array(
                            'title'       => 'کلید عمومی',
                            'type'        => 'textarea',
                            'description' => 'کلید عمومی اختصاص‌یافته برای درگاه ایران‌کیش.',
                            'default'     => '',
                            'desc_tip'    => true,
                        ),
                        'payment_confing' => array(
                            'title'       => 'تنظیمات عملیات پرداخت',
                            'type'        => 'title',
                            'description' => '',
                        ),
                        'success_massage' => array(
                            'title'       => 'پیام پرداخت موفق',
                            'type'        => 'textarea',
                            'description' => 'متنی که پس از پرداخت موفق به کاربر نمایش داده می‌شود. شما می‌توانید از شورت‌کد {refrenceID} برای نمایش کد رهگیری استفاده کنید.',
                            'default'     => 'از خرید شما سپاسگزاریم. پرداخت با موفقیت انجام شد.',
                        ),
                        'failed_massage' => array(
                            'title'       => 'پیام پرداخت ناموفق',
                            'type'        => 'textarea',
                            'description' => 'متنی که پس از پرداخت ناموفق نمایش داده می‌شود. می‌توانید از شورت‌کد {fault} برای نمایش دلیل خطا که از طرف ایران‌کیش ارسال می‌شود، استفاده کنید.',
                            'default'     => 'پرداخت شما ناموفق بود. لطفاً مجدداً تلاش کنید یا در صورت بروز مشکل با مدیر سایت تماس بگیرید.',
                        ),
                        'cancelled_massage' => array(
                            'title'       => 'پیام انصراف از پرداخت',
                            'type'        => 'textarea',
                            'description' => 'متنی که پس از انصراف کاربر از پرداخت و بازگشت از بانک نمایش داده می‌شود.',
                            'default'     => 'پرداخت به دلیل انصراف شما تکمیل نشد.',
                        ),
                    )
                );
            }



            public function process_payment($order_id)
            {
                $order = wc_get_order($order_id);
                return array(
                    'result'   => 'success',
                    'redirect' => $order->get_checkout_payment_url(true)
                );
            }


            public function generateAuthenticationEnvelope($pub_key, $terminalID, $password, $amount)
            {
                $data = $terminalID . $password . str_pad($amount, 12, '0', STR_PAD_LEFT) . '00';
                $data = hex2bin($data);
                $AESSecretKey = openssl_random_pseudo_bytes(16);
                $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
                $iv = openssl_random_pseudo_bytes($ivlen);
                $ciphertext_raw = openssl_encrypt($data, $cipher, $AESSecretKey, $options = OPENSSL_RAW_DATA, $iv);
                $hmac = hash('sha256', $ciphertext_raw, true);
                $crypttext = '';

                openssl_public_encrypt($AESSecretKey . $hmac, $crypttext, $pub_key);

                return array(
                    "data" => bin2hex($crypttext),
                    "iv" => bin2hex($iv),
                );
            }

            public function send_to_gateway($order_id)
            {
                $order = wc_get_order($order_id);
                if (!$order) {
                    return;
                }

                $currency = $order->get_currency();
                $currency = apply_filters('WC_IranKish_Currency', $currency, $order_id);
                $action = $this->hookname;

                do_action('WC_Gateway_Payment_Actions', $action);

                $Amount = intval($order->get_total());
                $Amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_before_check_currency', $Amount, $currency);

                $Amount = $this->adjust_amount_for_currency($Amount, $currency);

                $Amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_after_check_currency', $Amount, $currency);
                $Amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_irr', $Amount, $currency);
                $Amount = apply_filters('woocommerce_order_amount_total_IranKish_gateway', $Amount, $currency);

                do_action('WC_IranKish_Gateway_Payment', $order_id);

                $callBackUrl = add_query_arg('wc_order', $order_id, WC()->api_request_url('IranKish_Class'));
                $token = $this->generateAuthenticationEnvelope($this->pubKey, $this->terminalID, $this->password, $Amount);

                $data = [
                    "request" => [
                        "acceptorId" => $this->acceptOrId,
                        "amount" => (int)$Amount,
                        "billInfo" => null,
                        "requestId" => uniqid(),
                        "paymentId" => (string)$order_id,
                        "requestTimestamp" => time(),
                        "revertUri" => $callBackUrl,
                        "terminalId" => $this->terminalID,
                        "transactionType" => "Purchase"
                    ],
                    'authenticationEnvelope' => $token
                ];
                $data_string = json_encode($data);

                file_put_contents(ABSPATH . 'iksplugin-logs/send_to_gateway.txt', $data_string . PHP_EOL, FILE_APPEND);

                $response = $this->send_api_request('https://ikc.shaparak.ir/api/v3/tokenization/make', $data_string);

                if ($response) {
                    if ($response["responseCode"] != "00") {
                        $this->handle_error($order_id, $response["responseCode"]);
                    } else {
                        $this->handle_success($response, $order_id);
                    }
                }
            }

            private function adjust_amount_for_currency($amount, $currency)
            {
                $currency = strtolower($currency);

                switch ($currency) {
                    case 'irt':
                    case 'toman':
                    case 'iran toman':
                    case 'iranian toman':
                    case 'iran-toman':
                    case 'iranian-toman':
                    case 'iran_toman':
                    case 'iranian_toman':
                    case 'تومان':
                    case 'تومان ایران':
                        $amount *= 10;
                        break;
                    case 'irht':
                        $amount *= 1000 * 10;
                        break;
                    case 'irhr':
                        $amount *= 1000;
                        break;
                }

                return $amount;
            }

            private function send_api_request($url, $data_string)
            {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string)
                ]);

                $result = curl_exec($ch);
                $response = json_decode($result, JSON_OBJECT_AS_ARRAY);

                if ($result === false) {
                    file_put_contents(ABSPATH . 'iksplugin-logs/send_to_gateway.txt', "Error: " . curl_error($ch) . PHP_EOL, FILE_APPEND);
                    return null;
                }

                file_put_contents(ABSPATH . 'iksplugin-logs/send_to_gateway.txt', json_encode(['response' => $response]) . PHP_EOL, FILE_APPEND);
                return $response;
            }

            private function handle_error($order_id, $error_code)
            {
                $fault = $error_code;
                $note = sprintf(__('خطا در هنگام ارسال به بانک : %s', 'woocommerce'), $this->Fault_Status($fault));
                $note = apply_filters('WC_IranKish_Send_to_Gateway_Failed_Note', $note, $order_id, $fault);
                $order = wc_get_order($order_id);
                $order->add_order_note($note);

                $notice = sprintf(__('در هنگام اتصال به بانک خطای زیر رخ داده است : <br/>%s', 'woocommerce'), $this->Fault_Status($fault));
                $notice = apply_filters('WC_IranKish_Send_to_Gateway_Failed_Notice', $notice, $order_id, $fault);

                do_action('WC_IranKish_Send_to_Gateway_Failed', $order_id, $fault);
            }

            private function handle_success($response, $order_id)
            {
                $notice = 'در حال اتصال به بانک .....';
                $notice = apply_filters('WC_IranKish_Before_Send_to_Gateway_Notice', $notice, $order_id);

                do_action('WC_IranKish_Before_Send_to_Gateway', $order_id);

                echo '<form id="redirect_to_irankish" method="post" action="https://ikc.shaparak.ir/iuiv3/IPG/Index/" enctype="multipart/form-data" style="display:none !important;">
                <input type="hidden" name="tokenIdentity" value="' . $response['result']['token'] . '" />
                <input type="submit" value="Pay"/>
            </form>
            <script language="JavaScript" type="text/javascript">
                document.getElementById("redirect_to_irankish").submit();
            </script>';
            }


            public function thankyou_hook($order_id)
            {
                $order = wc_get_order($order_id);
                $order_status = $order->get_status();

                $order_status = in_array($order_status, ['completed', 'processing']) ? 'ok' : 'nok';

                $status = ($order_status == 'ok') ? 'green' : 'red';

                $wc_irankish_order_notice = get_post_meta($order_id, 'wc_irankish_order_notice', true);

                if (!empty($wc_irankish_order_notice)) {
                    echo "<div style='background:$status;padding:8px;border-radius:15px;color:white;font-weight:bold;text-align:center;'>$wc_irankish_order_notice</div>";
                }
            }

            public function get_order_status($text, $order)
            {
                if (in_array($order->get_status(), ['completed', 'processing'])) {
                    return $text;
                }
                return '';
            }


            public function return_from_gateway()
            {
                if (isset($_GET['wc_order'])) {
                    $order_id = sanitize_text_field($_GET['wc_order']);
                } else {
                    $order_id = WC()->session->get('order_id_irankish');
                }

                if ($order_id) {
                    $order = wc_get_order($order_id);
                    $currency = $order->get_currency();
                    $currency = apply_filters('WC_IranKish_Currency', $currency, $order_id);

                    if ($order->get_status() !== 'completed') {
                        $amount = intval($order->get_total());
                        $amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_before_check_currency', $amount, $currency);

                        $currency_lower = strtolower($currency);
                        if (in_array($currency_lower, ['irt', 'toman', 'iran toman', 'iranian toman', 'iran-toman', 'iranian-toman', 'iran_toman', 'iranian_toman', 'تومان', 'تومان ایران'])) {
                            $amount *= 10;
                        } elseif ($currency_lower === 'irht') {
                            $amount *= 10000; // تومان * 1000 * 10
                        } elseif ($currency_lower === 'irhr') {
                            $amount *= 1000; // تومان * 1000
                        }

                        $amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_after_check_currency', $amount, $currency);
                        $amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_irr', $amount, $currency);
                        $amount = apply_filters('woocommerce_order_amount_total_IranKish_gateway', $amount, $currency);

                        $status = 'failed';
                        $fault = 0;
                        file_put_contents(ABSPATH . 'iksplugin-logs/return_from_gateway.txt', json_encode(['post' => $_POST, 'get' => $_GET]) . PHP_EOL, FILE_APPEND);
                        if (sanitize_text_field($_POST['responseCode']) !== '00') {
                            if ($_POST['responseCode'] === '17') {
                                $status = 'cancelled';
                            } else {
                                $status = 'failed';
                                $fault = $_POST['responseCode'];
                            }
                        } else {
                            $data = [
                                "terminalId" => $this->terminalID,
                                "retrievalReferenceNumber" => sanitize_text_field($_POST['retrievalReferenceNumber']),
                                "systemTraceAuditNumber" => sanitize_text_field($_POST['systemTraceAuditNumber']),
                                "tokenIdentity" => sanitize_text_field($_POST['token']),
                            ];

                            $data_string = json_encode($data);
                            $ch = curl_init('https://ikc.shaparak.ir/api/v3/confirmation/purchase');
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');
                            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                'Content-Type: application/json',
                                'Content-Length: ' . strlen($data_string),
                            ]);

                            $result = curl_exec($ch);
                            curl_close($ch);

                            $response = json_decode($result, true);
                            file_put_contents(ABSPATH . 'iksplugin-logs/return_from_gateway.txt', json_encode(['response' => $response]) . PHP_EOL, FILE_APPEND);
                            if ($response["responseCode"] !== '00') {
                                $status = 'failed';
                                $fault = $response["responseCode"];
                            } else {
                                $status = 'completed';
                            }
                        }

                        if ($status !== 'cancelled' && $status !== 'completed') {
                            $status = 'failed';
                        }

                        $referenceID = sanitize_text_field($_POST['retrievalReferenceNumber']);

                        if ($status === 'completed') {
                            $action = $this->hookname;
                            do_action('WC_Gateway_Payment_Actions', $action);

                            update_post_meta($order_id, '_refrenceID', $referenceID);
                            $order->payment_complete($referenceID);

                            if (isset(WC()->cart)) {
                                WC()->cart->empty_cart();
                            }

                            $note = sprintf(__('پرداخت موفقیت آمیز بود. کد رهگیری: %s', 'woocommerce'), $referenceID);
                            $note = apply_filters('WC_IranKish_Return_from_Gateway_Success_Note', $note, $order_id, $referenceID);
                            if ($note) {
                                $order->add_order_note($note, 1);
                            }

                            $notice = wpautop(wptexturize($this->success_massage));
                            $notice = str_replace("{refrenceID}", $referenceID, $notice);
                            $notice = apply_filters('WC_IranKish_Return_from_Gateway_Success_Notice', $notice, $order_id, $referenceID);

                            update_post_meta($order_id, 'wc_irankish_order_notice', $notice);

                            do_action('WC_IranKish_Return_from_Gateway_Success', $order_id, $referenceID);

                            wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
                            exit;
                        } elseif ($status === 'cancelled') {

                            $action = $this->hookname;
                            do_action('WC_Gateway_Payment_Actions', $action);

                            $note = sprintf(__('کاربر از پرداخت انصراف داد. %s', 'woocommerce'), $referenceID);
                            $note = apply_filters('WC_IranKish_Return_from_Gateway_Cancelled_Note', $note, $order_id, $referenceID);
                            if ($note) {
                                $order->add_order_note($note, 1);
                            }

                            $notice = wpautop(wptexturize($this->cancelled_massage));
                            $notice = str_replace("{refrenceID}", $referenceID, $notice);
                            $notice = apply_filters('WC_IranKish_Return_from_Gateway_Cancelled_Notice', $notice, $order_id, $referenceID);

                            update_post_meta($order_id, 'wc_irankish_order_notice', $notice);

                            do_action('WC_IranKish_Return_from_Gateway_Cancelled', $order_id, $referenceID);

                            wp_redirect(add_query_arg('wc_status', 'error', $this->get_return_url($order)));
                            exit;
                        } else {

                            $action = $this->hookname;
                            do_action('WC_Gateway_Payment_Actions', $action);

                            $fault_status = $this->Fault_Status($fault);
                            $note = sprintf(__('خطا در بازگشت از بانک: %s', 'woocommerce'), $fault_status);
                            $note = apply_filters('WC_IranKish_Return_from_Gateway_Failed_Note', $note, $order_id, $referenceID, $fault);
                            if ($note) {
                                $order->add_order_note($note, 1);
                            }

                            $notice = wpautop(wptexturize($this->failed_massage));
                            $notice = str_replace("{refrenceID}", $referenceID, $notice);
                            $notice = str_replace("{fault}", $fault_status, $notice);
                            $notice = apply_filters('WC_IranKish_Return_from_Gateway_Failed_Notice', $notice, $order_id, $referenceID, $fault);

                            update_post_meta($order_id, 'wc_irankish_order_notice', $notice);

                            do_action('WC_IranKish_Return_from_Gateway_Failed', $order_id, $referenceID, $fault);

                            wp_redirect(add_query_arg('wc_status', 'error', $this->get_return_url($order)));
                            exit;
                        }
                    } else {
                        $action = $this->hookname;
                        do_action('WC_Gateway_Payment_Actions', $action);

                        $refrenceID = get_post_meta($order_id, '_refrenceID', true);
                        $notice = wpautop(wptexturize($this->success_massage));
                        $notice = str_replace("{refrenceID}", $refrenceID, $notice);
                        $notice = apply_filters('WC_IranKish_Return_from_Gateway_ReSuccess_Notice', $notice, $order_id, $refrenceID);

                        do_action('WC_IranKish_Return_from_Gateway_ReSuccess', $order_id, $refrenceID);

                        wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
                        exit;
                    }
                } else {
                    $refrenceID = sanitize_text_field($_POST['retrievalReferenceNumber']);
                    $fault = 'شماره سفارش وجود ندارد.';
                    $notice = wpautop(wptexturize($this->failed_massage));
                    $notice = str_replace("{fault}", $fault, $notice);
                    $notice = apply_filters('WC_IranKish_Return_from_Gateway_No_Order_ID_Notice', $notice, $order_id, $fault);

                    do_action('WC_IranKish_Return_from_Gateway_No_Order_ID', $order_id, $refrenceID, $fault);

                    wp_redirect(add_query_arg('wc_status', 'error', wc_get_checkout_url()));
                    exit;
                }
            }

            private static function Fault_Status($err_code)
            {

                $msgArray = array(
                    '5' => '‫از‬ ‫انجام‬ ‫تراکنش‬ ‫صرف‬ ‫نظر‬ ‫شد‬',
                    '3' => '‫پذیرنده‬ ‫فروشگاهی‬ ‫نا‬ ‫معتبر‬ ‫است‬',
                    '64' => '‫مبلغ‬ ‫تراکنش‬ ‫نادرست‬ ‫است‪،‬جمع‬ ‫مبالغ‬ ‫تقسیم‬ ‫وجوه‬ ‫برابر‬ ‫مبلغ‬ ‫کل‬ ‫تراکنش‬ ‫نمی‬ ‫باشد‬',
                    '94' => '‫تراکنش‬ ‫تکراری‬ ‫است‬',
                    '25' => '‫تراکنش‬ ‫اصلی‬ ‫یافت‬ ‫نشد‬',
                    '77' => '‫روز‬ ‫مالی‬ ‫تراکنش‬ ‫نا‬ ‫معتبر‬ ‫است‬',
                    '63' => '‫تمهیدات‬ ‫امنیتی‬ ‫نقض‬ ‫گردیده‬ ‫است‬',
                    '97' => '‫کد‬ ‫تولید‬ ‫کد‬ ‫اعتبار‬ ‫سنجی‬ ‫نا‬ ‫معتبر‬ ‫است‬',
                    '30' => '‫فرمت‬ ‫پیام‬ ‫نادرست‬ ‫است‬',
                    '86' => '‫شتاب‬ ‫در‬ ‫حال‬ ‫‪Off‬‬ ‫‪Sign‬‬ ‫است‬',
                    '55' => '‫رمز‬ ‫کارت‬ ‫نادرست‬ ‫است‬',
                    '40' => '‫عمل‬ ‫درخواستی‬ ‫پشتیبانی‬ ‫نمی‬ ‫شود‬',
                    '57' => '‫انجام‬ ‫تراکنش‬ ‫مورد‬ ‫درخواست‬ ‫توسط‬ ‫پایانه‬ ‫انجام‬ ‫دهنده‬ ‫مجاز‬ ‫نمی‬ ‫باشد‬',
                    '58' => '‫انجام‬ ‫تراکنش‬ ‫مورد‬ ‫درخواست‬ ‫توسط‬ ‫پایانه‬ ‫انجام‬ ‫دهنده‬ ‫مجاز‬ ‫نمی‬ ‫باشد‬',
                    '96' => '‫قوانین‬ ‫سامانه‬ ‫نقض‬ ‫گردیده‬ ‫است‬ ‫‪،‬‬ ‫خطای‬ ‫داخلی‬ ‫سامانه‬',
                    '2' => '‫تراکنش‬ ‫قبال‬ ‫برگشت‬ ‫شده‬ ‫است‬',
                    '54' => '‫تاریخ‬ ‫انقضا‬ ‫کارت‬ ‫سررسید‬ ‫شده‬ ‫است‬',
                    '62' => '‫کارت‬ ‫محدود‬ ‫شده‬ ‫است‬',
                    '75' => '‫تعداد‬ ‫دفعات‬ ‫ورود‬ ‫رمز‬ ‫اشتباه‬ ‫از‬ ‫حد‬ ‫مجاز‬ ‫فراتر‬ ‫رفته‬ ‫است‬',
                    '14' => '‫اطالعات‬ ‫کارت‬ ‫صحیح‬ ‫نمی‬ ‫باشد‬',
                    '51' => '‫موجودی‬ ‫حساب‬ ‫کافی‬ ‫نمی‬ ‫باشد‬',
                    '56' => '‫اطالعات‬ ‫کارت‬ ‫یافت‬ ‫نشد‬',
                    '61' => '‫مبلغ‬ ‫تراکنش‬ ‫بیش‬ ‫از‬ ‫حد‬ ‫مجاز‬ ‫است‬',
                    '65' => '‫تعداد‬ ‫دفعات‬ ‫انجام‬ ‫تراکنش‬ ‫بیش‬ ‫از‬ ‫حد‬ ‫مجاز‬ ‫است‬',
                    '78' => '‫کارت‬ ‫فعال‬ ‫نیست‬',
                    '79' => '‫حساب‬ ‫متصل‬ ‫به‬ ‫کارت‬ ‫بسته‬ ‫یا‬ ‫دارای‬ ‫اشکال‬ ‫است‬',
                    '42' => '‫کارت‬ ‫یا‬ ‫حساب‬ ‫مقصد‬ ‫در‬ ‫وضعیت‬ ‫پذیرش‬ ‫نمی‬ ‫باشد‬',
                    '901' => '‫درخواست‬ ‫نا‬ ‫معتبر‬ ‫است‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '902' => '‫پارامترهای‬ ‫اضافی‬ ‫درخواست‬ ‫نامعتبر‬ ‫می‬ ‫باشد		‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '903' => '‫شناسه‬ ‫پرداخت‬ ‫نامعتبر‬ ‫می‬ ‫باشد‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '904' => '‫اطالعات‬ ‫مرتبط‬ ‫با‬ ‫قبض‬ ‫نا‬ ‫معتبر‬ ‫می‬ ‫باشد‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '905' => '‫شناسه‬ ‫درخواست‬ ‫نامعتبر‬ ‫می‬ ‫باشد‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '906' => '‫درخواست‬ ‫تاریخ‬ ‫گذشته‬ ‫است‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '907' => '‫آدرس‬ ‫بازگشت‬ ‫نتیجه‬ ‫پرداخت‬ ‫نامعتبر‬ ‫می‬ ‫باشد‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '909' => '‫پذیرنده‬ ‫نامعتبر‬ ‫می‬ ‫باشد(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '910' => '‫پارامترهای‬ ‫مورد‬ ‫انتظار‬ ‫پرداخت‬ ‫تسهیمی‬ ‫تامین‬ ‫نگردیده‬ ‫است(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '911' => '‫پارامترهای‬ ‫مورد‬ ‫انتظار‬ ‫پرداخت‬ ‫تسهیمی‬ ‫نا‬ ‫معتبر‬ ‫یا‬ ‫دارای‬ ‫اشکال‬ ‫می‬ ‫باشد(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '912' => '‫تراکنش‬ ‫درخواستی‬ ‫برای‬ ‫پذیرنده‬ ‫فعال‬ ‫نیست‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '913' => '‫تراکنش‬ ‫تسهیم‬ ‫برای‬ ‫پذیرنده‬ ‫فعال‬ ‫نیست‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '914' => '‫آدرس‬ ‫آی‬ ‫پی‬ ‫دریافتی‬ ‫درخواست‬ ‫نا‬ ‫معتبر‬ ‫می‬ ‫باشد‬',
                    '915' => '‫شماره‬ ‫پایانه‬ ‫نامعتبر‬ ‫می‬ ‫باشد‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '916' => '‫شماره‬ ‫پذیرنده‬ ‫نا‬ ‫معتبر‬ ‫می‬ ‫باشد‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '917' => '‫نوع‬ ‫تراکنش‬ ‫اعالم‬ ‫شده‬ ‫در‬ ‫خواست‬ ‫نا‬ ‫معتبر‬ ‫می‬ ‫باشد‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '918' => '‫پذیرنده‬ ‫فعال‬ ‫نیست‬',
                    '919' => '‫مبالغ‬ ‫تسهیمی‬ ‫ارائه‬ ‫شده‬ ‫با‬ ‫توجه‬ ‫به‬ ‫قوانین‬ ‫حاکم‬ ‫بر‬ ‫وضعیت‬ ‫تسهیم‬ ‫پذیرنده‬ ‫‪،‬‬ ‫نا‬ ‫معتبر‬ ‫است‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '920' => '‫شناسه‬ ‫نشانه‬ ‫نامعتبر‬ ‫می‬ ‫باشد‬',
                    '921' => '‫شناسه‬ ‫نشانه‬ ‫نامعتبر‬ ‫و‬ ‫یا‬ ‫منقضی‬ ‫شده‬ ‫است‬',
                    '922' => '‫نقض‬ ‫امنیت‬ ‫درخواست‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '923' => '‫ارسال‬ ‫شناسه‬ ‫پرداخت‬ ‫در‬ ‫تراکنش‬ ‫قبض‬ ‫مجاز‬ ‫نیست‬ ‫(‬ ‫‪Tokenization‬‬ ‫)‬',
                    '925' => '‫مبلغ‬ ‫مبادله‬ ‫شده‬ ‫نا‬ ‫معتبر‬ ‫می‬ ‫باشد‬',
                );

                if (isset($msgArray[$err_code])) {
                    return $msgArray[$err_code];
                }
                return 'در حین پرداخت خطای سیستمی رخ داده است .' . $err_code;
            }
        }
    }
}
add_action('plugins_loaded', 'IRK_Gateway', 0);
