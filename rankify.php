<?php
/**
 * Rankify
 *
 * @copyright Copyright (C) 2021-2023, Projects Engine - contact@projectsengine.com
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Rankify
 * Version:     1.0.1
 * Plugin URI:  https://github.com/plugin/rankify
 * Description: This user-friendly plugin allows visitors to express their opinions through a straightforward voting system.
 * Author:      Projects Engine
 * Author URI:  https://www.projectsengine.com/user/dragipostolovski
 * Text Domain: rankify
 * Domain Path: /languages/
 * License:     GPL v3
 * Requires at least: 6.4
 * Requires PHP: 8.0
 *
 * Rankify requires at least: 6.4
 * Rankify tested up to: 6.4.1
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The core theme class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . '/core/Rankify.php';
new projectsengine\Rankify;