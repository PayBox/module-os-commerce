<?php
/**
 * @author PLATRON.ru modules D.Karmichkin
 *
 * @copyright Copyright (c) 2011 JCS Platron; http://www.platron.ru/
 */

use osCommerce\OM\Core\Autoloader;
use osCommerce\OM\Core\OSCOM;
use osCommerce\OM\Custom\PlatronSignature;
use osCommerce\OM\Custom\PlatronResponse;
use osCommerce\OM\Core\Site\Shop\Order;

define('OSCOM_TIMESTAMP_START', microtime());
error_reporting(E_ALL | E_STRICT);
define('OSCOM_PUBLIC_BASE_DIRECTORY', __DIR__ . '/');

require('osCommerce/OM/Core/Autoloader.php');
$OSCOM_Autoloader = new Autoloader('osCommerce\OM');
$OSCOM_Autoloader->register();

OSCOM::initialize();

$strScriptName = PlatronSignature::getOurScriptName();

if(isset($_POST) && !empty($_POST))
  $arrRequest = $_POST;
elseif(isset($_GET) && !empty($_GET))
  $arrRequest = $_GET;
else
	PlatronResponse::makeErrorResponseAndExit($strScriptName, "Пустой запрос", MODULE_PAYMENT_PLATRON_MERCHANT_SECRET_KEY);

if ( !PlatronSignature::check($arrRequest['pg_sig'], $strScriptName, $arrRequest, MODULE_PAYMENT_PLATRON_MERCHANT_SECRET_KEY) )
	PlatronResponse::makeErrorResponseAndExit($strScriptName, "Некоректная сигнатура", MODULE_PAYMENT_PLATRON_MERCHANT_SECRET_KEY);

if(!Order::exists((int)$arrRequest['pg_order_id']))
	PlatronResponse::makeErrorResponseAndExit($strScriptName, "Нет такой покупки", MODULE_PAYMENT_PLATRON_MERCHANT_SECRET_KEY);

$objOrder = new Order((int)$arrRequest['pg_order_id']);
if(array_search($objOrder->info['orders_status'], array('Pending', 'Processing')  === false) )
	PlatronResponse::makeErrorResponseAndExit($strScriptName, "Неправельный статус транзакции", MODULE_PAYMENT_PLATRON_MERCHANT_SECRET_KEY);

Order::process((int)$arrRequest['pg_order_id'], 2);

PlatronResponse::makeOkResponseAndExit($strScriptName, MODULE_PAYMENT_PLATRON_MERCHANT_SECRET_KEY);
?>
