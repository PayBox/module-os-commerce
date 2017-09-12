<?php
/**
 * @author PLATRON.ru modules D.Karmichkin
 *
 * @copyright Copyright (c) 2011 JCS Platron; http://www.platron.ru/
 */
namespace osCommerce\OM\Core\Site\Shop\Module\Payment;

use osCommerce\OM\Core\HttpRequest;
use osCommerce\OM\Core\Mail;
use osCommerce\OM\Core\OSCOM;
use osCommerce\OM\Core\Registry;
use osCommerce\OM\Core\Site\Shop\Order;
use osCommerce\OM\Core\Site\Shop\Shipping;
use osCommerce\OM\Custom\PlatronSignature;


class platron extends \osCommerce\OM\Core\Site\Shop\PaymentModuleAbstract {
	protected function initialize() {
		  $OSCOM_PDO = Registry::get('PDO');
		  $OSCOM_ShoppingCart = Registry::get('ShoppingCart');

		  $this->_api_version = '3.0-2';

		  $this->_title = OSCOM::getDef('platron_title');
		  $this->_method_title = OSCOM::getDef('platron_method_title');
		  $this->_status = (MODULE_PAYMENT_PLATRON_STATUS == '1') ? true : false;
		  $this->_sort_order = MODULE_PAYMENT_PLATRON_SORT_ORDER;

		  if ( $this->_status === true ) {
				if ( (int)MODULE_PAYMENT_PLATRON_ORDER_STATUS_ID > 0 ) {
					$this->order_status = MODULE_PAYMENT_PLATRON_ORDER_STATUS_ID;
				}
		  }
	}

	public function getProcessButton(){
		$confirmation = $this->_confirmation();
		$ret = '
		<div class="moduleBox" width="100%" style="text-align: left;">
			<h6>'. OSCOM::getDef('order_payment_information_title').'</h6>
			<div class="content">
				<table border="0" cellspacing="0" cellpadding="2">
				';
					for ( $i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++ ) {
						$ret .='
						  <tr>
							<td width="10">&nbsp;</td>
							<td>'.$confirmation["fields"][$i]["title"].'</td>
							<td width="10">&nbsp;</td>
							<td>'.$confirmation['fields'][$i]['field'].'</td>
						  </tr>
						  ';
					}
		$ret .= '
				</table>
			</div>
		</div>
		';

		return $ret;
	}

	public function preConfirmationCheck(){
		return false;
	}

	public function _confirmation() {
		$OSCOM_ShoppingCart = Registry::get('ShoppingCart');
		$OSCOM_Customer = Registry::get('Customer');

		$confirmation = array();
		$confirmation['fields'][] = array('title' => 'Пожалуста введите телефон в формате 7**********', 'field' => '<input type="text" name="cc_user_phone" value="'.$OSCOM_ShoppingCart->_billing_address['telephone'].'">');
		$confirmation['fields'][] = array('title' => 'Пожалуста введите свой электронный адрес', 'field' => '<input type="text" name="cc_contact_email" value="'.$OSCOM_Customer->getEmailAddress().'">');
	//      $confirmation['fields'][] = array('title' => 'Credit Card Owner:', 'field' => '<input type="text" name="cc_owner" value="'.$OSCOM_ShoppingCart->_billing_address['firstname'].' '.$OSCOM_ShoppingCart->_billing_address['lastname'].'" />');
	//      $confirmation['fields'][] = array('title' => 'Credit Card Number:', 'field' => '<input type="text" name="cc_number_nh-dns" value="" />');
	//      $confirmation['fields'][] = array('title' => 'Credit Card Expiry Date:', 'field' => $expires_month.'&nbsp;'.$expires_year);
	//      $confirmation['fields'][] = array('title' => 'Card Verification Value (CVV2):', 'field' => '<input type="text" name="cc_cvc_nh-dns" value="" size="5" />');
		return $confirmation;
	}

	/**
	 * Генерация случайной соли
	 *
	 * @param int $length длинна пароля
	 * @return string
	 */
	private static function genRandomSalt($length = 8)
	{
		$vowels="ypesdoaiufgh";
		$consonants="qxcvtjzbklwrnm";
		mt_srand((double) microtime() * 1000000);

		$pass='';
		for($i=0;$i<$length;$i++)
		{
			if ($i%2==0)
				$pass.=$consonants[mt_rand()%20];
			else
				$pass.=$vowels[mt_rand()%6];
		}

		return $pass;
	}

