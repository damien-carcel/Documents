<?php
/**
 *
 * This file is part of CakeDocuments.
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
 * @link        https://github.com/damien-carcel/CakeDocuments
 * @package     app.View.Documents
 * @license     http://www.gnu.org/licenses/gpl.html
 */
?>

<div class="users form">
	<?php echo $this->Session->flash('auth'); ?>
	<?php echo $this->Form->create('User'); ?>
	<fieldset>
		<legend>
			<?php
			echo __('Veuillez entrer un identifiant et un mot de passe');
			?>
		</legend>
		<?php echo $this->Form->input(
			'username',
			array('label' => __('Utilisateur'))
		);
		echo $this->Form->input(
			'password',
			array('label' => __('Mot de passe'))
		);
		?>
	</fieldset>
	<?php
	echo $this->Form->submit(__('Connexion'));
	?>
	<div class="link-button">
		<?php
		echo $this->Html->link(
			__('Nouvel utilisateur'),
			array(
				'controller' => 'users',
				'action' => 'add'
			)
		);
		?>
	</div>
	<?php
	echo $this->Form->end();
	?>
</div>
