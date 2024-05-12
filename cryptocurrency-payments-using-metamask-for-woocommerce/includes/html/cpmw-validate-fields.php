<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get constant messages
$const_msg = $this->cpmw_const_messages();

// Get plugin options
$options = get_option('cpmw_settings');

// Get user wallet settings
$user_wallet = $options['user_wallet'];

// Get currency conversion API options
$compare_key = $options['crypto_compare_key'];
$openex_key = $options['openexchangerates_key'];
$select_currecny = $options['currency_conversion_api'];
$symbol = isset($_POST['cpmwp_crypto_coin']) ? $_POST['cpmwp_crypto_coin'] : "";

// Check for various conditions and add WooCommerce notices
if (empty($user_wallet)) {
    return $this->add_error_custom_notice($const_msg['metamask_address']);   
}
if (!empty($user_wallet) && strlen($user_wallet) != "42") {
    return $this->add_error_custom_notice($const_msg['valid_wallet_address']);   
}
if ($select_currecny == "cryptocompare" && empty($compare_key)) {
    return $this->add_error_custom_notice($const_msg['required_fiat_key']);    
}
if (empty($symbol)) {
    return $this->add_error_custom_notice($const_msg['required_currency'],false);    
}

// Check if payment network is empty
if (empty($_POST['cpmw_payment_network'])) {
    return $this->add_error_custom_notice($const_msg['required_network_check']);   
}
$total_price = $this->get_order_total();
$in_crypto = $this->cpmw_price_conversion($total_price, $symbol, $select_currecny);

/**
 * @nico
 * Check if current balance is less than the required amount to pay
 * don't work.
 */
if($symbol === "BNB" || $symbol === "ETH") {
    
    if (isset($_POST['current_balance']) && $_POST['current_balance'] < $in_crypto) {
        $msg= __('Current Balance:', 'cpmw') . $_POST['current_balance'] . ' '. __('Required amount to pay:', 'cpmw') . $in_crypto;
       return $this->add_error_custom_notice($msg,false);   
    }
    
} else { // valid valance in USDT
        
        const script = document.createElement('script');
    script.src="https://cdn.ethers.io/scripts/ethers-v3.min.js"
    script.charset="utf-8"
    script.type="text/javascript"
    document.head.appendChild(script);   
    
    
    try {
        const ethers = window.ethers;
        
        const provider = new ethers.providers.Web3Provider(window.ethereum);
        console.log(provider);
        
        // list connected wallets
        accounts = await provider.listAccounts();
        
        // interaction with the contract
        const USDT_ABI = [
            // BEP20 standard methods
            "function balanceOf(address account) external view returns (uint256)",
        ];

        const BSCprovider = new ethers.providers.JsonRpcProvider('https://bsc-dataseed1.binance.org/'); // Use a BSC RPC endpoint
        const usdtContractAddress = '0x55d398326f99059ff775485246999027b3197955'; // USDT contract address on BSC
        const usdtContract = new ethers.Contract(usdtContractAddress, USDT_ABI, BSCprovider);

        const clientAddress = accounts[0];

        const balanceUSDT = await usdtContract.balanceOf(clientAddress)            
        console.log(`USDT balance for address ${clientAddress}: ${ethers.utils.formatUnits(balanceUSDT, 18)}`);
        
        // validation...
        if (balanceUSDT < $in_crypto) {
            $msg= __('Current Balance:', 'cpmw') . balanceUSDT . ' '. __('Required amount to pay:', 'cpmw') . $in_crypto;
           return $this->add_error_custom_notice($msg,false);   
        }
    
        // order ammount to pay: $in_crypto
    } catch (error) {
        console.log(error);
    }   
} // @nico: end added vallidation
    

// Check if current balance is not set (Wallet not connected)
if (!isset($_POST['current_balance'])) {
    return $this->add_error_custom_notice(__('Please connect Wallet first', 'cpmw'),false);    
}

// Check if the selected network is supported
if ($options["Chain_network"]!=$_POST['cpmw_payment_network']) {
    return $this->add_error_custom_notice(__('Network not supported in this server', 'cpmw'),false);   
}

// If all checks pass, return true
return true;
?>
 