	public function process(){

		if(isset($_POST['pg_sig']) && !empty($_POST['pg_sig']))
			$arrRequest = $_POST;
		elseif(isset($_GET['pg_sig']) && !empty($_GET['pg_sig']))
			$arrRequest = $_GET;
		else
			$arrRequest = null;

		if($arrRequest != null)
		{
			if ( !PlatronSignature::check($arrRequest['pg_sig'], PlatronSignature::getOurScriptName(), $arrRequest, MODULE_PAYMENT_PLATRON_MERCHANT_SECRET_KEY) )
				exit();

			if(isset($_GET['Success']))
				return true;
			elseif($_GET['Fail'])
				return false;
			else
				echo "PS вернула неправельные данные";
			exit();
		}

	  $OSCOM_ShoppingCart = Registry::get('ShoppingCart');
	  $OSCOM_Currencies = Registry::get('Currencies');

	  $currency_arr = $OSCOM_Currencies->getData();
	  $currency_arr = $currency_arr[$OSCOM_Currencies->getCode()];
	  /*
	   * Собираем все параметры джля отправки
	   */
	  $this->_order_id = Order::insert();
	  $description = '';
	  $products = $OSCOM_ShoppingCart->getProducts();
	  foreach($products as $v)
	  {
		$description .= $v['name'].';';
	  }
	  $description = substr($description,0,-1);
	  $amount = (float) $OSCOM_ShoppingCart->getTotal();

	  $arrReq = array(
		  'pg_salt' => self::genRandomSalt(),
		  'pg_merchant_id' => MODULE_PAYMENT_PLATRON_MERCHANT_ID,
		  'pg_order_id' => $this->_order_id,
		  'pg_amount' => substr(round($amount* $currency_arr['value'],$currency_arr['decimal_places']), 0, 10),
		  'pg_currency' => substr($OSCOM_Currencies->getCode(), 0, 3),
		  'pg_lifetime' => MODULE_PAYMENT_PLATRON_TRANSACTION_LIFETIME,
		  'pg_user_ip'	=> $_SERVER['REMOTE_ADDR'],
		  'pg_description' => $description,
		  'pg_user_phone' => $_POST['cc_user_phone'],
		  'pg_user_contact_email' => $_POST['cc_contact_email'],
		  'pg_user_ip' => $_SERVER['REMOTE_ADDR'],
	  );

	  if(defined('MODULE_PAYMENT_PLATRON_MERCHANT_CHECK_URL') && MODULE_PAYMENT_PLATRON_MERCHANT_CHECK_URL != "")
	  {
		 $arrReq['pg_check_url'] = MODULE_PAYMENT_PLATRON_MERCHANT_CHECK_URL;
		 $arrReq['pg_request_method'] = "POST";
	  }
	  if(defined('MODULE_PAYMENT_PLATRON_MERCHANT_RESULT_URL') && MODULE_PAYMENT_PLATRON_MERCHANT_RESULT_URL != "")
	  {
		 $arrReq['pg_result_url'] = MODULE_PAYMENT_PLATRON_MERCHANT_RESULT_URL;
		 $arrReq['pg_request_method'] = "POST";
	  }
	  if(defined('MODULE_PAYMENT_PLATRON_MERCHANT_REFUND_URL') && MODULE_PAYMENT_PLATRON_MERCHANT_REFUND_URL != "")
	  {
		 $arrReq['pg_refund_url'] = MODULE_PAYMENT_PLATRON_MERCHANT_REFUND_URL;
		 $arrReq['pg_request_method'] = "POST";
	  }
	  if(defined('MODULE_PAYMENT_PLATRON_MERCHANT_SUCCESS_URL') && MODULE_PAYMENT_PLATRON_MERCHANT_SUCCESS_URL != "")
	  {
		 $arrReq['pg_success_url'] = MODULE_PAYMENT_PLATRON_MERCHANT_SUCCESS_URL;
		 $arrReq['pg_success_url_method'] = "AUTOPOST";
	  }
	  if(defined('MODULE_PAYMENT_PLATRON_MERCHANT_FAILURE_URL') && MODULE_PAYMENT_PLATRON_MERCHANT_FAILURE_URL != "")
	  {
		 $arrReq['pg_failure_url'] = MODULE_PAYMENT_PLATRON_MERCHANT_FAILURE_URL;
		 $arrReq['pg_success_url_method'] = "AUTOPOST";
	  }

	  $arrParsedUrl = parse_url(MODULE_PAYMENT_PLATRON_ACTION_URL);
	  if(!isset($arrParsedUrl["scheme"]) || empty($arrParsedUrl["scheme"]))
	  {
		  $arrParsedUrl = parse_url("http://".MODULE_PAYMENT_PLATRON_ACTION_URL);
	  }
	  if(!isset($arrParsedUrl["scheme"]) || empty($arrParsedUrl["scheme"]) ||
		!isset($arrParsedUrl["host"]) || empty($arrParsedUrl["host"]) ||
		!isset($arrParsedUrl["path"]) || empty($arrParsedUrl["path"]))
	  {
		$this->showerror("Некоректно задан Action_URL в настройках платежной системы");
		exit;
	  }

	  if(substr($arrParsedUrl['path'], 0, 1) == "/")
		  $strScriptName = substr($arrParsedUrl['path'], 1);
	  else
		  $strScriptName = $arrParsedUrl['path'];

	  $strActionUrl = $arrParsedUrl["scheme"]."://".$arrParsedUrl["host"].$arrParsedUrl["path"];

	  $arrReq['cms_payment_module'] = 'OS_COMMERCE';
	  $arrReq['pg_sig'] = PlatronSignature::make($strScriptName, $arrReq, MODULE_PAYMENT_PLATRON_MERCHANT_SECRET_KEY);
	  Order::process($this->_order_id, 1);
	  $urlToRedirect = $strActionUrl."?".http_build_query($arrReq);
	  OSCOM::redirect( $urlToRedirect );
	  exit();
	}

	private function showerror($err){
		OSCOM::redirect(OSCOM::getLink(null, 'Cart', 'error_message=' . urlencode(stripslashes($err)), 'SSL'));
	}

}
?>
