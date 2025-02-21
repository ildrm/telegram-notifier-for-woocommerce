<?php
/*
Plugin Name: Notifier for WooCommerce
Plugin URI: https://wordpress.org/plugins/wc-message-notifier/
Description: Send notifications to messaging channels when WooCommerce orders are created or updated
Version: 1.1
Author: Shahin Ilderemi
Author URI: https://profiles.wordpress.org/msdildrm
Text Domain: wc-message-notifier
Domain Path: /languages
License: GPL v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
    exit;
}

class WC_Message_Notifier {
    private static $instance = null;
    private $bot_token;
    private $chat_id;
    private $thread_id;
    private $use_proxy;
    private $proxy_type;
    private $proxy_ip;
    private $proxy_port;
    private $proxy_username;
    private $proxy_password;
    private $use_custom_url;
    private $custom_api_url;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->bot_token = get_option('wc_telegram_bot_token', '');
        $this->chat_id = get_option('wc_telegram_chat_id', '');
        $this->thread_id = get_option('wc_telegram_thread_id', '');
        
        // Proxy settings
        $this->use_proxy = get_option('wc_telegram_use_proxy', false);
        $this->proxy_type = get_option('wc_telegram_proxy_type', 'http');
        $this->proxy_ip = get_option('wc_telegram_proxy_ip', '');
        $this->proxy_port = get_option('wc_telegram_proxy_port', '');
        $this->proxy_username = get_option('wc_telegram_proxy_username', '');
        $this->proxy_password = get_option('wc_telegram_proxy_password', '');
        
        // Custom URL settings
        $this->use_custom_url = get_option('wc_telegram_use_custom_url', false);
        $this->custom_api_url = get_option('wc_telegram_custom_api_url', '');
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
        add_action('init', array($this, 'load_plugin_textdomain'));
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain('wc-message-notifier', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function init() {
        if ($this->check_woocommerce()) {
            // add_action('woocommerce_new_order', array($this, 'handle_new_order'), 10, 1);
            add_action('woocommerce_checkout_order_processed', array($this, 'handle_new_order'), 10, 1);
            add_action('woocommerce_order_status_changed', array($this, 'handle_status_change'), 10, 3);
        }
    }

    public function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                add_action('admin_notices', function() {
                    echo '<div class="error"><p>' . esc_html__('WooCommerce is required for this plugin to work.', 'wc-message-notifier') . '</p></div>';
                });
                return false;
            }
        }
        return true;
    }

    public function add_menu() {
        add_options_page(
            esc_html__('Telegram Notification Settings', 'wc-message-notifier'),
            esc_html__('Telegram Notification', 'wc-message-notifier'),
            'manage_options',
            'wc-message-notifier',
            array($this, 'settings_page')
        );
    }

    private function log($message, $type = 'info') {
        if (WP_DEBUG) {
            $log_file = WP_CONTENT_DIR . '/telegram-notifier.log';
            $date = gmdate('Y-m-d H:i:s');
            $log_message = "[$date][$type] " . (is_string($message) ? $message : wp_json_encode($message)) . "\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);
        }
    }

    private function format_price($amount, $currency) {
        if ($amount == 0) {
            return esc_html__('0', 'wc-message-notifier');
        }
        
        $persian_numbers = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $formatted_amount = number_format($amount, 0, '.', ',');
        $formatted_amount = str_replace(range(0, 9), $persian_numbers, $formatted_amount);
        
        switch ($currency) {
            case 'IRR':
                return $formatted_amount . ' ' . esc_html__('Rial', 'wc-message-notifier');
            case 'IRT':
            case 'IRHR':
            case 'IRHT':
                return $formatted_amount . ' ' . esc_html__('Toman', 'wc-message-notifier');
            default:
                return $formatted_amount . ' ' . $currency;
        }
    }

    private function translate_status($status) {
        $statuses = array(
            'pending' => esc_html__('Pending Payment', 'wc-message-notifier'),
            'processing' => esc_html__('Processing', 'wc-message-notifier'),
            'on-hold' => esc_html__('On Hold', 'wc-message-notifier'),
            'completed' => esc_html__('Completed', 'wc-message-notifier'),
            'cancelled' => esc_html__('Cancelled', 'wc-message-notifier'),
            'refunded' => esc_html__('Refunded', 'wc-message-notifier'),
            'failed' => esc_html__('Failed', 'wc-message-notifier'),
            'checkout-draft' => esc_html__('Draft', 'wc-message-notifier')
        );
        
        return isset($statuses[$status]) ? $statuses[$status] : $status;
    }

    private function translate_payment_method($method) {
        $methods = array(
            'cod' => esc_html__('Cash on Delivery', 'wc-message-notifier'),
            'bacs' => esc_html__('Direct Bank Transfer', 'wc-message-notifier'),
            'cheque' => esc_html__('Check Payment', 'wc-message-notifier'),
            'payping' => esc_html__('PayPing', 'wc-message-notifier'),
            'WC_ZPal' => esc_html__('ZarinPal', 'wc-message-notifier'),
            'zarinpal' => esc_html__('ZarinPal', 'wc-message-notifier'),
            'sep' => esc_html__('Saman Electronic Payment', 'wc-message-notifier'),
            'mellat' => esc_html__('Mellat Payment', 'wc-message-notifier'),
            'mabna' => esc_html__('Card to Card', 'wc-message-notifier'),
            'idpay' => esc_html__('IDPay', 'wc-message-notifier'),
            'payir' => esc_html__('Pay.ir', 'wc-message-notifier'),
            'vandar' => esc_html__('Vandar', 'wc-message-notifier'),
            'sizpay' => esc_html__('SizPay', 'wc-message-notifier'),
            'sepordeh' => esc_html__('Sepordeh', 'wc-message-notifier'),
            'nextpay' => esc_html__('NextPay', 'wc-message-notifier')
        );
        
        return isset($methods[$method]) ? $methods[$method] : $method;
    }

    private function escape_markdown($text) {
        return str_replace(
            ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)', '\\~', '\\`', '\\>', '\\#', '\\+', '\\-', '\\=', '\\|', '\\{', '\\}', '\\.', '\\!'],
            $text
        );
    }

    private function escape_markdown_url($url) {
        return str_replace(
            ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!', ',', ':', ';', '/', '?', '&'],
            ['\\_', '\\*', '\\[', '\\]', '\\(', '\\)', '\\~', '\\`', '\\>', '\\#', '\\+', '\\-', '\\=', '\\|', '\\{', '\\}', '\\.', '\\!', '\\,', '\\:', '\\;', '\\/', '\\?', '\\&'],
            $url
        );
    }

    private function get_order_items_text($order) {
        if (!$order instanceof WC_Order) {
            return esc_html__('Error getting order', 'wc-message-notifier');
        }
    
        $items = $order->get_items();
        $text = sprintf("📝 %s\n", esc_html__('Products in order:', 'wc-message-notifier'));
        
        if (empty($items)) {
            return $text . esc_html__('No products', 'wc-message-notifier');
        }
    
        foreach ($items as $item) {
            $product = $item->get_product();
            $quantity = $item->get_quantity();
            $total = $item->get_total();
            
            $text .= "• " . $this->escape_markdown($item->get_name());
            $text .= " \\| " . $this->escape_markdown($quantity . " " . esc_html__('units', 'wc-message-notifier'));
            
            if ($item->get_subtotal() != $total) {
                $text .= " \\| " . $this->escape_markdown($this->format_price($item->get_subtotal(), ''));
                $text .= " → " . $this->escape_markdown($this->format_price($total, ''));
            } else {
                $text .= " \\| " . $this->escape_markdown($this->format_price($total, ''));
            }
            $text .= "\n";
        }
        
        return $text;
    }

    private function format_address($order) {
        $address_parts = array(
            $order->get_billing_state(),
            $order->get_billing_city(),
            $order->get_billing_address_1(),
            $order->get_billing_address_2()
        );

        $address_parts = array_filter($address_parts);
        
        return !empty($address_parts) ? implode('، ', $address_parts) : esc_html__('Not registered', 'wc-message-notifier');
    }

    private function prepare_order_message($order, $title, $additional_info = '') {
        $currency = $order->get_currency();
        
        // محاسبه مبالغ
        $subtotal = $order->get_subtotal();
        $discount = $order->get_total_discount();
        $tax = $order->get_total_tax();
        $total = $order->get_total();
        
        $message = $this->escape_markdown($title) . "\n\n";
        /* translators: %s: Order number */
        $message .= sprintf(esc_html__('Order: \#%s', 'wc-message-notifier'), $this->escape_markdown($order->get_order_number())) . "\n";
        /* translators: %s: Customer name */
        $message .= sprintf(esc_html__('Customer: %s', 'wc-message-notifier'), $this->escape_markdown(($order->get_formatted_billing_full_name() ?? $order->get_formatted_shipping_full_name() ?: esc_html__('Not registered', 'wc-message-notifier')))) . "\n";
        /* translators: %s: Phone number */
        $message .= sprintf(esc_html__('Phone: %s', 'wc-message-notifier'), $this->escape_markdown(($order->get_billing_phone() ?? $order->get_shipping_phone() ?: esc_html__('Not registered', 'wc-message-notifier')))) . "\n";
        /* translators: %s: Address */
        $message .= sprintf(esc_html__('Address: %s', 'wc-message-notifier'), $this->escape_markdown($this->format_address($order))) . "\n\n";
        
        // اضافه کردن لیست محصولات
        $message .= $this->get_order_items_text($order) . "\n";
        
        // اطلاعات مالی
        $message .= "💰 " . esc_html__('Financial Information:', 'wc-message-notifier') . "\n";
        /* translators: %s: Total products amount */
        $message .= sprintf(esc_html__('Total Products Amount: %s', 'wc-message-notifier'), $this->escape_markdown($this->format_price($subtotal, $currency))) . "\n";
        
        if ($discount > 0) {
            /* translators: %s: Discount amount */
            $message .= sprintf(esc_html__('Discount: %s', 'wc-message-notifier'), $this->escape_markdown($this->format_price($discount, $currency))) . "\n";
        }
        
        if ($tax > 0) {
            /* translators: %s: Tax amount */
            $message .= sprintf(esc_html__('Tax: %s', 'wc-message-notifier'), $this->escape_markdown($this->format_price($tax, $currency))) . "\n";
        }
        
        /* translators: %s: Final amount */
        $message .= sprintf(esc_html__('Final Amount: %s', 'wc-message-notifier'), $this->escape_markdown($this->format_price($total, $currency))) . "\n";
        
        if (!empty($additional_info)) {
            $message .= "\n" . $this->escape_markdown($additional_info);
        }
        
        /* translators: %s: Payment method */
        $message .= sprintf(esc_html__('Payment Method: %s', 'wc-message-notifier'), $this->escape_markdown($this->translate_payment_method($order->get_payment_method()))) . "\n";
        
        /* translators: %s: IP address */
        $message .= sprintf(esc_html__('IP: %s', 'wc-message-notifier'), $this->escape_markdown($order->get_customer_ip_address())) . "\n\n";
    
        // افزودن لینک مدیریت سفارش با آدرس صحیح
        $home_url = home_url();
        $admin_url = admin_url('post.php?post=' . $order->get_id() . '&action=edit');
        
        // جایگزینی آدرس پایه سایت
        $base_url_pattern = wp_parse_url($home_url, PHP_URL_PATH);
        if ($base_url_pattern) {
            // اگر وردپرس در یک زیرپوشه نصب شده باشد
            $base_url_pattern = rtrim($base_url_pattern, '/');
            $full_url = str_replace($base_url_pattern . $base_url_pattern . '/', $base_url_pattern, $admin_url);
        } else {
            // اگر وردپرس در روت نصب شده باشد
            $full_url = $admin_url;
        }
    
        /* translators: %s: Order URL */
        $message .= "\n🔗 " . sprintf(esc_html__('[View and Edit Order on Site](%s)', 'wc-message-notifier'), $this->escape_markdown_url($full_url)) . "\n" . $this->escape_markdown_url($full_url);
        
        return $message;
    }
    

    private function send_notification($message) {
        if (empty($this->bot_token) || empty($this->chat_id)) {
            $this->log(esc_html__('Bot token or chat ID is empty', 'wc-message-notifier'), 'error');
            return false;
        }

        // Determine API URL
        if ($this->use_custom_url && !empty($this->custom_api_url)) {
            $url = $this->custom_api_url;
            $patterns = [
                '{bot_token}' => urlencode($this->bot_token),
                '{method}' => 'sendMessage'
            ];
            $url = str_replace(array_keys($patterns), array_values($patterns), $url);
        } else {
            $url = 'https://api.telegram.org/bot' . urlencode($this->bot_token) . '/sendMessage';
        }

        $body = array(
            'chat_id' => $this->chat_id,
            'text' => $message,
            'parse_mode' => 'MarkdownV2'
        );

        if (!empty($this->thread_id)) {
            $body['message_thread_id'] = $this->thread_id;
        }

        $args = array(
            'body' => $body,
            'timeout' => 30,
            'sslverify' => false,
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded')
        );

        // Add proxy configuration if enabled
        if ($this->use_proxy && !empty($this->proxy_ip) && !empty($this->proxy_port)) {
            $proxy_auth = '';
            if (!empty($this->proxy_username) && !empty($this->proxy_password)) {
                $proxy_auth = $this->proxy_username . ':' . $this->proxy_password . '@';
            }
            
            $proxy_string = $this->proxy_type . '://' . $proxy_auth . $this->proxy_ip . ':' . $this->proxy_port;
            $args['proxy'] = $proxy_string;
        }

        /* translators: %s: URL */
        $this->log(sprintf(esc_html__('Sending request to: %s', 'wc-message-notifier'), $url), 'debug');
        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            /* translators: %s: error message */
            $this->log(sprintf(esc_html__('WordPress HTTP Error: %s', 'wc-message-notifier'), $response->get_error_message()), 'error');
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (!$result || !isset($result['ok']) || $result['ok'] !== true) {
            $error = isset($result['description']) ? $result['description'] : esc_html__('Unknown error', 'wc-message-notifier');
            /* translators: %s: error message */
            $this->log(sprintf(esc_html__('Telegram API Error: %s', 'wc-message-notifier'), $error), 'error');
            return false;
        }

        $this->log(esc_html__('Message sent successfully', 'wc-message-notifier'), 'success');
        return true;
    }

    public function handle_new_order($order_id) {
        $order = wc_get_order($order_id);
        if ($order) {
            $message = $this->prepare_order_message($order, esc_html__('🛍 New Order', 'wc-message-notifier'));
            $this->send_notification($message);
        }
    }

    public function handle_status_change($order_id, $old_status, $new_status) {
        $order = wc_get_order($order_id);
        if ($order) {
            /* translators: %1$s: previous status, %2$s: new status */
            $status_info = sprintf( __( 'Previous Status: %1$s\nNew Status: %2$s\n', 'wc-message-notifier' ), $this->translate_status($old_status), $this->translate_status($new_status) );

            $message = $this->prepare_order_message($order, esc_html__('📦 Order Status Changed', 'wc-message-notifier'), $status_info);
            $this->send_notification($message);
        }
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
    
        // Add nonce field to form
        wp_nonce_field('telegram_notifier_settings', 'telegram_notifier_nonce');
    
        if (isset($_POST['wc_telegram_save_settings'])) {
            // Verify nonce with proper sanitization
            $nonce = isset($_POST['telegram_notifier_nonce']) ? sanitize_text_field(wp_unslash($_POST['telegram_notifier_nonce'])) : '';
            if (!wp_verify_nonce(sanitize_text_field($nonce), 'telegram_notifier_settings')) {
                wp_die(esc_html__('Security check failed', 'wc-message-notifier'));
            }
    
            // Sanitize and save settings
            $this->bot_token = sanitize_text_field(wp_unslash($_POST['bot_token'] ?? ''));
            $this->chat_id = sanitize_text_field(wp_unslash($_POST['chat_id'] ?? ''));
            $this->thread_id = sanitize_text_field(wp_unslash($_POST['thread_id'] ?? ''));
            $this->use_proxy = isset($_POST['use_proxy']);
            $this->proxy_type = sanitize_text_field(wp_unslash($_POST['proxy_type'] ?? 'http'));
            $this->proxy_ip = sanitize_text_field(wp_unslash($_POST['proxy_ip'] ?? ''));
            $this->proxy_port = sanitize_text_field(wp_unslash($_POST['proxy_port'] ?? ''));
            $this->proxy_username = sanitize_text_field(wp_unslash($_POST['proxy_username'] ?? ''));
            $this->proxy_password = sanitize_text_field(wp_unslash($_POST['proxy_password'] ?? ''));
            $this->use_custom_url = isset($_POST['use_custom_url']);
            $this->custom_api_url = esc_url_raw(wp_unslash($_POST['custom_api_url'] ?? ''));
    
            // Save to WordPress options
            update_option('telegram_notifier_bot_token', $this->bot_token);
            update_option('telegram_notifier_chat_id', $this->chat_id);
            update_option('telegram_notifier_thread_id', $this->thread_id);
            update_option('telegram_notifier_use_proxy', $this->use_proxy);
            update_option('telegram_notifier_proxy_type', $this->proxy_type);
            update_option('telegram_notifier_proxy_ip', $this->proxy_ip);
            update_option('telegram_notifier_proxy_port', $this->proxy_port);
            update_option('telegram_notifier_proxy_username', $this->proxy_username);
            update_option('telegram_notifier_proxy_password', $this->proxy_password);
            update_option('telegram_notifier_use_custom_url', $this->use_custom_url);
            update_option('telegram_notifier_custom_api_url', $this->custom_api_url);
    
            echo '<div class="updated"><p>' . esc_html__('Settings saved successfully.', 'wc-message-notifier') . '</p></div>';
        }

        if (isset($_POST['wc_telegram_test'])) {
            $test_message = esc_html__("🔔 This is a test message.\n\nIf you see this message, your settings are correct.", 'wc-message-notifier');
            $result = $this->send_notification($test_message);
            
            if ($result) {
                echo '<div class="updated"><p>' . esc_html__('Test message sent successfully.', 'wc-message-notifier') . '</p></div>';
            } else {
                echo '<div class="error"><p>' . esc_html__('Error sending test message. Please check logs.', 'wc-message-notifier') . '</p></div>';
            }
        }

        include(plugin_dir_path(__FILE__) . 'templates/settings-page.php');
    }
}

// Plugin activation hook
register_activation_hook(__FILE__, function() {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__('WooCommerce is required for this plugin to work.', 'wc-message-notifier'));
    }
});

// Initialize plugin
add_action('plugins_loaded', array('WC_Message_Notifier', 'get_instance'));
