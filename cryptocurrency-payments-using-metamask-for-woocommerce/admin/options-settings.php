<?php defined('ABSPATH') || exit;

if (class_exists('CSF')):

    $prefix = "cpmw_settings";

    CSF::createOptions($prefix, array(
        'framework_title' => esc_html__('Settings', 'cpmw'),
        'menu_title' => false,
        'menu_slug' => "cpmw-metamask-settings",
        'menu_capability' => 'manage_options',
        'menu_type' => 'submenu',
        'menu_parent' => 'woocommerce',
        'menu_position' => 103,
        'menu_hidden' => true,
        'nav' => 'inline',
        'show_bar_menu' => false,
        'show_sub_menu' => false,
        'show_reset_section' => false,
        'show_reset_all' => false,
        'theme' => 'light',

    ));

    CSF::createSection($prefix, array(

        'id' => 'general_options',
        'title' => esc_html__('General Options', 'cpmw'),
        'icon' => 'fa fa-cog',
        'fields' => array(

            array(
                'id' => 'user_wallet',
                'title' => __('Payment Address <span style="color:red">(Required)</span>', 'cpmw'),
                'type' => 'text',
                'placeholder' => '0x1dCXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                'validate' => 'csf_validate_required',
                'help' => esc_html__('Enter your default wallet address to receive crypto payments.', 'cpmw'),
                'desc' => 'Enter your default wallet address to receive crypto payments.<br>
											<span >You can use different payment addresses for different networks/chains in pro version.<a href="' . CPMW_BUY_PRO . '" target="_blank" > (Buy Pro) </a></span>',
            ),
            array(
                'id' => 'currency_conversion_api',
                'title' => esc_html__('Crypto Price API', 'cpmw'),
                'type' => 'select',
                'options' => array(
                    'cryptocompare' => __('CryptoCompare', 'cpmw'),
                    'openexchangerates' => __('Binance', 'cpmw'),
                ),
                'default' => 'openexchangerates',
                'desc' => 'It will convert product price from fiat currency to cryptocurrency in real time. Match your token symbol with CryptoCompare or Binance listed tokens for accurate pricing.<br>
											<span >You can add custom price for a token or use Coinbrain api in pro version. <a href="' . CPMW_BUY_PRO . '" target="_blank"> (Buy Pro) </a></span>',
            ),
            array(
                'id' => 'crypto_compare_key',
                'title' => __('CryptoCompare API Key <span style="color:red">(Required)</span>', 'cpmw'),
                'type' => 'text',
                'dependency' => array('currency_conversion_api', '==', 'cryptocompare'),
                'desc' => 'Check -<a href=" https://paywithcryptocurrency.net/get-cryptocompare-free-api-key/" target="_blank">How to retrieve CryptoCompare free API key?</a>',
            ),          
            array(
                'id' => 'openexchangerates_key',
                'title' => __('Openexchangerates API Key', 'cpmw'),
                'type' => 'text',   
                'dependency' => array('currency_conversion_api', '==', 'openexchangerates'),       
                'desc' => 'Please provide the API key if you are utilizing a store currency other than USD. Check -<a href="https://paywithcryptocurrency.net/get-openexchangerates-free-api-key/" target="_blank">How to retrieve openexchangerates free api key?</a>',

            ),
            array(
                'id' => 'Chain_network',
                'title' => esc_html__('Select Network/Chain', 'cpmw'),
                'type' => 'select',
                'options' => array(
                    '0x1' => __('Ethereum Mainnet (ERC20)', 'cpmw'),
                    '0xaa36a7' => __('Ethereum Sepolia (Testnet)', 'cpmw'),
                    '0x5' => __('Ethereum Goerli (Testnet)', 'cpmw'),
                    '0x38' => __('Binance Smart Chain (BEP20)', 'cpmw'),
                    '0x61' => __('Binance Smart Chain (Testnet)', 'cpmw'),
                    'avalanche' => __('Avalanche (Pro)', 'cpmw'),
                    'polygon' => __('Polygon Mainnet (Pro)', 'cpmw'),
                    'custom' => __('Any Custom Network (Pro)', 'cpmw'),
                ),
                'desc' => '<span >You can add custom network/chain or select multiple networks/chains in pro version.<a href="' . CPMW_BUY_PRO . '" target="_blank"> (Buy Pro) </a></span>',
                'default' => '0x1',

            ),
            array(
                'id' => 'eth_select_currency',
                'title' => __('Select Crypto Currency <span style="color:red">(Required )</span>', 'cpmw'),
                'type' => 'select',
                'validate' => 'csf_validate_required',
                'placeholder' => 'Select Crypto currency',
                'options' => array(
                    'ETH' => __('Ethereum', 'cpmw'),
                    'USDT' => __('USDT', 'cpmw'),
                ),
                'chosen' => true,
                'multiple' => true,
                'settings' => array('width' => '50%'),
                'dependency' => array('Chain_network', 'any', '0x1,0x5,0xaa36a7'),
                'desc' => '<span >You can add any custom token/coin in pro version. <a href="' . CPMW_BUY_PRO . '" target="_blank"> (Buy Pro) </a></span>',
                'default' => 'ETH',

            ),
            array(
                'id' => 'bnb_select_currency',
                'title' => __('Select Crypto Currency <span style="color:red">(Required )</span>', 'cpmw'),
                'type' => 'select',
                'placeholder' => 'Select Crypto Currency',
                'validate' => 'csf_validate_required',
                'options'  => array(
                    'BNB'  => __('BNB Binance Coin', 'cpmw'),
                    'USDT' => __('USDT', 'cpmw'),
                    'USDC' => __('USDC', 'cpmw'),
                    'BUSD' => __('BUSD', 'cpmw'),
                    // @nico: ng_coin
                ),
                'chosen' => true,
                'multiple' => true,
                'settings' => array('width' => '50%'),
                'dependency' => array('Chain_network', 'any', '0x38,0x61'),
                'desc' => '<span >You can add any custom token/coin in pro version. <a href="' . CPMW_BUY_PRO . '" target="_blank"> (Buy Pro) </a></span>',
                'default' => 'BNB',
            ),
            array(
                'id' => 'enable_refund',
                'title' => esc_html__('Enable Refund', 'cpmw'),
                'type' => 'switcher',
                'text_on' => 'Enable',
                'text_off' => 'Disable',
                'text_width' => 80,
                'desc' => '<span >A pro feature to refund customer via crypto wallet from order page. <a href="' . CPMW_BUY_PRO . '" target="_blank"> (Buy Pro) </a></span>',
                'help' => esc_html__('Enable refund option', 'cpmw'),
                'default' => true,
            ),

            array(
                'id' => 'payment_status',
                'title' => esc_html__('Payment Success: Order Status', 'cpmw'),
                'type' => 'select',
                'options' => apply_filters(
                    'cpmwp_settings_order_statuses',
                    array(
                        'default' => __('Woocommerce Default Status', 'cpmw'),
                        'on-hold' => __('On Hold', 'cpmw'),
                        'processing' => __('Processing', 'cpmw'),
                        'completed' => __('Completed', 'cpmw'),
                    )
                ),
                'desc' => __('Order status upon successful cryptocurrency payment.', 'cpmw'),
                'default' => 'default',
            ),

            array(
                'id' => 'redirect_page',
                'title' => esc_html__('Payment Success: Redirect Page', 'cpmw'),
                'type' => 'text',
                'placeholder' => 'https://coolplugins.net/my-account/orders/',
                'desc' => 'Enter custom url to redirect or leave blank to update order status on same page.',
            ),
            array(
                'id' => 'dynamic_messages',
                'title' => esc_html__('Customize Text Display', 'cpmw'),
                'type' => 'select',
                'options' => array(
                    'confirm_msg' => __('Payment Confirmation (Popup)', 'cpmw'),
                    'payment_process_msg' => __('Payment Processing (Popup)', 'cpmw'),
                    'rejected_message' => __('Payment Rejected (Popup)', 'cpmw'),
                    'payment_msg' => __('Payment Completed (Popup)', 'cpmw'),
                    'place_order_button' => __('Place Order Button (Checkout page)', 'cpmw'),
                    'select_a_currency' => __('Select Coin (Checkout page)', 'cpmw'),
                ),

                'desc' => __('Customize the text displayed by the plugin on the frontend.', 'cpmw'),
                'default' => 'place_order_button',
            ),
            array(
                'id' => 'confirm_msg',
                'title' => esc_html__('Payment Confirmation (Popup)', 'cpmw'),
                'type' => 'text',
                'dependency' => array('dynamic_messages', '==', 'confirm_msg'),
                'desc' => 'You can change it to your preferred text or leave it blank to keep the default text.',
                'placeholder' => __('Confirm Payment Inside Your Wallet!', 'cpmw'),
            ),
            array(
                'id' => 'payment_process_msg',
                'title' => esc_html__('Payment Processing (Popup)', 'cpmw'),
                'type' => 'text',
                'dependency' => array('dynamic_messages', '==', 'payment_process_msg'),
                'desc' => 'Custom message to show  while processing payment via blockchain.',
                'placeholder' => __('Payment in process.', 'cpmw'),
            ),
            array(
                'id' => 'rejected_message',
                'title' => esc_html__('Payment Rejected (Popup)', 'cpmw'),
                'type' => 'text',
                'dependency' => array('dynamic_messages', '==', 'rejected_message'),
                'desc' => 'Custom message to show  if you rejected payment via metamask.',
                'placeholder' => __('Transaction rejected. ', 'cpmw'),
            ),
            array(
                'id' => 'payment_msg',
                'title' => esc_html__('Payment Completed (Popup)', 'cpmw'),
                'type' => 'text',
                'dependency' => array('dynamic_messages', '==', 'payment_msg'),
                'placeholder' => __('Payment completed successfully.', 'cpmw'),
                'desc' => 'Custom message to show  if  payment confirm  by blockchain.',

            ),
            array(
                'id' => 'place_order_button',
                'title' => esc_html__('Place Order Button (Checkout page)', 'cpmw'),
                'type' => 'text',
                'dependency' => array('dynamic_messages', '==', 'place_order_button'),
                'placeholder' => __('Pay With Crypto Wallets', 'cpmw'),
                'desc' => 'Please specify a name for the "Place Order" button on the checkout page.',

            ),
            array(
                'id' => 'select_a_currency',
                'title' => esc_html__('Select Coin (Checkout page)', 'cpmw'),
                'type' => 'text',
                'dependency' => array('dynamic_messages', '==', 'select_a_currency'),
                'placeholder' => __('Please Select a Currency', 'cpmw'),
                'desc' => 'Please provide a name for the label that selects the currency on the checkout page.',

            ),
            array(
                'id' => 'enable_debug_log',
                'title' => esc_html__('Debug mode ', 'cpmw'),
                'type' => 'switcher',
                'text_on' => 'Enable',
                'text_off' => 'Disable',
                'text_width' => 80,
                'desc' => 'When enabled, payment error logs will be saved to WooCommerce > Status > <a href="' . esc_url(get_admin_url(null, "admin.php?page=wc-status&tab=logs")) . '">Logs.</a>',
                'help' => esc_html__('Enable debug mode', 'cpmwp'),
                'default' => true,
            ),

        ),
    ));
    CSF::createSection(
        $prefix,
        array(
            'title' => 'Wallets',
            'icon' => 'fas fa-wallet',
            'fields' => array(
                array(
                    'id' => 'supported_wallets',
                    'title' => 'Supported Wallets<strong style="color:red">(Pro only)</strong>',
                    'type' => 'fieldset',
                    'fields' => array(
                        array(
                            'type' => 'content',
                            'content' => '<a href="' . CPMW_BUY_PRO . '" target="_blan" ><img src="' . CPMW_URL . '/assets/images/wallets-promotion.png"></a>',
                        ),

                    ),

                ),

            ),

        )
    );

    CSF::createSection(
        $prefix,
        array(
            'title' => 'Networks/Chains',
            'icon' => 'fas fa-network-wired',
            'fields' => array(
                array(
                    'type' => 'content',
                    'content' => ' <center><h2 style="color:red"><a href="' . CPMW_BUY_PRO . '" target="_blan" >Buy Pro</a> Version To Activate Below Features</h2></center>',
                ),
                array(
                    'type' => 'content',
                    'content' => '<a href="' . CPMW_BUY_PRO . '" target="_blan" ><img src="' . CPMW_URL . '/assets/images/promotion.png"></a>',
                ),
            ),
        ));
    CSF::createSection(
        $prefix,
        array(
            'title' => 'Login With Crypto Wallets',
            'icon' => 'fas fa-key',

            'fields' => array(
                array(
                    'type' => 'content',
                    'content' => ' <center><h2 style="color:red"><a href="' . CPMW_BUY_PRO . '" target="_blan" >Buy Pro</a> Version To Activate Below Features</h2></center>',
                ),
                array(
                    'type' => 'content',
                    'content' => '<a href="' . CPMW_BUY_PRO . '" target="_blan" ><img src="' . CPMW_URL . '/assets/images/wallet-login.png"></a>',
                ),

            ),
        ));

    CSF::createSection($prefix, array(
        'title' => 'Free Test Tokens',
        'icon' => 'fas fa-rocket',
        'fields' => array(
            array(
                'type' => 'heading',
                'content' => 'Get Free Test Tokens to Test Payment via Metamask on Test Networks/Chains.',
            ),
            array(
                'type' => 'subheading',
                'content' => ' ETH Test Token For Sepolia Network: <a href="https://sepoliafaucet.com/" target="_blank">https://sepoliafaucet.com</a>',
            ),
            array(
                'type' => 'subheading',
                'content' => ' USDT Test Token For Sepolia Network: <a href="https://chaindrop.org/?chainid=11155111&token=0x6175a8471c2122f778445e7e07a164250a19e661" target="_blank">https://chaindrop.org</a>',
            ),
            array(
                'type' => 'subheading',
                'content' => ' ETH Test Token For Goerli Network: <a href="https://goerlifaucet.com/" target="_blank">https://goerlifaucet.com/</a>',
            ),
            array(
                'type' => 'subheading',
                'content' => 'Binance Test Tokens For Binance Network: <a href="https://testnet.binance.org/faucet-smart" target="_blank">https://testnet.binance.org/faucet-smart</a>',
            ),

        ),

    ));
    CSF::createSection($prefix, array(
        'title' => 'Buy Pro',
        'icon' => 'fas fa-shopping-cart',
        'fields' => array(
            array(
                'type' => 'content',
                'content' => '<style>
					                table {
					                  font-family: arial, sans-serif;
					                  border-collapse: collapse;
					                  width: 50%;

					                }
					                 th {

					                    color:#EF5508;
					                  }
					                td, th {
					                  border: 1px solid #dddddd;
					                  text-align: left;
					                  padding:20px;

					                }
					                tr td:first-child {
					                    font-weight: 600;
					                  }


					                .dashicons.dashicons-no{
					                    color:red;
					                }
					                .dashicons.dashicons-yes{
					                    color:green;
					                }
					                </style>

								<h2 class="elementor-heading-title elementor-size-default">Compare <b>Free v/s Pro</b></h2>

								<table>
					<tbody>
					<tr>
					<th>Features</th>
					<th>Free</th>
					<th>Pro</th>
					</tr>
					<tr>
					<td>Add custom tokens</td>
					<td><span class="dashicons dashicons-no" ></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
					<td>Add custom networks</td>
					<td><span class="dashicons dashicons-no"></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
					<td>Multiple wallets support</td>
					<td><span class="dashicons dashicons-no"></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
					<td>Token based discount</td>
					<td><span class="dashicons dashicons-no"></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
					<td>Custom price for token</td>
					<td><span class="dashicons dashicons-no"></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
				    <tr>
					<td>Custom price for native currency</td>
					<td><span class="dashicons dashicons-no"></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
					<td>Order refund via crypto</td>
					<td><span class="dashicons dashicons-no"></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
					<td>
					Mobile wallets support</td>
					<td><span class="dashicons dashicons-no"></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
					<td>WalletConnect integration</td>
					<td><span class="dashicons dashicons-no"></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
					<td>Login via Wallets</td>
					<td><span class="dashicons dashicons-no"></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
					<td>Pay via QR code</td>
					<td><span class="dashicons dashicons-no"></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					<tr>
					<td>Premium support</td>
					<td><span class="dashicons dashicons-no"></span></td>
					<td><span class="dashicons dashicons-yes"></span></td>
					</tr>
					</tbody>
					</table>
					<br><h1> <a class="button button-primary" href="' . CPMW_BUY_PRO . '" target="_blan">Buy Pro</a> <a class="button button-primary" href="' . CPMW_DEMO_URL . '" target="_blan"> Demo </a> <a class="button button-secondary" href="https://paywithcryptocurrency.net/docs/plugin-documentation/" target="_blan">Docs</a></h1>
					',
            ),

        ),

    ));

endif;
