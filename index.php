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
$tm = new TransmitMail();
$tm->init('config/config.yml');
$tm->run();
