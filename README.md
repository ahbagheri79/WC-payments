  WC Payments Plugin Installation Guide

# WC Payments Plugin Installation Guide
-------------------------------
## Supported Payment Gateways
### -Iran Kish
  (More gateways coming soon)

---

## Tested with:
- WordPress version 6.7
- WooCommerce version 9.5.2


This guide will walk you through two methods to install the **WC Payments Plugin** in your WordPress site: using **WP-CLI** or manually uploading the plugin files. 🚀

* * *

Method 1: Installing via WP-CLI
-------------------------------

WP-CLI is a powerful command line interface for managing WordPress installations. Follow these steps to install the plugin via WP-CLI:

1.  **Open Your Command Line Interface**  
    First, ensure you have SSH access to your WordPress server. Open the terminal or command line on your server. 💻
2.  **Navigate to Your WordPress Installation**  
    Use the `cd` command to go to the root directory of your WordPress site:
    
        cd /path/to/your/wordpress/installation
    
3.  **Install the Plugin Using WP-CLI**  
    Run the following command to install the WC Payments Plugin:
    
        wp plugin install https://github.com/ahbagheri79/WC-payments/archive/refs/heads/main.zip --activate
    
    This command will:
    *   Download the plugin from GitHub.
    *   Automatically install it in the plugins directory.
    *   Activate the plugin immediately after installation. 🔥
4.  **Verify the Installation**  
    To confirm that the plugin is activated, you can use the following WP-CLI command:
    
        wp plugin status wc-payments
    
    This should show that the plugin is active. ✔️

* * *

Method 2: Manual Installation
-----------------------------

If you prefer to manually install the plugin, follow these steps:

1.  **Download the Plugin Files**  
    Go to the GitHub repository and download the plugin ZIP file by clicking on the green "Code" button and selecting **Download ZIP**: [WC Payments Plugin - GitHub](https://github.com/ahbagheri79/WC-payments) 📥
2.  **Extract the Plugin Files**  
    After downloading the ZIP file, extract its contents to a folder on your local machine. 🖥️
3.  **Upload the Plugin to Your WordPress Site**  
    \- Use an FTP client (e.g., FileZilla) or your hosting control panel's file manager to access your WordPress site's files.  
    \- Navigate to the `wp-content/plugins/` directory.  
    \- Upload the extracted plugin folder (it should be named `WC-payments`) into the `plugins` directory. 🔧
4.  **Activate the Plugin via the WordPress Admin Dashboard**  
    \- Log in to your WordPress admin dashboard.  
    \- Go to **Plugins** > **Installed Plugins**.  
    \- Find **WC Payments** in the list of installed plugins.  
    \- Click **Activate** next to the plugin. ⚡
5.  **Verify the Installation**  
    After activation, you should see the WC Payments settings in your WordPress admin area. You can now configure the plugin as needed. 🔑

* * *

Troubleshooting
---------------

*   **Plugin Not Showing Up**: Make sure you have placed the plugin folder in the `wp-content/plugins/` directory.
*   **Installation Errors**: Check your server's PHP version and WordPress requirements to ensure compatibility with the plugin.

* * *

Important Notes
---------------

*   **No Sales in Iranian Marketplaces**: This plugin is completely free, and no individual or company is allowed to sell it on Iranian marketplaces or any other platform. 🚫
*   **Contributions Welcome**: If you extend or modify this plugin, you are required to push your changes back to this repository. This ensures that the improvements are shared with the community and remain free for everyone to use. 🤝

Feel free to reach out via GitHub Issues if you encounter any problems! 💬

## Release Notes
---------------
### 1.0.1
- Added support for Iran Kish payment gateway.
