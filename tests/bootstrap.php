<?php
/**
 * PHPUnit bootstrap.
 *
 * @package    TransmitMail
 * @subpackage PHPUnit with Selenium 2
 * @author     TAGAWA Takao
 * @license    MIT License
 * @copyright  TransmitMail Development Team
 * @link       https://github.com/dounokouno/TransmitMail
 */

mb_language('ja');
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

require_once('conf/config.php');
require_once('webdriver/TransmitMailFunctionalTest.php');
