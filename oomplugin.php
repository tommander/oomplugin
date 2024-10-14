<?php
/**
 * Undocumented file
 *
 * @package OrderOfMass
 *
 * Plugin Name:       OoM Plugin
 * Plugin URI:        https://mass.tommander.cz/
 * Description:       Order of Mass Plugin
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Tommander
 * Author URI:        https://tommander.cz/
 * Text Domain:       order-of-mass
 * License:           MIT
 * License URI:       https://spdx.org/licenses/MIT.html
 */

namespace TMD\OrderOfMass;

require_once __DIR__ . '/vendor/autoload.php';

$oom_plugin_main = Plugin\Main::new();
$oom_plugin_main->run();
