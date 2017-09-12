<?php
/**
 * @author PLATRON.ru modules D.Karmichkin
 *
 * @copyright Copyright (c) 2011 JCS Platron; http://www.platron.ru/
 */
namespace osCommerce\OM\Custom;

use \SimpleXMLElement;
use osCommerce\OM\Custom\PlatronSignature;

class PlatronResponse {
	/**
	 * Генерация ответа на запросы Platron
	 * @param type $strScriptName
	 * @param array $arrRequestData
	 * @param type $strSecretKey
	 */
	static public function makeResponse($strScriptName, $arrRequestData, $strSecretKey)
	{
		$xmlResponce = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><response/>');

		$arrRequestData['pg_salt'] = self::genRandomSalt();
		foreach ($arrRequestData as $key => $value)
		{
			$xmlResponce->addChild($key, $value);
		}
		$xmlResponce->addChild('pg_sig', PlatronSignature::make($strScriptName, $arrRequestData, $strSecretKey));
		header('Content-type: text/xml');
		echo $xmlResponce->asXML();
	}

	static public function makeResponseAndExit($strScriptName, $arrRequestData, $strSecretKey)
	{

		self::makeResponse($strScriptName, $arrRequestData, $strSecretKey);
		exit();
	}

	static public function makeOkResponseAndExit($strScriptName, $strSecretKey)
	{
		$arrRequestData = array(
			'pg_status' => 'ok'
		);
		self::makeResponseAndExit($strScriptName, $arrRequestData, $strSecretKey);
	}

	static public function makeErrorResponseAndExit($strScriptName, $strErrorMessage, $strSecretKey)
	{
		$arrRequestData = array(
			'pg_status' => 'error',
			'pg_error_description' => $strErrorMessage
		);
		self::makeResponseAndExit($strScriptName, $arrRequestData, $strSecretKey);
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
}
?>