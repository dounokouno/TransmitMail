<?php
/**
 * TransmitMail
 *
 * @package   TransmitMail
 * @license   MIT License
 * @copyright TAGAWA Takao, dounokouno@gmail.com
 * @link      https://github.com/dounokouno/TransmitMail
 */

require_once 'lib/TransmitMail.php';
require_once 'lib/exTransmitMail.php';
$tm = new exTransmitMail('config/config.yml');
$tm->run();
