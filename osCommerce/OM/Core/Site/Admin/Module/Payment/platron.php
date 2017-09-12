<?php
/**
 * @author PLATRON.ru modules D.Karmichkin
 *
 * @copyright Copyright (c) 2011 JCS Platron; http://www.platron.ru/
 */

  namespace osCommerce\OM\Core\Site\Admin\Module\Payment;

  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

/**
 * The administration side of the Paypal Express Checkout payment module
 */

  class platron extends \osCommerce\OM\Core\Site\Admin\PaymentModuleAbstract {

/**
 * The administrative title of the payment module
 *
 * @var string
 * @access protected
 */

    protected $_title;

/**
 * The administrative description of the payment module
 *
 * @var string
 * @access protected
 */

    protected $_description;

/**
 * The developers name
 *
 * @var string
 * @access protected
 */

    protected $_author_name = 'Dmitriy Karmichkin';

/**
 * The developers address
 *
 * @var string
 * @access protected
 */

    protected $_author_www = 'https://paybox.kz/';

/**
 * The status of the module
 *
 * @var boolean
 * @access protected
 */

    protected $_status = false;

/**
 * Initialize module
 *
 * @access protected
 */

    protected function initialize() {
      $this->_title = OSCOM::getDef('platron_title');
      $this->_description = OSCOM::getDef('platron_description');
      $this->_status = (defined('MODULE_PAYMENT_PLATRON_STATUS') && (MODULE_PAYMENT_PLATRON_STATUS == '1') ? true : false);
      $this->_sort_order = (defined('MODULE_PAYMENT_PLATRON_SORT_ORDER') ? MODULE_PAYMENT_PLATRON_SORT_ORDER : 0);
    }

/**
 * Checks to see if the module has been installed
 *
 * @access public
 * @return boolean
 */

    public function isInstalled() {
      return defined('MODULE_PAYMENT_PLATRON_STATUS');
    }

/**
 * Installs the module
 *
 * @access public
 * @see \osCommerce\OM\Core\Site\Admin\PaymentModuleAbstract::install()
 */

    public function install() {
      parent::install();

      $OSCOM_PDO = Registry::get('PDO');
	  $Qcheck = $OSCOM_PDO->prepare("SELECT COUNT(orders_status_id) as count FROM :table_orders_status WHERE `orders_status_name` = 'Approved [Platron]'");
      $Qcheck->execute();
      $res = $Qcheck->fetch();
	  if($res['count'] == 0)
	  {
		$Qcheck = $OSCOM_PDO->prepare("SELECT max(orders_status_id) as status_id FROM :table_orders_status ");
		$Qcheck->execute();
		$res = $Qcheck->fetch();
		$status_id = (int) $res['status_id'];
		$Qcheck = $OSCOM_PDO->prepare('insert into :table_orders_status VALUES("'.++$status_id.'","1","Approved [Platron]") ');
		$Qcheck->execute();
	  }

	  $Qcheck = $OSCOM_PDO->prepare("SELECT COUNT(orders_status_id) as count FROM :table_orders_status WHERE `orders_status_name` = 'Refunded [Platron]'");
      $Qcheck->execute();
      $res = $Qcheck->fetch();
	  if($res['count'] == 0)
	  {
		$Qcheck = $OSCOM_PDO->prepare("SELECT max(orders_status_id) as status_id FROM :table_orders_status ");
		$Qcheck->execute();
		$res = $Qcheck->fetch();
		$status_id = (int) $res['status_id'];
		$Qcheck = $OSCOM_PDO->prepare('insert into :table_orders_status VALUES("'.++$status_id.'","1","Refunded [Platron]") ');
		$Qcheck->execute();
	  }

	  $Qcheck = $OSCOM_PDO->prepare("SELECT COUNT(orders_status_id) as count FROM :table_orders_status WHERE `orders_status_name` = 'Fail [Platron]'");
      $Qcheck->execute();
      $res = $Qcheck->fetch();
	  if($res['count'] == 0)
	  {
		$Qcheck = $OSCOM_PDO->prepare("SELECT max(orders_status_id) as status_id FROM :table_orders_status ");
		$Qcheck->execute();
		$res = $Qcheck->fetch();
		$status_id = (int) $res['status_id'];
		$Qcheck = $OSCOM_PDO->prepare('insert into :table_orders_status VALUES("'.++$status_id.'","1","Fail [Platron]") ');
		$Qcheck->execute();
	  }

      $data = array(array('title' => 'Enable PayBox Payment Gate',
                          'key' => 'MODULE_PAYMENT_PLATRON_STATUS',
                          'value' => '-1',
                          'description' => 'Хотите ли вы принимать платежи через PayBox?',
                          'group_id' => '6',
                          'use_function' => 'osc_cfg_use_get_boolean_value',
                          'set_function' => 'osc_cfg_set_boolean_value(array(1, -1))'),
                    array('title' => 'Идентификатор магазина в системе PayBox',
                          'key' => 'MODULE_PAYMENT_PLATRON_MERCHANT_ID',
                          'value' => '',
                          'description' => 'Идентификатор магазина в системе PayBox.',
                          'group_id' => '6'),
                    array('title' => 'Кодовое слово',
                          'key' => 'MODULE_PAYMENT_PLATRON_MERCHANT_SECRET_KEY',
                          'value' => '',
                          'description' => 'Секретный ключ магазина(см. в paybox.kz/admin).',
                          'group_id' => '6'),
		  			array('title' => 'Action URL to PayBox',
                          'key' => 'MODULE_PAYMENT_PLATRON_ACTION_URL',
                          'value' => 'https://paybox.kz/payment.php',
                          'description' => 'URL На который делать запрос, при создании транзакции',
                          'group_id' => '6'),
                    array('title' => 'Transaction lifetime',
                          'key' => 'MODULE_PAYMENT_PLATRON_TRANSACTION_LIFETIME',
                          'value' => '86400',
                          'description' => 'Время в течение которого можно оплатить товар.',
                          'group_id' => '6'),
                    array('title' => 'Check URL',
                          'key' => 'MODULE_PAYMENT_PLATRON_MERCHANT_CHECK_URL',
                          'value' => 'http://'.$_SERVER['SERVER_NAME'].'/platron_check.php',
                          'description' => 'URL на который присылать check-запрос
							  (в случае пустого занчения, берется из настроек магазина на сайте PayBox)',
                          'group_id' => '6'),
					array('title' => 'Result URL',
                          'key' => 'MODULE_PAYMENT_PLATRON_MERCHANT_RESULT_URL',
                          'value' => 'http://'.$_SERVER['SERVER_NAME'].'/platron_result.php',
                          'description' => 'URL на который присылать result-запрос
							  (в случае пустого занчения, берется из настроек магазина на сайте PayBox)',
                          'group_id' => '6'),
		  			array('title' => 'Refund URL',
                          'key' => 'MODULE_PAYMENT_PLATRON_MERCHANT_REFUND_URL',
                          'value' => 'http://'.$_SERVER['SERVER_NAME'].'/platron_refund.php',
                          'description' => 'URL на который присылать запрос при refund
							  (в случае пустого занчения, берется из настроек магазина на сайте PayBox)',
                          'group_id' => '6'),
		  		  	array('title' => 'Success URL',
                          'key' => 'MODULE_PAYMENT_PLATRON_MERCHANT_SUCCESS_URL',
                          'value' => 'http://'.$_SERVER['SERVER_NAME'].'/index.php?Checkout&Process&Success',
                          'description' => 'URL на который будет перенаправляться пользователь при удачном проведение платежа
							  (в случае пустого занчения, берется из настроек магазина на сайте PayBox)',
                          'group_id' => '6'),
					array('title' => 'Failure URL',
                          'key' => 'MODULE_PAYMENT_PLATRON_MERCHANT_FAILURE_URL',
                          'value' => 'http://'.$_SERVER['SERVER_NAME'].'/index.php?Checkout&Process&Fail',
                          'description' => 'URL на который будет перенаправляться пользователь при не удачном проведение платежа
							  (в случае пустого занчения, берется из настроек магазина на сайте PayBox)',
                          'group_id' => '6'),


					array('title' => 'Sort order of display.',
                          'key' => 'MODULE_PAYMENT_PLATRON_SORT_ORDER',
                          'value' => '0',
                          'description' => 'Sort order of display. Lowest is displayed first.',
                          'group_id' => '6'),
                    array('title' => 'Payd Order Status',
                          'key' => 'MODULE_PAYMENT_PLATRON_ORDER_PAYD_STATUS_ID',
                          'value' => '0',
                          'description' => 'Set the status of orders made with this payment module to this value on payd',
                          'group_id' => '6',
                          'use_function' => 'osc_cfg_use_get_order_status_title',
                          'set_function' => 'osc_cfg_set_order_statuses_pull_down_menu'),
		            array('title' => 'Fail Order Status',
                          'key' => 'MODULE_PAYMENT_PLATRON_ORDER_FAIL_STATUS_ID',
                          'value' => '0',
                          'description' => 'Set the status of orders made with this payment module to this value on fail',
                          'group_id' => '6',
                          'use_function' => 'osc_cfg_use_get_order_status_title',
                          'set_function' => 'osc_cfg_set_order_statuses_pull_down_menu'),
		            array('title' => 'Refunded Order Status',
                          'key' => 'MODULE_PAYMENT_PLATRON_ORDER_REFUNDED_STATUS_ID',
                          'value' => '0',
                          'description' => 'Set the status of orders made with this payment module to this value on refund',
                          'group_id' => '6',
                          'use_function' => 'osc_cfg_use_get_order_status_title',
                          'set_function' => 'osc_cfg_set_order_statuses_pull_down_menu')
                   );

      OSCOM::callDB('Admin\InsertConfigurationParameters', $data, 'Site');
    }

/**
 * Return the configuration parameter keys in an array
 *
 * @access public
 * @return array
 */

    public function getKeys() {
      return array('MODULE_PAYMENT_PLATRON_STATUS',
                   'MODULE_PAYMENT_PLATRON_MERCHANT_ID',
                   'MODULE_PAYMENT_PLATRON_MERCHANT_SECRET_KEY',
				   'MODULE_PAYMENT_PLATRON_ACTION_URL',
                   'MODULE_PAYMENT_PLATRON_TRANSACTION_LIFETIME',
				   'MODULE_PAYMENT_PLATRON_MERCHANT_CHECK_URL',
				   'MODULE_PAYMENT_PLATRON_MERCHANT_RESULT_URL',
				   'MODULE_PAYMENT_PLATRON_MERCHANT_REFUND_URL',
				   'MODULE_PAYMENT_PLATRON_MERCHANT_SUCCESS_URL',
				   'MODULE_PAYMENT_PLATRON_MERCHANT_FAILURE_URL',

                   'MODULE_PAYMENT_PLATRON_SORT_ORDER',
                   'MODULE_PAYMENT_PLATRON_ORDER_PAYD_STATUS_ID',
				   'MODULE_PAYMENT_PLATRON_ORDER_FAIL_STATUS_ID',
				   'MODULE_PAYMENT_PLATRON_ORDER_REFUNDED_STATUS_ID');
    }

/**
 * Remove the module
 *
 * @access public
 */

    public function remove(){
        parent::remove();
//        $OSCOM_PDO = Registry::get('PDO');
//        $Qcheck = $OSCOM_PDO->prepare("DELETE FROM :table_orders_status WHERE orders_status_name = 'Approved [Platron]' ");
//        $Qcheck->execute();
    }

}


?>
