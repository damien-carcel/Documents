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
 * @package     app.View.Documents
 * @license     http://www.gnu.org/licenses/gpl.html
 */

$path = $this->Html->link(
	'Retour',
	array(
		'controller' => 'documents',
		'action' => 'index',
		$folderToRename['Dir']['parent']
	)
);
?>
<div class="back-button">
	<div class="link-button"><?php echo $path ?></div>
</div>

<?php
echo $this->Form->create('Dir'), "\n";
echo $this->Form->input(
	'name',
	array(
		'label' => '<h1>Nouveau nom</h1>',
		'default' => $folderToRename['Dir']['name']
	)
), "\n";
echo $this->Form->end('Renommer'), "\n";
