<div class="wrap">
    <h1><?php esc_html_e('Telegram Notification Settings', 'telegram-notifier-for-woocommerce'); ?></h1>
    <form method="post">
        <h2><?php esc_html_e('Basic Settings', 'telegram-notifier-for-woocommerce'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Bot Token', 'telegram-notifier-for-woocommerce'); ?></th>
                <td>
                    <input type="text" name="bot_token" value="<?php echo esc_attr($this->bot_token); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Get token from @BotFather', 'telegram-notifier-for-woocommerce'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Chat ID', 'telegram-notifier-for-woocommerce'); ?></th>
                <td>
                    <input type="text" name="chat_id" value="<?php echo esc_attr($this->chat_id); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('For channel ID: @username- or numeric ID with hyphen', 'telegram-notifier-for-woocommerce'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Thread ID', 'telegram-notifier-for-woocommerce'); ?></th>
                <td>
                    <input type="text" name="thread_id" value="<?php echo esc_attr($this->thread_id); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Optional: Topic ID for supergroups', 'telegram-notifier-for-woocommerce'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('Proxy Settings', 'telegram-notifier-for-woocommerce'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Use Proxy', 'telegram-notifier-for-woocommerce'); ?></th>
                <td>
                    <input type="checkbox" name="use_proxy" <?php checked($this->use_proxy); ?>>
                    <p class="description"><?php esc_html_e('Enable proxy for API requests', 'telegram-notifier-for-woocommerce'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Proxy Type', 'telegram-notifier-for-woocommerce'); ?></th>
                <td>
                    <select name="proxy_type">
                        <option value="http" <?php selected($this->proxy_type, 'http'); ?>>HTTP</option>
                        <option value="https" <?php selected($this->proxy_type, 'https'); ?>>HTTPS</option>
                        <option value="socks5" <?php selected($this->proxy_type, 'socks5'); ?>>SOCKS5</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Proxy IP', 'telegram-notifier-for-woocommerce'); ?></th>
                <td>
                    <input type="text" name="proxy_ip" value="<?php echo esc_attr($this->proxy_ip); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Proxy Port', 'telegram-notifier-for-woocommerce'); ?></th>
                <td>
                    <input type="text" name="proxy_port" value="<?php echo esc_attr($this->proxy_port); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Proxy Username', 'telegram-notifier-for-woocommerce'); ?></th>
                <td>
                    <input type="text" name="proxy_username" value="<?php echo esc_attr($this->proxy_username); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Optional: Username if proxy requires authentication', 'telegram-notifier-for-woocommerce'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Proxy Password', 'telegram-notifier-for-woocommerce'); ?></th>
                <td>
                    <input type="password" name="proxy_password" value="<?php echo esc_attr($this->proxy_password); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Optional: Password if proxy requires authentication', 'telegram-notifier-for-woocommerce'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('API URL Settings', 'telegram-notifier-for-woocommerce'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Use Custom URL', 'telegram-notifier-for-woocommerce'); ?></th>
                <td>
                    <input type="checkbox" name="use_custom_url" <?php checked($this->use_custom_url); ?>>
                    <p class="description"><?php esc_html_e('Use custom API URL instead of official Telegram API', 'telegram-notifier-for-woocommerce'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Custom API URL', 'telegram-notifier-for-woocommerce'); ?></th>
                <td>
                    <input type="url" name="custom_api_url" value="<?php echo esc_url($this->custom_api_url); ?>" class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Available patterns: {bot_token}, {method}. Example: https://your-domain.com/telegram/{bot_token}/{method}', 'telegram-notifier-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <div class="card">
            <h3><?php esc_html_e('Bot Setup Guide:', 'telegram-notifier-for-woocommerce'); ?></h3>
            <ol>
                <li><?php esc_html_e('Visit @BotFather on Telegram', 'telegram-notifier-for-woocommerce'); ?></li>
                <li><?php esc_html_e('Create a new bot using /newbot command', 'telegram-notifier-for-woocommerce'); ?></li>
                <li><?php esc_html_e('Copy the received token to Bot Token field', 'telegram-notifier-for-woocommerce'); ?></li>
                <li><?php esc_html_e('Add the bot to your group/channel', 'telegram-notifier-for-woocommerce'); ?></li>
                <li><?php esc_html_e('Make the bot admin of the group/channel', 'telegram-notifier-for-woocommerce'); ?></li>
                <li><?php esc_html_e('Enter the correct Chat ID:', 'telegram-notifier-for-woocommerce'); ?>
                    <ul>
                        <li><?php esc_html_e('For channel: @username- or -100xxxxxxxxxx', 'telegram-notifier-for-woocommerce'); ?></li>
                        <li><?php esc_html_e('For group: -xxxxxxxxxx', 'telegram-notifier-for-woocommerce'); ?></li>
                    </ul>
                </li>
                <li><?php esc_html_e('Click "Send Test Message" to verify settings', 'telegram-notifier-for-woocommerce'); ?></li>
            </ol>
        </div>

        <p class="submit">
            <input type="submit" name="wc_telegram_save_settings" class="button-primary" value="<?php esc_attr_e('Save Settings', 'telegram-notifier-for-woocommerce'); ?>">
            <input type="submit" name="wc_telegram_test" class="button-secondary" value="<?php esc_attr_e('Send Test Message', 'telegram-notifier-for-woocommerce'); ?>">
        </p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const proxyCheckbox = document.querySelector('input[name="use_proxy"]');
    const proxyFields = document.querySelectorAll('.form-table tr:not(:first-child)');
    const customUrlCheckbox = document.querySelector('input[name="use_custom_url"]');
    const customUrlField = document.querySelector('input[name="custom_api_url"]').closest('tr');

    function toggleProxyFields() {
        const proxyTable = proxyCheckbox.closest('.form-table');
        const rows = proxyTable.querySelectorAll('tr:not(:first-child)');
        rows.forEach(row => {
            row.style.display = proxyCheckbox.checked ? 'table-row' : 'none';
        });
    }

    function toggleCustomUrlField() {
        customUrlField.style.display = customUrlCheckbox.checked ? 'table-row' : 'none';
    }

    // Initial state
    toggleProxyFields();
    toggleCustomUrlField();

    // Event listeners
    proxyCheckbox.addEventListener('change', toggleProxyFields);
    customUrlCheckbox.addEventListener('change', toggleCustomUrlField);
});
</script>
