<?php
/**
 *
 * This file is part of Documents.
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
 *
 * @copyright   Copyright (C) Damien Carcel (https://github.com/damien-carcel)
 * @link        https://github.com/damien-carcel/Documents
 * @package     app.Lib
 * @license     http://www.gnu.org/licenses/gpl.html
 */

/**
 * A set of static functions to make data easily readable.
 *
 * @author Damien Carcel (https://github.com/damien-carcel)
 */
class HumanReadable {

/**
 * Return a human readable data size.
 *
 * @param int $size A data size to make human readable, given in bytes.
 * @return string
 */
	public static function size($size) {
		$base = 1024;

		$units = explode(' ', 'octets ko Mo Go To Po');
		for ($i = 0; $size > $base; $i++) {
			$size /= $base;
		}

		return round($size, 1) . ' ' . $units[$i];
	}
}
