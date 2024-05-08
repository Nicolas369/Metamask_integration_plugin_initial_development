<?php
if (!defined('ABSPATH')) {
    exit();
}
if (!class_exists('CPMW_TRANSACTION_TABLE')) {
    class CPMW_TRANSACTION_TABLE
    {
        public function __construct()
        {

        }

        //Transaction table callback

        public static function cpmw_transaction_table()
        {
            $list_table = new Cpmw_metamask_list();
            echo '<div class="wrap"><h2>' . __("Crypto Transactions", "cpmw") . '</h2>';

            $list_table->prepare_items();
            ?>
           <form method="post" class="alignleft">
            <select name="payment_status">
                <option value=""><?php echo esc_html__('All Transactions', 'cpmwp'); ?></option>
                <option value="awaiting" <?php selected(isset($_GET['status']), 'awaiting') ?>>
                    <?php echo esc_html__('Awaiting Confirmation', 'cpmwp'); ?>
                </option>
                <option value="completed" <?php selected(isset($_GET['status']), 'completed') ?>>
                    <?php echo esc_html__('Confirmed', 'cpmwp'); ?>
                </option>
                <option value="unsuccessful" <?php selected(isset($_GET['status']), 'unsuccessful') ?>>
                    <?php echo esc_html__('Unsuccessful', 'cpmwp'); ?>
                </option>
            </select>

            <button class="button" type="submit"><?php echo esc_html__('Filter', 'cpmwp'); ?></button>
            <a href="<?php echo esc_url(admin_url('admin.php?page=cpmw-metamask')) ?>" class="button"><?php echo esc_html__('Reset', 'cpmwp'); ?></a>
            </form>
            <form method="post">
                <input type="hidden" name="page" value="my_list_test" />
                <?php
            $list_table->search_box('search', 'search_id');
            ?>
            </form>
                    <?php
            $list_table->display();

            echo '</div>';
        }


    }
}