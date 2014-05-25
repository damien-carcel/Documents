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
	array('controller' => 'documents', 'action' => 'index', $previousId)
);
?>
<div class="back-button">
	<div class="link-button"><?php echo $path ?></div>
</div>

<h1>Fichier à ajouter</h1>
<?php
echo $this->Form->create('Document', array('type' => 'file')), "\n";
echo $this->Form->input('', array('name' => 'MAX_FILE_SIZE', 'type' => 'hidden', 'value' => 100000000)), "\n";
echo $this->Form->file('name'), "\n";
echo $this->Form->end('Envoyer'), "\n";
