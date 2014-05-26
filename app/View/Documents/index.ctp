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

App::import('Lib', 'HumanReadable');

if ($currentName !== 'Racine') {
	$path = '';
	foreach ($previousFolders as $previousFolder) {
		$path .= $this->Html->link(
			$previousFolder['Folder']['name'],
			array(
				'controller' => 'documents',
				'action' => 'index',
				$previousFolder['Folder']['id']
			)
		);
		$path .= ' → ';
	}

	$path .= $currentName;
} else {
	$path = '-';
}

if ($this->Session->read('Auth.User.role') === 'admin' ||
	$this->Session->read('Auth.User.role') === 'superadmin') {
	$administration = true;
} else {
	$administration = false;
}
?>
<div class="folders">
	<h2><?php echo $path ?></h2>
	<h1><?php echo $currentName; ?></h1>
</div>

<div id="container">
	<aside>
		<div class="link-button">
			<?php
			echo $this->Html->link(
				'Nouveau Dossier',
				array(
					'controller' => 'documents',
					'action' => 'addFolder',
					$currentID
				)
			), "\n";
			?>
		</div>
		<div class="link-button">
			<?php
			echo $this->Html->link(
				'Ajouter un fichier',
				array(
					'controller' => 'documents',
					'action' => 'addFile',
					$currentID
				)
			);
			?>
		</div>
	</aside>
	<article>
		<table>
			<tr>
				<th>
					Nom
				</th>
				<th>
					Taille
				</th>
				<th>
					Type
				</th>
				<th>
					Dernière modification
				</th>
				<?php
				if ($administration) {
				?>
				<th>
					Actions
				</th>
				<?php
				}
				?>
			</tr>

			<?php
			foreach ($folders as $folder) {
				if (!empty($folder['Folder']['name'])) {
					?>
					<tr>
						<td>
							<?php
							echo $this->Html->link(
								$folder['Folder']['name'],
								array(
									'controller' => 'documents',
									'action' => 'index',
									$folder['Folder']['id']
								)
							);
							?>
						</td>
						<td>
							<?php
							echo 'Contient ', $folder['Folder']['content'];
							if ($folder['Folder']['content'] == 1) {
								echo ' élément';
							} else {
								echo ' éléments';
							}
							?>
						</td>
						<td>
							Dossier
						</td>
						<td>
							<?php echo $this->Time->format(
								'\l\e d/m/Y \à H \h i',
								$folder['Folder']['modified']
							); ?>
						</td>
						<?php
						if ($administration) {
							echo '<td>';
							echo $this->Html->link(
								'Renommer',
								array(
									'action' => 'renameFolder',
									$folder['Folder']['id']
								)
							);
							echo ' | ';
							echo $this->Html->link(
								'Déplacer',
								array(
									'action' => 'moveFolder',
									$folder['Folder']['id']
								)
							);
							echo ' | ';
							echo $this->Form->postLink(
								'Supprimer',
								array(
									'action' => 'deleteFolder',
									$folder['Folder']['id']
								),
								array(
									'confirm' => 'Êtes-vous sûr ? Tous les dossiers et documents contenus seront également éffacés.'
								)
							);
							echo '</td>';
						}
						?>
					</tr>
				<?php
				}
			}

			foreach ($documents as $document) {
				if (!empty($document['Document']['name'])) {
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					?>
					<tr>
						<td>
							<?php
								echo $this->Html->link(
									$document['Document']['name'],
									array(
										'controller' => 'documents',
										'action' => 'shareLink',
										$document['Document']['id']
									)
								);
							?>
						</td>
						<td>
							<?php echo HumanReadable::size(
								filesize($document['Document']['path'])
							); ?>
						</td>
						<td>
							<?php echo finfo_file(
								$finfo,
								$document['Document']['path']
							); ?>
						</td>
						<td>
							<?php echo $this->Time->format(
								'\l\e d/m/Y \à H \h i',
								$document['Document']['modified']
							); ?>
						</td>
						<?php
						if ($administration) {
							echo '<td>';
							echo $this->Html->link(
								'Renommer',
								array(
									'action' => 'renameFile',
									$document['Document']['id']
								)
							);
							echo ' | ';
							echo $this->Html->link(
								'Déplacer',
								array(
									'action' => 'moveFile',
									$document['Document']['id']
								)
							);
							echo ' | ';
							echo $this->Form->postLink(
								'Supprimer',
								array(
									'action' => 'deleteFile',
									$document['Document']['id']
								),
								array(
									'confirm' => 'Êtes-vous sûr ?'
								)
							);
							echo '</td>';
						}
						?>
					</tr>
				<?php
				}
			}
			?>
		</table>
	</article>
</div>
