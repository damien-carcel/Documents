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
 * @package     app.View.Layouts
 * @license     http://www.gnu.org/licenses/gpl.html
 */

$appName = 'Documents';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<?php echo $this->Html->charset(), "\n"; ?>
	<title>
		<?php
		echo $appName;
		if ($title_for_layout != ' ') {
			echo ' - ';
		}
		echo $title_for_layout;
		?>
	</title>
	<?php
	echo $this->Html->meta('icon'), "\n";
	echo $this->Html->meta(array('content' => 'IE=edge,chrome=1', 'http-equiv' => 'X-UA-Compatible')), "\n";
	echo $this->Html->meta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0')), "\n";

	// load cake.generic.css only in debug mode for error messages
	if (Configure::read('debug') > 0) {
		echo $this->Html->css('cake.generic'), "\n";
	}
	echo $this->Html->css('documents'), "\n";

	echo '<!--[if lt IE 9]>', "\n";
	echo $this->Html->script('http://html5shiv.googlecode.com/svn/trunk/html5.js'), "\n";
	echo '<![endif]-->', "\n";

	echo $this->fetch('meta'), "\n";
	echo $this->fetch('css'), "\n";
	echo $this->fetch('script'), "\n";
	?>
</head>
<body>
	<header>
		<?php echo $this->Html->image('icon.png', array('alt' => 'Logo', 'id' => 'logo')), "\n"; ?>
		<h1>Documents</h1>
	</header>
	<section>

		<?php echo $this->Session->flash(), "\n"; ?>

		<?php echo $this->fetch('content'), "\n"; ?>
	</section>
	<footer>
		<?php
		$connectedUser = $this->Session->read('Auth.User.username');
		if (!empty($connectedUser)) {
			echo __('Utilisateur connecté : '), $this->Session->read('Auth.User.username'), ' - ';
			echo $this->Html->link(
				__('[Déconnexion]'),
				array(
					'controller' => 'users',
					'action' => 'logout'
				)
			);
			echo ' - ';
		}
		echo date('d/m/Y');
		echo ' ‑ ';
		echo date('H:m');
		?>
	</footer>
</body>
</html>
