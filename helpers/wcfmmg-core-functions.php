<?php
if(!function_exists('wcfmmg_woocommerce_inactive_notice')) {
	function wcfmmg_woocommerce_inactive_notice() {
		?>
		<div id="message" class="error">
		<p><?php printf( __( '%sWCFM Marketplace Migrator is inactive.%s The %sWooCommerce plugin%s must be active for the WCFM Marketplace Migrator to work. Please %sinstall & activate WooCommerce%s', 'wc-multivendor-marketplace' ), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=woocommerce' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
		</div>
		<?php
	}
}

if(!function_exists('wcfmmg_wcfm_inactive_notice')) {
	function wcfmmg_wcfm_inactive_notice() {
		?>
		<div id="message" class="error">
		<p><?php printf( __( '%sWCFM Marketplace Migrator is inactive.%s The %sWooCommerce Frontend Manager%s must be active for the WCFM Marketplace Migrator to work. Please %sinstall & activate WooCommerce Frontend Manager%s', 'wc-multivendor-marketplace' ), '<strong>', '</strong>', '<a target="_blank" href="https://wordpress.org/plugins/wc-frontend-manager/">', '</a>', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=wc+frontend+manager' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
		</div>
		<?php
	}
}

if(!function_exists('wcfmmg_marketplace_inactive_notice')) {
	function wcfmmg_marketplace_inactive_notice() {
		?>
		<div id="message" class="error">
		<p><?php printf( __( '%sWCFM Marketplace Migrator is inactive.%s Please install & activate your old multi-vendor plugin.', 'wc-multivendor-marketplace' ), '<strong>', '</strong>' ); ?></p>
		</div>
		<?php
	}
}

if(!function_exists('wcfmmg_active_notice')) {
	function wcfmmg_active_notice() {
		?>
		<div id="message" class="notice notice-warning">
		<p><?php printf( __( '%sWCFM Marketplace Migrator is active.%s Do you want to run migration, then %sclick here%s.', 'wc-multivendor-marketplace' ), '<strong>', '</strong>', '<a href="'.admin_url( 'index.php?page=wcfmmg-migrator' ).'">', '</a>' ); ?></p>
		</div>
		<?php
	}
}

add_filter( 'wcfm_enable_setup_wizard', '__return_false' );
?>