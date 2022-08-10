<?php
/**
 * TransmitMail
 *
 * @package   TransmitMail
 * @license   MIT License
 * @copyright TAGAWA Takao, dounokouno@gmail.com
 * @link      https://github.com/dounokouno/TransmitMail
 */

chdir(__DIR__);
require_once __DIR__ . '/lib/TransmitMail.php';
$tm = new TransmitMail();
$tm->init(__DIR__ . '/config/config.test.yml');
$tm->run();
