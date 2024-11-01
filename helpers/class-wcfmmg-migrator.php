<?php
/**
 * WCFM Marketplace Vendor Store Setup Class
 * 
 * @since 1.0.0
 * @package wcfm/helpers
 * @author WC Lovers
 */
if (!defined('ABSPATH')) {
    exit;
}

class WCFMmg_Migrator {

	/** @var string Currenct Step */
	private $step = '';

	/** @var array Steps for the setup wizard */
	private $steps = array();

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'wcfm_admin_menus' ) );
		add_action( 'admin_init', array( $this, 'wcfmmg_migration' ) );
	}

	/**
	 * Add admin menus/screens.
	 */
	public function wcfm_admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'wcfmmg-migrator', '' );
	}
	
	/**
	 * Show the setup wizard.
	 */
	public function wcfmmg_migration() {
		global $WCFM, $WCFMmg;
		if ( filter_input(INPUT_GET, 'page') != 'wcfmmg-migrator') {
			return;
		}
		
		if (!WCFMmg_Dependencies::wcfm_plugin_active_check()) {
			if (isset($_POST['wcfmmg_install_wcfm'])) {
				$this->install_wcfm();
			}
			$this->install_wcfm_view();
			exit();
		}

		$default_steps = array(
				'introduction' => array(
					'name' => __('Introduction', 'wc-frontend-manager' ),
					'view' => array($this, 'wcfmmg_migration_introduction'),
					'handler' => '',
				),
				'store-stat' => array(
					'name' => __('Store Stat', 'wc-multivendor-marketplace-migration'),
					'view' => array($this, 'wcfmmg_migration_store_stat'),
					'handler' => ''
				),
				'store-process' => array(
					'name' => __('Processing', 'wc-multivendor-marketplace-migration'),
					'view' => array($this, 'wcfmmg_migration_store_process'),
					'handler' => array($this, 'wcfmmg_migration_store_process_save')
				),
				'next_steps' => array(
					'name' => __('Complete!', 'wc-frontend-manager'),
					'view' => array($this, 'wcfmmg_migration_complete'),
					'handler' => '',
				),
		);
		
		$this->steps = apply_filters('wcfmmg_migration_steps', $default_steps);
		$current_step = filter_input(INPUT_GET, 'step');
		$this->step = $current_step ? sanitize_key($current_step) : current(array_keys($this->steps));
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script('jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array('jquery'), '2.70', true);
		
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
		wp_enqueue_style( 'wc-setup', WC()->plugin_url() . '/assets/css/wc-setup.css', array('dashicons', 'install'), WC_VERSION);
		wp_enqueue_style( 'wcfm-setup', $WCFM->plugin_url . 'assets/css/setup/wcfm-style-dashboard-setup.css', array('wc-setup'), $WCFM->version );
		wp_enqueue_style( 'wcfm_fa_icon_css',  $WCFM->plugin_url . 'assets/fonts/font-awesome/css/font-awesome.min.css', array(), $WCFM->version );
		
		if (!empty($_POST['save_step']) && isset($this->steps[$this->step]['handler'])) {
				call_user_func($this->steps[$this->step]['handler'], $this);
		}

		ob_start();
		$this->wcfmmg_migration_header();
		$this->wcfmmg_migration_steps();
		$this->wcfmmg_migration_content();
		$this->wcfmmg_migration_footer();
		exit();
	}

	/**
	 * Get slug from path
	 * @param  string $key
	 * @return string
	 */
	private static function format_plugin_slug($key) {
			$slug = explode('/', $key);
			$slug = explode('.', end($slug));
			return $slug[0];
	}

	/**
	 * Get the URL for the next step's screen.
	 * @param string step   slug (default: current step)
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 * @since 1.0.0
	 */
	public function get_next_step_link($step = '') {
		if (!$step) {
			$step = $this->step;
		}

		$keys = array_keys($this->steps);
		if (end($keys) === $step) {
			return admin_url();
		}

		$step_index = array_search($step, $keys);
		if (false === $step_index) {
			return '';
		}

		return add_query_arg('step', $keys[$step_index + 1]);
	}

	/**
	 * Setup Wizard Header.
	 */
	public function wcfmmg_migration_header() {
		global $WCFM, $WCFMmg;
		
		$logo_image_url = $WCFMmg->plugin_url . 'assets/images/wcfmmp-75x75.png';
		
		set_current_screen();
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
			<head>
				<meta name="viewport" content="width=device-width" />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title><?php esc_html_e('WCFM Marketplace Migration', 'wc-multivendor-marketplace-migration'); ?></title>
				<?php wp_print_scripts('jquery-blockui'); ?>
				<?php
				?>
				<?php do_action('admin_print_scripts'); ?>
				<?php do_action('admin_print_styles'); ?>
				<?php do_action('admin_head'); ?>
				<style type="text/css">
					.wc-setup-steps {
						justify-content: center;
					}
				</style>
			</head>
			<body class="wc-setup wp-core-ui">
			 <h1 id="wc-logo"><a target="_blank" href="<?php echo site_url(); ?>"><img width="75" height="75" src="<?php echo $logo_image_url; ?>" alt="<?php echo get_bloginfo('title'); ?>" /><span><?php _e( 'WCFM Marketplace Migration', 'wc-multivendor-marketplace-migration' ); ?></span></a></h1>
			<?php
	}

	/**
	 * Output the steps.
	 */
	public function wcfmmg_migration_steps() {
		$ouput_steps = $this->steps;
		array_shift($ouput_steps);
		?>
		<ol class="wc-setup-steps">
			<?php foreach ($ouput_steps as $step_key => $step) : ?>
			  <li class="<?php
					if ($step_key === $this->step) {
							echo 'active';
					} elseif (array_search($this->step, array_keys($this->steps)) > array_search($step_key, array_keys($this->steps))) {
							echo 'done';
					}
					?>">
					<?php echo esc_html($step['name']); ?>
				</li>
		<?php endforeach; ?>
		</ol>
		<?php
	}

	/**
	 * Output the content for the current step.
	 */
	public function wcfmmg_migration_content() {
		echo '<div class="wc-setup-content">';
		call_user_func($this->steps[$this->step]['view'], $this);
		echo '</div>';
	}
	
	/**
	 * Content for install wcfm view
	 */
	public function install_wcfm_view() {
		global $WCFMmg;
		
		set_current_screen();
			?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
					<head>
							<meta name="viewport" content="width=device-width" />
							<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
							<title><?php esc_html_e('WCFM Marketplace &rsaquo; Setup Wizard', 'wc-multivendor-marketplace'); ?></title>
							<?php do_action('admin_print_styles'); ?>
							<?php do_action('admin_head'); ?>
							<style type="text/css">
									body {
											margin: 100px auto 24px;
											box-shadow: none;
											background: #f1f1f1;
											padding: 0;
											max-width: 700px;
									}
									#wc-logo {
											border: 0;
											margin: 0 0 24px;
											padding: 0;
											text-align: center;
									}
									#wc-logo a {
										color: #00897b;
										text-decoration: none;
									}
									
									#wc-logo a span {
										padding-left: 10px;
										padding-top: 23px;
										display: inline-block;
										vertical-align: top;
										font-weight: 700;
									}
									.wcfm-install-woocommerce {
											box-shadow: 0 1px 3px rgba(0,0,0,.13);
											padding: 24px 24px 0;
											margin: 0 0 20px;
											background: #fff;
											overflow: hidden;
											zoom: 1;
									}
									.wcfm-install-woocommerce .button-primary{
											font-size: 1.25em;
											padding: .5em 1em;
											line-height: 1em;
											margin-right: .5em;
											margin-bottom: 2px;
											height: auto;
									}
									.wcfm-install-woocommerce{
											font-family: sans-serif;
											text-align: center;    
									}
									.wcfm-install-woocommerce form .button-primary{
											color: #fff;
											background-color: #00798b;
											font-size: 16px;
											border: 1px solid #00798b;
											width: 230px;
											padding: 10px;
											margin: 25px 0 20px;
											cursor: pointer;
									}
									.wcfm-install-woocommerce form .button-primary:hover{
											background-color: #000000;
									}
									.wcfm-install-woocommerce p{
											line-height: 1.6;
									}

							</style>
					</head>
					<body class="wcfm-setup wp-core-ui">
						<h1 id="wc-logo"><a href="http://wclovers.com/"><img src="<?php echo $WCFMmg->plugin_url; ?>assets/images/wcfmmp-75x75.png" alt="WCFM" /><span>WCFM Marketplace Migration</span></a></h1>
						<div class="wcfm-install-woocommerce">
							<p><?php _e('WCFM Maketplace migration requires WCfM Core plugin to be active!', 'wc-multivendor-marketplace'); ?></p>
							<form method="post" action="" name="wcfmmg_install_wcfm">
								<?php submit_button(__('Install WCfM Core', 'wc-multivendor-marketplace' ), 'primary', 'wcfmmg_install_wcfm'); ?>
								<?php wp_nonce_field('wcfmmg-install-wcfm'); ?>
							</form>
						</div>
					</body>
			</html>
			<?php
	}

	/**
	 * Install wcfm if not exist
	 * @throws Exception
	 */
	public function install_wcfm() {
		check_admin_referer('wcfmmg-install-wcfm');
		include_once( ABSPATH . 'wp-admin/includes/file.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		WP_Filesystem();
		$skin = new Automatic_Upgrader_Skin;
		$upgrader = new WP_Upgrader($skin);
		$installed_plugins = array_map(array(__CLASS__, 'format_plugin_slug'), array_keys(get_plugins()));
		$plugin_slug = 'wc-frontend-manager';
		$plugin = 'wc-frontend-manager/wc_frontend_manager.php';
		$installed = false;
		$activate = false;
		// See if the plugin is installed already
		if (in_array($plugin_slug, $installed_plugins)) {
				$installed = true;
				$activate = !is_plugin_active($plugin);
		}
		// Install this thing!
		if (!$installed) {
			// Suppress feedback
			ob_start();
	
			try {
				$plugin_information = plugins_api('plugin_information', array(
						'slug' => $plugin_slug,
						'fields' => array(
								'short_description' => false,
								'sections' => false,
								'requires' => false,
								'rating' => false,
								'ratings' => false,
								'downloaded' => false,
								'last_updated' => false,
								'added' => false,
								'tags' => false,
								'homepage' => false,
								'donate_link' => false,
								'author_profile' => false,
								'author' => false,
						),
				));

				if (is_wp_error($plugin_information)) {
					throw new Exception($plugin_information->get_error_message());
				}

				$package = $plugin_information->download_link;
				$download = $upgrader->download_package($package);

				if (is_wp_error($download)) {
					throw new Exception($download->get_error_message());
				}

				$working_dir = $upgrader->unpack_package($download, true);

				if (is_wp_error($working_dir)) {
					throw new Exception($working_dir->get_error_message());
				}

				$result = $upgrader->install_package(array(
						'source' => $working_dir,
						'destination' => WP_PLUGIN_DIR,
						'clear_destination' => false,
						'abort_if_destination_exists' => false,
						'clear_working' => true,
						'hook_extra' => array(
								'type' => 'plugin',
								'action' => 'install',
						),
				));

				if (is_wp_error($result)) {
					throw new Exception($result->get_error_message());
				}

				$activate = true;
			} catch (Exception $e) {
				printf(
						__('%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'wc-multivendor-marketplace'), 'WC Frontend Manager', $e->getMessage(), esc_url(admin_url('plugin-install.php?tab=search&s=wc-frontend-manager'))
				);
				exit();
			}

			// Discard feedback
			ob_end_clean();
		}

		wp_clean_plugins_cache();
		// Activate this thing
		if ($activate) {
				try {
						$result = activate_plugin($plugin);

						if (is_wp_error($result)) {
								throw new Exception($result->get_error_message());
						}
				} catch (Exception $e) {
						printf(
							__('%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'wc-multivendor-marketplace'), 'WC Frontend Manager', admin_url('plugins.php')
						);
						exit();
				}
		}
		wp_safe_redirect(admin_url('index.php?page=wcfmmg-migrator'));
	}

	/**
	 * Introduction step.
	 */
	public function wcfmmg_migration_introduction() {
		?>
		<h1><?php printf( __("Welcome to %s migration!", 'wc-multivendor-marketplace-migration'), 'WCFM Marketplace' ); ?></h1>
		
		<?php if( WCFMmg_Dependencies::woocommerce_plugin_active_check() && WCFMmg_Dependencies::wcfm_plugin_active_check() ) { ?>
			<p><?php printf( __('Before we start please read this carefully -', 'wc-multivendor-marketplace-migration') ); ?></p>
			<ul>
				<li><?php esc_html_e("You have already kept backup of your site's Database.", 'wc-multivendor-marketplace-migration'); ?></li>
				<li><?php esc_html_e("Disabled caching and optimize plugins.", 'wc-multivendor-marketplace-migration'); ?></li>
				<li><?php esc_html_e("Your previous multi-vendor plugin installed and activated.", 'wc-multivendor-marketplace-migration'); ?></li>
				<li><?php esc_html_e("WCFM (WC Frontend Manager) installed and activated.", 'wc-multivendor-marketplace-migration'); ?></li>
				<li><?php esc_html_e("You are using a non-interrupted internet connection.", 'wc-multivendor-marketplace-migration'); ?></li>
			</ul>
			<p style="color: #ff9900;  font-weight: 400;">*<?php printf( __("This migration tool only for migrating vendors, marketplace setting will not migrated using this. You have to do that manually.", 'wc-multivendor-marketplace-migration') ); ?></p>
			<p style="color: #ff4500;  font-weight: 400;">**<?php printf( __("Vendor's shipping setting will not migrate as WCFM Marketplace vendnor shipping totally different than your previous multi-vendor plugin! %sRead this to know more%s", 'wc-multivendor-marketplace-migration'), '<a href="https://wclovers.com/knowledgebase/wcfm-marketplace-store-shipping/" target="_blank">', '</a>' ); ?></p>
			<p style="color: #ff4500;  font-weight: 400;">**<?php printf( __("Deleted orders or deleted product's orders will not be migrated!", 'wc-multivendor-marketplace-migration') ); ?></p>
			<p style="color: #bb0000;  font-weight: 600;">***<?php printf( __('Never close this browser tab when migration is running.', 'wc-multivendor-marketplace-migration') ); ?></p>
			<p><?php printf( __('We are ready, are you?', 'wc-multivendor-marketplace-migration') ); ?></p>
			<p class="wc-setup-actions step">
				<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button-primary button button-large button-next"><?php esc_html_e("Let's go!", 'wc-frontend-manager'); ?></a>
				<a href="<?php echo esc_url(get_wcfm_url()); ?>" class="button button-large"><?php esc_html_e('Not right now', 'wc-frontend-manager'); ?></a>
			</p>
		<?php
		} else {
			?>
			<p style="color: #bb0000; font-weight: 600;"><?php printf( __('WooCommerce or WC Frontend Manager (WCFM) is missing, please activate and back here!.', 'wc-multivendor-marketplace-migration') ); ?></p>
			<?php
		}
	}

	/**
	 * Store stat content
	 */
	public function wcfmmg_migration_store_stat() {
		global $WCFM, $WCFMmg;
		
		$is_marketplace = wcfm_is_marketplace();
		$multivendor_plugins = array( 'dokan' => 'Dokan Mutivendor', 'wcmarketplace' => 'WC Marketplace', 'wcvendors' => 'WC Vendors', 'wcpvendors' => 'WC Product Vendors' );
		
		$wcfm_all_vendors = $WCFM->wcfm_vendor_support->wcfm_get_vendor_list( true, '', '', '' );
		unset($wcfm_all_vendors[0]);
		update_option( '_wcfmmg_vendor_count', count($wcfm_all_vendors) );
		update_option( '_wcfmmg_migrated_vendor_count', 0 );
		
		$count_products = array();
		$current_user_id = 0;
		$count_products['publish'] = wcfm_get_user_posts_count( $current_user_id, 'product', 'publish' );
		$count_products['pending'] = wcfm_get_user_posts_count( $current_user_id, 'product', 'pending' );
		$count_products['draft']   = wcfm_get_user_posts_count( $current_user_id, 'product', 'draft' );
		$count_products['private'] = wcfm_get_user_posts_count( $current_user_id, 'product', 'private' );
		$count_products['any'] = 0;
		foreach( $count_products as $count_product ) {
			$count_products['any']  += $count_product;
		}
		
		?>
		<h1><?php printf( __("Multi-vendor Store Stat!", 'wc-multivendor-marketplace-migration') ); ?></h1>
		<?php if( WCFMmg_Dependencies::woocommerce_plugin_active_check() && WCFMmg_Dependencies::wcfm_plugin_active_check() ) { ?>
			<ul>
				<li><?php printf( __("Multi-vendor Plugin - %s", 'wc-multivendor-marketplace-migration'), $multivendor_plugins[$is_marketplace] ); ?></li>
				<li><?php printf( __("Vendors - %s", 'wc-multivendor-marketplace-migration'), count($wcfm_all_vendors) ); ?></li>
				<li><?php printf( __("Products - %s", 'wc-multivendor-marketplace-migration'), $count_products['any'] ); ?></li>
			</ul>
			<?php if( count($wcfm_all_vendors) > 0 ) { ?>
				<p><?php printf( __('You are about to start migration!', 'wc-multivendor-marketplace-migration') ); ?></p>
				<p style="color: #bb0000;  font-weight: 600;">***<?php printf( __('Never close this browser tab when migration is running.', 'wc-multivendor-marketplace-migration') ); ?></p>
				<p class="wc-setup-actions step">
					<a href="<?php echo esc_url($this->get_next_step_link()); ?>" class="button-primary button button-large button-next"><?php esc_html_e("Start Migration", 'wc-multivendor-marketplace-migration'); ?></a>
					<a href="<?php echo esc_url(get_wcfm_url()); ?>" class="button button-large"><?php esc_html_e('Not right now', 'wc-frontend-manager'); ?></a>
				</p>
			<?php } else { ?>
				<p style="color: #ff4500;  font-weight: 400;"><?php printf( __('You have no vendor, no migration require!', 'wc-multivendor-marketplace-migration') ); ?></p>
				<p class="wc-setup-actions step">
					<a href="<?php echo esc_url(get_wcfm_url()); ?>" class="button button-large"><?php esc_html_e('Get me out from this', 'wc-frontend-manager'); ?></a>
				</p>
			<?php } ?>
			<?php
		} else {
			?>
			<p style="color: #bb0000; font-weight: 600;"><?php printf( __('WooCommerce or WC Frontend Manager (WCFM) is missing, please activate and back here!.', 'wc-multivendor-marketplace-migration') ); ?></p>
			<?php
		}
	}
	
	/**
	 * Store Processing content
	 */
	public function wcfmmg_migration_store_process() {
		global $WCFM, $WCFMmg;
		
		$total_vendors = absint( get_option( '_wcfmmg_vendor_count', 0 ) );
		$processed_vendors = absint( get_option( '_wcfmmg_migrated_vendor_count', 0 ) );
		$migration_step = get_option( '_wcfmmg_migration_step', '' );
		
		if( !$total_vendors || ( $total_vendors == $processed_vendors ) ) {
			wp_safe_redirect($this->get_next_step_link());
		} else {
			if( wcfm_is_marketplace() != 'wcpvendors' ) {
				$wcfm_get_vendors = $WCFM->wcfm_vendor_support->wcfm_get_vendor_list( true, 0, 1 );
			} else {
				$wcfm_get_vendors = $WCFM->wcfm_vendor_support->wcfm_get_vendor_list( true, $processed_vendors, 1 );
			}
			unset( $wcfm_get_vendors[0] );
			
			if( !empty( $wcfm_get_vendors ) ) {
				foreach( $wcfm_get_vendors as $vendor_id => $wcfm_vendor ) {
					?>
					<h1><?php printf( __("Migrating Store: #%s", 'wc-multivendor-marketplace-migration'), $vendor_id. ' ' . $wcfm_vendor ); ?></h1>
					<script>
					  jQuery(document).ready(function($) {
					  		$('.wc-setup-content').block({
									message: null,
									overlayCSS: {
										background: '#fff',
										opacity: 0.6
									}
								});
					  });
					</script>
					<style>
					 .processing_box{padding: 0px 20px 20px 20px; border:1px solid #ccc; border-radius: 5px; margin-bottom: 20px;}
					 .processing_message{}
					 .processing_box_icon{font-size:15px;margin-right:10px;color:#111;}
					 .processing_box_status{font-size:25px;margin-left:10px;color:#00798b;}
					</style>
					<div class="processing_box processing_box_setting">
					  <?php if( !$migration_step ) { ?>
							<p class="setting_process processing_message" style=""><span class="fa fa-gear processing_box_icon"></span><?php printf( __('Store setting migrating ....', 'wc-multivendor-marketplace-migration') ); ?></p>
							<?php 
							$setting_complete = $WCFMmg->wcfm_marketplace->store_setting_migrate( $vendor_id ); 
							if( $setting_complete ) {
								$migration_step = 'setting';
								update_option( '_wcfmmg_migration_step', 'setting' );
							?>
								<style>
									.setting_process{display:none;}
								</style>
								<p class="setting_complete processing_message" style=""><span class="fa fa-gear processing_box_icon"></span><?php printf( __('Store setting migration complete', 'wc-multivendor-marketplace-migration') ); ?><span class="fa fa-check-square-o processing_box_status"></span></p>
							<?php } ?>
						<?php } else { ?>
							<p class="setting_complete processing_message" style=""><span class="fa fa-gear processing_box_icon"></span><?php printf( __('Store setting migration complete', 'wc-multivendor-marketplace-migration') ); ?><span class="fa fa-check-square-o processing_box_status"></span></p>
						<?php } ?>
					</div>
					
					<div class="processing_box processing_box_product">
					  <?php if( $migration_step == 'setting' ) { ?>
							<p class="product_process processing_message" style=""><span class="fa fa-cube processing_box_icon"></span><?php printf( __('Store product migrating ....', 'wc-multivendor-marketplace-migration') ); ?></p>
							<?php 
							$product_complete = $WCFMmg->wcfm_marketplace->store_product_migrate( $vendor_id ); 
							if( $product_complete ) {
								$migration_step = 'product';
								update_option( '_wcfmmg_migration_step', 'product' );
							?>
								<style>
									.product_process{display:none;}
								</style>
								<p class="product_complete processing_message" style=""><span class="fa fa-cube processing_box_icon"></span><?php printf( __('Store product migration complete', 'wc-multivendor-marketplace-migration') ); ?><span class="fa fa-check-square-o processing_box_status"></span></p>
							<?php } ?>
						<?php } else { ?>
							<p class="product_complete processing_message" style=""><span class="fa fa-cube processing_box_icon"></span><?php printf( __('Store product migration complete', 'wc-multivendor-marketplace-migration') ); ?><span class="fa fa-check-square-o processing_box_status"></span></p>
						<?php } ?>
					</div>
					
					<div class="processing_box processing_box_order">
					  <?php if( $migration_step == 'product' ) { ?>
							<p class="order_process processing_message" style=""><span class="fa fa-cart-plus processing_box_icon"></span><?php printf( __('Store order migrating ....', 'wc-multivendor-marketplace-migration') ); ?></p>
							<?php 
							$order_complete = $WCFMmg->wcfm_marketplace->store_order_migrate( $vendor_id ); 
							if( $order_complete ) {
								$migration_step = 'order';
								update_option( '_wcfmmg_migration_step', 'order' );
							?>
								<style>
									.order_process{display:none;}
								</style>
								<p class="order_complete processing_message" style=""><span class="fa fa-cart-plus processing_box_icon"></span><?php printf( __('Store order migration complete', 'wc-multivendor-marketplace-migration') ); ?><span class="fa fa-check-square-o processing_box_status"></span></p>
							<?php } ?>
						<?php } else { ?>
							<p class="order_complete processing_message" style=""><span class="fa fa-cart-plus processing_box_icon"></span><?php printf( __('Store order migration complete', 'wc-multivendor-marketplace-migration') ); ?><span class="fa fa-check-square-o processing_box_status"></span></p>
						<?php } ?>
					</div>
					
					<div class="processing_box processing_box_review">
					  <?php if( $migration_step == 'order' ) { ?>
							<p class="review_process processing_message" style=""><span class="fa fa-comments-o processing_box_icon"></span><?php printf( __('Store review migrating ....', 'wc-multivendor-marketplace-migration') ); ?></p>
							<?php 
							$review_complete = $WCFMmg->wcfm_marketplace->store_review_migrate( $vendor_id ); 
							if( $review_complete ) {
								$migration_step = 'review';
								update_option( '_wcfmmg_migration_step', 'review' );
							?>
								<style>
									.review_process{display:none;}
								</style>
								<p class="review_complete processing_message" style=""><span class="fa fa-comments-o processing_box_icon"></span><?php printf( __('Store review migration complete', 'wc-multivendor-marketplace-migration') ); ?><span class="fa fa-check-square-o processing_box_status"></span></p>
							<?php } ?>
						<?php } else { ?>
							<p class="review_complete processing_message" style=""><span class="fa fa-comments-o processing_box_icon"></span><?php printf( __('Store review migration complete', 'wc-multivendor-marketplace-migration') ); ?><span class="fa fa-check-square-o processing_box_status"></span></p>
						<?php } ?>
					</div>
					
					<div class="processing_box processing_box_vendor">
					  <?php if( $migration_step == 'review' ) { ?>
							<p class="vendor_process processing_message" style=""><span class="fa fa-user processing_box_icon"></span><?php printf( __('Store vendor migrating ....', 'wc-multivendor-marketplace-migration') ); ?></p>
							<?php 
							$vendor_complete = $WCFMmg->wcfm_marketplace->store_vendor_migrate( $vendor_id ); 
							if( $vendor_complete ) {
								$migration_step = 'vendor';
								update_option( '_wcfmmg_migration_step', 'vendor' );
							?>
								<style>
									.vendor_process{display:none;}
								</style>
								<p class="vendor_complete processing_message" style=""><span class="fa fa-gear processing_box_icon"></span><?php printf( __('Store vendor migration complete', 'wc-multivendor-marketplace-migration') ); ?><span class="fa fa-check-square-o processing_box_status"></span></p>
							<?php } ?>
						<?php } else { ?>
							<p class="vendor_complete processing_message" style=""><span class="fa fa-gear processing_box_icon"></span><?php printf( __('Store vendor migration complete', 'wc-multivendor-marketplace-migration') ); ?><span class="fa fa-check-square-o processing_box_status"></span></p>
						<?php } ?>
					</div>
					<?php
					$processed_vendors++;
					update_option( '_wcfmmg_migrated_vendor_count', $processed_vendors );
					update_option( '_wcfmmg_migrated_last_vendor', $vendor_id );
					update_option( '_wcfmmg_migration_step', '' );
					?>
					<script>
					setTimeout(function() {
					  window.location = window.location.href;
					}, 1000);
					</script>
					<?php
				}
			} else {
				wp_safe_redirect($this->get_next_step_link());
			}
		}
	}
	

	
	/**
	 * Ready to go content
	 */
	public function wcfmmg_migration_complete() {
		global $WCFM;
		?>
		<h1><?php esc_html_e('We are done!', 'wc-frontend-manager'); ?></h1>
		<p><?php esc_html_e("Your store is successfully migrated. It's time to experience the things more Easily and Peacefully.", 'wc-multivendor-marketplace-migration'); ?></p>
		<p style="color: #bb0000;  font-weight: 600;"><?php printf( __('Please ask your vendors to re-check their store setting, specially payment setting.', 'wc-multivendor-marketplace-migration') ); ?></p>
		<p><?php printf( __( "Please uninstall your existing multi-vendor plugin and install %sWCFM Marketplace%s.", 'wc-multivendor-marketplace-migration'), '<a target="_blank" href="https://wordpress.org/plugins/wc-multivendor-marketplace/">', '</a>' ); ?></p>
		</div>
		<div class="wc-setup-next-steps">
		  <p class="wc-setup-actions step">
			  <a class="button button-primary button-large" href="<?php echo esc_url( get_wcfm_url() ); ?>"><?php esc_html_e( "Let's go to Dashboard", 'wc-frontend-manager' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function wcfmmg_migration_footer() {
				?>
			</body>
			<?php do_action('admin_footer'); ?>
	</html>
	<?php
	}
}

new WCFMmg_Migrator();
