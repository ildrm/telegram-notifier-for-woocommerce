<div class="wrap">
    <h1><?php esc_html_e('Message Notification Settings', 'wc-message-notifier'); ?></h1>
    <form method="post">
        <h2><?php esc_html_e('Basic Settings', 'wc-message-notifier'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Bot Token', 'wc-message-notifier'); ?></th>
                <td>
                    <input type="text" name="bot_token" value="<?php echo esc_attr($this->bot_token); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Get token from @BotFather', 'wc-message-notifier'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Chat ID', 'wc-message-notifier'); ?></th>
                <td>
                    <input type="text" name="chat_id" value="<?php echo esc_attr($this->chat_id); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('For channel ID: @username- or numeric ID with hyphen', 'wc-message-notifier'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Thread ID', 'wc-message-notifier'); ?></th>
                <td>
                    <input type="text" name="thread_id" value="<?php echo esc_attr($this->thread_id); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Optional: Topic ID for supergroups', 'wc-message-notifier'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('Proxy Settings', 'wc-message-notifier'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Use Proxy', 'wc-message-notifier'); ?></th>
                <td>
                    <input type="checkbox" name="use_proxy" <?php checked($this->use_proxy); ?>>
                    <p class="description"><?php esc_html_e('Enable proxy for API requests', 'wc-message-notifier'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Proxy Type', 'wc-message-notifier'); ?></th>
                <td>
                    <select name="proxy_type">
                        <option value="http" <?php selected($this->proxy_type, 'http'); ?>>HTTP</option>
                        <option value="https" <?php selected($this->proxy_type, 'https'); ?>>HTTPS</option>
                        <option value="socks5" <?php selected($this->proxy_type, 'socks5'); ?>>SOCKS5</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Proxy IP', 'wc-message-notifier'); ?></th>
                <td>
                    <input type="text" name="proxy_ip" value="<?php echo esc_attr($this->proxy_ip); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Proxy Port', 'wc-message-notifier'); ?></th>
                <td>
                    <input type="text" name="proxy_port" value="<?php echo esc_attr($this->proxy_port); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Proxy Username', 'wc-message-notifier'); ?></th>
                <td>
                    <input type="text" name="proxy_username" value="<?php echo esc_attr($this->proxy_username); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Optional: Username if proxy requires authentication', 'wc-message-notifier'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Proxy Password', 'wc-message-notifier'); ?></th>
                <td>
                    <input type="password" name="proxy_password" value="<?php echo esc_attr($this->proxy_password); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Optional: Password if proxy requires authentication', 'wc-message-notifier'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('API URL Settings', 'wc-message-notifier'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Use Custom URL', 'wc-message-notifier'); ?></th>
                <td>
                    <input type="checkbox" name="use_custom_url" <?php checked($this->use_custom_url); ?>>
                    <p class="description"><?php esc_html_e('Use custom API URL instead of official Telegram API', 'wc-message-notifier'); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Custom API URL', 'wc-message-notifier'); ?></th>
                <td>
                    <input type="url" name="custom_api_url" value="<?php echo esc_url($this->custom_api_url); ?>" class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Available patterns: {bot_token}, {method}. Example: https://your-domain.com/telegram/{bot_token}/{method}', 'wc-message-notifier'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <div class="card">
            <h3><?php esc_html_e('Bot Setup Guide:', 'wc-message-notifier'); ?></h3>
            <ol>
                <li><?php esc_html_e('Visit @BotFather on Telegram', 'wc-message-notifier'); ?></li>
                <li><?php esc_html_e('Create a new bot using /newbot command', 'wc-message-notifier'); ?></li>
                <li><?php esc_html_e('Copy the received token to Bot Token field', 'wc-message-notifier'); ?></li>
                <li><?php esc_html_e('Add the bot to your group/channel', 'wc-message-notifier'); ?></li>
                <li><?php esc_html_e('Make the bot admin of the group/channel', 'wc-message-notifier'); ?></li>
                <li><?php esc_html_e('Enter the correct Chat ID:', 'wc-message-notifier'); ?>
                    <ul>
                        <li><?php esc_html_e('For channel: @username- or -100xxxxxxxxxx', 'wc-message-notifier'); ?></li>
                        <li><?php esc_html_e('For group: -xxxxxxxxxx', 'wc-message-notifier'); ?></li>
                    </ul>
                </li>
                <li><?php esc_html_e('Click "Send Test Message" to verify settings', 'wc-message-notifier'); ?></li>
            </ol>
        </div>

        <p class="submit">
            <input type="submit" name="wc_telegram_save_settings" class="button-primary" value="<?php esc_attr_e('Save Settings', 'wc-message-notifier'); ?>">
            <input type="submit" name="wc_telegram_test" class="button-secondary" value="<?php esc_attr_e('Send Test Message', 'wc-message-notifier'); ?>">
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
