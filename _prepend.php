<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 *
 * Use this as the bootstrap file for all php files
 */

$sitePath = dirname(__FILE__);
/** @var \Composer\Autoload\ClassLoader $composer */
$composer = include($sitePath . '/vendor/autoload.php');

$config = \App\Config::create($sitePath);
include($config->getSrcPath() . '/config/application.php');
$config->set('composer', $composer);

include_once $sitePath.'/src/App/Bootstrap.php';