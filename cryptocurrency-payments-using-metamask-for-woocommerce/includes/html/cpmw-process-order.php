<?php
if (!defined('ABSPATH')) {
    exit;
}
// Get constant messages
$const_msg = $this->cpmw_const_messages();

// Get plugin options
$options = get_option('cpmw_settings');

// Get supported network names
$network_name = $this->cpmw_supported_networks();

// Default messages
$payment_msg = !empty($options['payment_msg']) ? $options['payment_msg'] : __("Payment Completed Successfully", "cpmw");
$confirm_msg = !empty($options['confirm_msg']) ? $options['confirm_msg'] : __("Confirm Payment in your wallet", "cpmw");
$process_msg = !empty($options['payment_process_msg']) ? $options['payment_process_msg'] : __("Payment in process", "cpmw");
$rejected_msg = !empty($options['rejected_message']) ? $options['rejected_message'] : __("Transaction Rejected ", "cpmw");
$place_order_button=(isset($options['place_order_button']) && !empty($options['place_order_button'])) ? $options['place_order_button']: __('Pay With Crypto Wallets', 'cpmwp');
// Get network and redirect options
$network = !empty($options['Chain_network']) ? $options['Chain_network'] : "";
$redirect = !empty($options['redirect_page']) ? $options['redirect_page'] : "";

// Determine crypto currency based on network
$crypto_currency = ($network == '0x1' || $network == '0x5') ? $options["eth_select_currency"] : $options["bnb_select_currency"];

// Get order details
$order = new WC_Order($order_id);
$total = $order->get_total();
$nonce = wp_create_nonce('cpmw_metamask_pay'.$order_id);
$user_wallet = $order->get_meta('cpmwp_user_wallet');
$in_crypto = $order->get_meta('cpmwp_in_crypto');
$currency_symbol = $order->get_meta('cpmwp_currency_symbol');
$payment_status = $order->get_status();

// Get additional network and token information

// @nico: follow the cpmw_add_tokens for add tokens...
$add_tokens = $this->cpmw_add_tokens();
$token_address = isset($add_tokens[$network][$currency_symbol]) ? $add_tokens[$network][$currency_symbol] : '';
$transaction_id = (!empty($order->get_meta('TransactionId'))) ? $order->get_meta('TransactionId') : "";
$sig_token_address = $order->get_meta('cpmwp_contract_address');

// Generate signature for transaction request
$secret_key = $this->cpmw_get_secret_key();
$tx_req_data = json_encode(
    array(
        'order_id' => $order_id,
        'selected_network' => $network,
        'receiver' => strtoupper($user_wallet),
        'amount' => str_replace(',', '', $in_crypto),
        'token_address' => strtoupper($sig_token_address)
    )
);
$block_explorer =$this->cpmw_get_explorer_url();
$signature = hash_hmac('sha256', $tx_req_data, $secret_key);
$filePaths = glob(CPMW_PATH . '/assets/pay-with-metamask/build/main' . '/*.php');
$fileName = pathinfo($filePaths[0], PATHINFO_FILENAME);
$jsbuildUrl=str_replace('.asset','',$fileName);
// Enqueue required scrips 
wp_enqueue_script('cpmw_react_widget', CPMW_URL . 'assets/pay-with-metamask/build/main/'.$jsbuildUrl.'.js', array('wp-element'), CPMW_VERSION, true);

wp_localize_script( 'cpmw_react_widget', "extradataRest",
    array(
        'url'                   => CPMW_URL,
        'supported_networks'    => $network_name,
        'restUrl'               => get_rest_url() . 'pay-with-metamask/v1/',
        'fiatSymbol'            => get_woocommerce_currency_symbol(),
        'totalFiat'             => $total, // @nico: fiat
        'network_name'          => $network_name[$network], // @nico: network
        'token_address'         => $token_address, // @nico: token    
        'transaction_id'        => $transaction_id,
        'const_msg'             => $const_msg,
        'wallet_image'          => CPMW_URL . 'assets/images/metamask.png',
        'redirect'              => $redirect,
        'currency_logo'         => $this->cpmw_get_coin_logo($currency_symbol),
        'order_page'            => get_home_url() . '/my-account/orders/',
        'currency_symbol'       => $currency_symbol,
        'confirm_msg'           => $confirm_msg,
        'block_explorer'        => $block_explorer[$network],
        'network'               => $network,
        'is_paid'               => $order->is_paid(),
        'decimalchainId'        => isset($network) ? hexdec($network) : false,
        'process_msg'           => $process_msg,
        'payment_msg'           => $payment_msg,
        'rejected_msg'          => $rejected_msg,
        'in_crypto'             => str_replace(',', '', $in_crypto),
        'receiver'              => $user_wallet,   // @nico: receiver wallet     
        'order_status'          => $payment_status,
        'id'                    => $order_id,
        'place_order_btn'       => $place_order_button,
        'nonce'                 => wp_create_nonce('wp_rest'),
        'payment_status'        => $options['payment_status'],        
        'signature'             => $signature
    ));

wp_enqueue_style('cpmw_custom_css', CPMW_URL . 'assets/css/style.css', array(), CPMW_VERSION);

$trasn_id=$order->get_meta('TransactionId');
$link_hash = "--";

if (!empty($trasn_id) && $trasn_id != "false") {
   
    $networkDomain = isset($block_explorer[$network]) ? $block_explorer[$network] : '';
    if (!empty($networkDomain)) {
        $link_hash = '<a href="https://' . $networkDomain . '/tx/' . $trasn_id . '" target="_blank">' . $trasn_id . '</a>';
    }
}


// Display payment information
echo '<div class="cmpw_meta_connect1" id="cmpw_meta_connect">
<div class="ccpwp-card">
<div class="ccpwp-card__image ccpwp-loading"></div>
<div class="ccpwp-card__title ccpwp-loading"></div>
<div class="ccpwp-card__description ccpwp-loading"></div>
</div>
</div>';
?>
<section class="cpmw-woocommerce-woocommerce-order-details">
    <h2 class="woocommerce-order-details__title"><?php echo __('Crypto payment details','cpmw') ?></h2>
    <table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
        <tbody>
            <tr>
                <th scope="row">   <?php echo __('Price:', 'cpmw') ?></th>
                <td><?php echo esc_html($in_crypto . $currency_symbol) ?></td>
            </tr>
            <tr>
                <th scope="row"> <?php echo __('Payment Status','cpmw') ?></th>
                <td class="cpmwp_statu_<?php echo $order->get_status(); ?>"><?php echo $order->get_status(); ?></td>
            </tr>
            <?php
             if (!empty($trasn_id)&& $trasn_id != "false") {
            ?>
             <tr>
                <th scope="row"> <?php echo __('Transaction id:', 'cpmw') ?></th>
                <td><?php echo wp_kses_post( $link_hash )?></td>
            </tr>
            <?php
             }
            ?>

        </tbody>
    </table>
</section>

        <?php
