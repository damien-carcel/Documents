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
 * @package     app.Controller
 * @license     http://www.gnu.org/licenses/gpl.html
 */

App::uses('CakeTime', 'Utility');
/**
 * Documents controller
 *
 * This is the main controller for the “Documents” application.
 *
 * @author Damien Carcel (https://github.com/damien-carcel)
 */
class DocumentsController extends AppController {

	public $uses = array('Document', 'Dir');

	public $helpers = array('Html', 'Form', 'Session', 'Time', 'Js');

	public $components = array('Session');

/**
 * Allow admin users to access all method of DocumentsController, but
 * allow other users only to access DocumentsController::index().
 *
 * @param string $user
 * @return bool
 */
	public function isAuthorized($user) {
		if ($this->action === 'index' || $this->action === 'shareLink' ||
			(isset($user['role']) && $user['role'] === 'admin')) {
			return true;
		}
		return parent::isAuthorized($user);
	}

/**
 * Return the content of a folder to the view. By default, the folder’s
 * id is set to 0, which is the root folder.
 *
 * @param int $folderId The current folder id.
 * @throws NotFoundException Return an exception if the id
 * folder is not a number.
 */
	public function index($folderId = 0) {
		$this->set('title_for_layout', __(' '));

		if (is_numeric($folderId)) {
			// Retrieve all folders contained by current folder
			$folders = $this->Dir->find(
				'all',
				array(
					'conditions' => array('parent' => $folderId),
					'order' => array('name')
				)
			);

			// Add the number of elements contained by each folder
			foreach ($folders as &$folder) {
				$folder['Dir']['content'] = $this
					->_count($folder['Dir']['id']);
			}

			// Retrieve all files contained by current folder
			$documents = $this->Document->find(
				'all', array(
					'conditions' => array('folder' => $folderId),
					'order' => array('name')
				)
			);

			// Add the path to the folder containing the file on the server
			foreach ($documents as &$document) {
				$document = $this->_pathToFile($document);
			}

			// Set all folders and files contained by current folder
			$this->set('folders', $folders);
			$this->set('documents', $documents);

			// Set the current folder name
			if ($folderId == 0) {
				$currentName = 'Racine';
			} else {
				$currentDir = $this->Dir->findById($folderId);
				$currentName = $currentDir['Dir']['name'];

				// Return information of all the parent folder to the
				// view to allow a backward link
				$previousId = $currentDir['Dir']['parent'];
				$previousDirs = array();
				if ($previousId == 0) {
					$previousDirs[] = $this->_createRootDir();
				} else {
					$workingId = $previousId;
					do {
						$previousDir = $this->Dir->findById($workingId);
						$previousDirs[] = $previousDir;
						$workingId = $previousDir['Dir']['parent'];
					} while ($workingId != 0);
					$previousDirs[] = $this->_createRootDir();
				}
				$this->set('previousDirs', array_reverse($previousDirs));
			}
			$this->set('currentName', $currentName);
			$this->set('currentID', $folderId);

		} else {
			throw new NotFoundException(
				__('L’argument doit être un nombre entier.')
			);
		}
	}

/**
 * Allow to download a file stored by the application without knowing
 * its location on the server, but only its id.
 *
 * @param int $fileId The ID of the file we want to share/download
 * @return CakeResponse
 */
	public function shareLink($fileId) {
		$fileToShare = $this->Document->findById($fileId);
		$fileToShare = $this->_pathToFile($fileToShare);

		$this->response->file(
			$fileToShare['Document']['path'] . DS .
				$fileToShare['Document']['file'],
			array(
				'download' => true,
				'name' => $fileToShare['Document']['name']
			)
		);
		return $this->response;
	}

/**
 * Delete an existing folder and all of its contents
 *
 * @param int $folderId The ID of the folder to delete.
 * @throws MethodNotAllowedException
 */
	public function deleteDir($folderId) {
		if ($this->request->is('get')) {
			throw new MethodNotAllowedException();
		} else {
			// Retrieve the name and the parent of the folder to delete
			$currentDir = $this->Dir->findById($folderId);
			$name = $currentDir['Dir']['name'];
			$parent = $currentDir['Dir']['parent'];

			// Retrieve all folder ID that are inside the current one
			$folders[] = $folderId;
			$newDirsList[] = $folderId;
			while (!empty($newDirsList)) {
				// Will contain last retrieved folders, in which we
				// will search childs during the next loop
				$lastDirsList = array();

				foreach ($newDirsList as $id) {
					$children = $this->Dir->find(
						'all',
						array('conditions' => array('parent' => $id))
					);

					$childrenDirs = array();
					foreach ($children as $child) {
						$childrenDirs[] = $child['Dir']['id'];
					}
					$folders = array_merge($folders, $childrenDirs);
					$lastDirsList = array_merge(
						$lastDirsList,
						$childrenDirs
					);
				}
				$newDirsList = $lastDirsList;
			}

			// Delete folder and all its contents
			foreach ($folders as $id) {
				$this->Dir->delete($id);
				$files = $this->Document->find(
					'all',
					array('conditions' => array('folder' => $id))
				);
				if (!empty($files)) {
					foreach ($files as $file) {
						$this->_delete($file['Document']['id']);
					}
				}
			}

			// Set a message and redirect the user
			$this->Session->setFlash(
				__('Le dossier %s et son contenu ont été supprimés.', h($name))
			);
			return $this->redirect(array('action' => 'index', $parent));
		}
	}

/**
 * Create a new folder inside the current one.
 *
 * @param (int) $id The ID of the current folder.
 * @throws NotFoundException Return an exception if the ID does not
 * correspond to an existing folder.
 */
	public function addDir($folderId) {
		$this->set('title_for_layout', __('Nouveau dossier'));


		// Check that the current folder exists
		if ($folderId != 0) {
			if (!$this->Dir->findById($folderId)) {
				throw new NotFoundException(__('Mauvais dossier parent'));
			}
		}

		$this->set('previousId', $folderId);

		if ($this->request->is('post')) {
			$folderName = $this->request->data['Dir']['name'];
			$folderExists = $this->Dir->find(
				'all',
				array(
					'conditions' => array(
						'name' => $folderName,
						'parent' => $folderId
					)
				)
			);
			if ($folderExists) {
				$this->Session->setFlash(
					__('Un dossier portant le nom %s existe déjà. Veuillez choisir un autre nom.', h($folderName))
				);
			} else {
				$this->Dir->create();
				if ($this->Dir->save($this->request->data)) {
					return $this->redirect(
						array('action' => 'index', $folderId)
					);
				}
				$this->Session->setFlash(__('Impossible de créer ce dossier.'));
			}
		}
	}

/**
 * Delete an existing document.
 *
 * @param int $fileId The ID of the file to delete.
 * @throws MethodNotAllowedException Return an exception if the ID
 * does not exist in database.
 */
	public function deleteFile($fileId) {
		if ($this->request->is('get')) {
			throw new MethodNotAllowedException();
		} else {
			// Retrieve the name and the parent of the file to delete
			$document = $this->Document->findById($fileId);
			$name = $document['Document']['name'];
			$folder = $document['Document']['folder'];

			// Delete the file
			$this->_delete($fileId);

			// Set a message and redirect the user
			$this->Session->setFlash(
				__('Le fichier %s a été supprimé.', h($name))
			);
			return $this->redirect(array('action' => 'index', $folder));
		}
	}

/**
 * Create a entry in database for a file uploaded on the server. The
 * copy of the file is done during the upload validation. If a file
 * with the same name and in the same folder already exists, it is
 * replaced on the server and the database is simply uploaded.
 *
 * @param int $folderId The ID of the folder that will contain the file.
 * @throws NotFoundException Return an exception if the $id does not
 * correspond to an existing folder.
 */
	public function addFile($folderId) {
		$this->set('title_for_layout', __('Nouvel upload'));

		// Check if the folder which will contain the uploaded file exist
		$folder = $this->Dir->findById($folderId);
		if (!$folder && $folderId != 0) {
			throw new NotFoundException(
				__(
					'Le dossier ayant pour identifiant %s n’existe pas.',
					h($folderId)
				)
			);
		}

		$this->set('previousId', $folderId);

		if ($this->request->is('post')) {
			// Check if the file already exists
			$fileToUpload = $this->Document->findByName(
				$this->request->data['Document']['name']['name']
			);

			if ($fileToUpload &&
				$fileToUpload['Document']['folder'] == $folderId) {
				//

				$this->Document->id = $fileToUpload['Document']['id'];
				// Replace the old file by the new one
				$fileToUpload = $this->_pathToFile($fileToUpload);
				move_uploaded_file(
					$this->request->data['Document']['name']['tmp_name'],
					$fileToUpload['Document']['path'] .
						DS . $fileToUpload['Document']['file']
				);

				if ($this->Document->save()) {
					$this->Session->setFlash(
						__(
							'Le fichier %s a bien été remplacé.',
							h($fileToUpload['Document']['name'])
						)
					);
					return $this->redirect(
						array('action' => 'index', $folderId)
					);
				}
			} else {
				// Save a new entry in database
				$data['Document']['folder'] = $folderId;
				$data['Document']['name'] = $this
					->request->data['Document']['name']['name'];
				$data['Document']['file'] = time();

				// Save the uploaded file on the server
				move_uploaded_file(
					$this->request->data['Document']['name']['tmp_name'],
					$this->_createDir() . DS . $data['Document']['file']
				);

				if ($this->Document->save($data)) {
					$this->Session->setFlash(
						__(
							'Le fichier %s a bien été ajouté.',
							h($fileToUpload['Document']['name'])
						)
					);
					return $this->redirect(
						array('action' => 'index', $folderId)
					);
				}
			}
		}
	}

/**
 * Move a folder into a new folder.
 *
 * @param int $folderId The ID of the folder we want to move.
 * @throws NotFoundException Return an exception if $folderId does not
 * correspond to an existing folder.
 */
	public function moveDir($folderId) {
		$this->set( 'title_for_layout', __('Déplacer'));

		$folderToMove = $this->Dir->findById($folderId);
		if (!$folderToMove) {
			throw new NotFoundException(__('Ce dossier n’existe pas.'));
		}

		if ($folderToMove['Dir']['parent'] == 0) {
			$previousId = 0;
		} else {
			$currentDir = $this->Dir->findById(
				$folderToMove['Dir']['parent']
			);
			$previousId = $currentDir['Dir']['id'];
		}
		$this->set('previousId', $previousId);

		$this->set('folderToMove', $folderId);

		$this->set(
			'page_title',
			__(
				'Où souhaitez-vous déplacer le dossier %s ?',
				h($folderToMove['Dir']['name'])
			)
		);

		$this->set('model', 'Dir');
		$this->set('field', 'parent');

		$this->set('folders', $this->_displayDirsList($folderId));

		if ($this->request->is('post')) {
			$this->Dir->id = $folderId;
			if ($this->Dir->save($this->request->data)) {
				$this->Session->setFlash(
					__(
						'Le dossier %s a bien été déplacé.',
						h($folderToMove['Dir']['name'])
					)
				);
				return $this->redirect(
					array(
						'action' => 'index',
						$this->request->data['Dir']['parent']
					)
				);
			}
		}
	}

/**
 * Move a file into a new folder.
 *
 * @param int $fileId The ID of the file we want to move.
 * @throws NotFoundException Return an exception if $fileId does not
 * correspond to an existing file.
 */
	public function moveFile($fileId) {
		$this->set('title_for_layout', __('Déplacer'));

		$fileToMove = $this->Document->findById($fileId);
		if (!$fileToMove) {
			throw new NotFoundException(__('Ce fichier n’existe pas.'));
		}

		if ($fileToMove['Document']['folder'] == 0) {
			$previousId = 0;
		} else {
			$currentDir = $this->Dir->findById(
				$fileToMove['Document']['folder']
			);
			$previousId = $currentDir['Dir']['id'];
		}

		$this->set('previousId', $previousId);

		$this->set(
			'page_title',
			__(
				'Où souhaitez-vous déplacer le dossier %s ?',
				h($fileToMove['Document']['name'])
			)
		);

		$this->set('model', 'Document');
		$this->set('field', 'folder');

		$this->set('folders', $this->_displayDirsList());

		if ($this->request->is('post')) {
			$this->Document->id = $fileId;
			if ($this->Document->save($this->request->data)) {
				$this->Session->setFlash(__(
					'Le fichier ' . $fileToMove['Document']['name'] .
					' a bien été déplacé.'
				));
				return $this->redirect(
					array(
						'action' => 'index',
						$this->request->data['Document']['folder']
					)
				);
			}
		}
	}

	public function renameDir($folderId) {
		$this->set('title_for_layout', __('Nouveau nom'));

		$folderToRename = $this->Dir->findById($folderId);
		if (!$folderToRename) {
			throw new NotFoundException(__('Ce dossier n’existe pas.'));
		}

		$this->set('folderToRename', $folderToRename);
		$oldName = $folderToRename['Dir']['name'];

		if ($this->request->is('post')) {
			$this->Dir->id = $folderId;
			if ($this->Dir->save($this->request->data)) {
				$this->Session->setFlash(__(
					'Le dossier « ' . $oldName . ' » a bien été renommé en « ' .
					$this->request->data['Dir']['name'] . ' ».'
				));
				return $this->redirect(
					array(
						'action' => 'index',
						$folderToRename['Dir']['parent']
					)
				);
			}
		}
	}

	public function renameFile($fileId) {
		$this->set('title_for_layout', __('Nouveau nom'));

		$fileToRename = $this->Document->findById($fileId);
		if (!$fileToRename) {
			throw new NotFoundException(__('Ce fichier n’existe pas.'));
		}

		$this->set('fileToRename', $fileToRename);
		$oldName = $fileToRename['Document']['name'];

		if ($this->request->is('post')) {
			$this->Document->id = $fileId;
			if ($this->Document->save($this->request->data)) {
				$this->Session->setFlash(__(
					'Le fichier « ' . $oldName . ' » a bien été renommé en « ' .
					$this->request->data['Document']['name'] . ' ».'
				));
				return $this->redirect(
					array(
						'action' => 'index',
						$fileToRename['Document']['folder']
					)
				);
			}
		}
	}

/**
 * Delete a file by erasing its entry in database and the effective
 * file on the server.
 *
 * @param int $fileId The ID of the file to delete.
 */
	protected function _delete($fileId) {
		// Retrieve file information in database
		$file = $this->Document->findById($fileId);
		$file = $this->_pathToFile($file);

		// Delete the file on the server and folders if they are empty
		unlink($file['Document']['path'] . DS . $file['Document']['file']);
		$this->_deleteDirs($file['Document']['path']);

		// Delete the database entry
		$this->Document->delete($fileId);
	}

/**
 * Return the number of elements, documents and folders, contained
 * in a specific folder.
 *
 * @param int $folderId The folder's ID.
 * @return int
 */
	protected function _count($folderId) {
		$folder = $this->Dir->find(
			'all',
			array('conditions' => array('parent' => $folderId))
		);
		$files = $this->Document->find(
			'all',
			array('conditions' => array('folder' => $folderId))
		);
		return count($folder) + count($files);
	}

/**
 * Return the path to the folder containing a file, based on its date
 * of creation on the server.
 *
 * @param Document $document The file we want to find the path.
 * @return Document
 */
	protected function _pathToFile($document) {
		$document['Document']['path'] = 'files' . DS .
			CakeTime::format('Y', $document['Document']['created']) . DS .
			CakeTime::format('m', $document['Document']['created']) . DS .
			CakeTime::format('d', $document['Document']['created']);
		return $document;
	}

/**
 * Create a hierarchy of folders on the server to save an uploaded
 * file.
 *
 * @return string Return the path to the folder that will contain the
 * uploaded file.
 */
	protected function _createDir() {
		$path = 'files' . DS . date('Y', time());
		if (!file_exists($path)) {
			mkdir($path);
		}
		$path .= DS . date('m', time());
		if (!file_exists($path)) {
			mkdir($path);
		}
		$path .= DS . date('d', time());
		if (!file_exists($path)) {
			mkdir($path);
		}
		return $path;
	}

/**
 * Delete empty folders on server.
 *
 * @param string $path The path of the folder to delete.
 * @return bool Return true if all folders are deleted.
 */
	protected function _deleteDirs($path) {
		// Erase DAY folder
		if (rmdir($path)) {
			// Erase MONTH folder
			$path = substr($path, 0, -strlen(strrchr($path, '/')));
			if (rmdir($path)) {
				// Erase YEAR folder
				$path = substr($path, 0, -strlen(strrchr($path, '/')));
				if (rmdir($path)) {
					return true;
				}
			}
		}
	}

/**
 * Return the list of all folders, including root. If element to move
 * into a new folder is itself a folder, then it will not be returned
 * alongside the others.
 *
 * @param int $folderId The Id of the folder moved by
 * DocumentsController::moveDir. This argument is optional as it is
 * not used by DocumentsController::moveFile.
 *
 * @return array
 */
	protected function _displayDirsList($folderId = null) {
		$root = array(
			'0' => $this->_createRootDir()
		);
		$root['0']['Dir']['name'] = '&nbsp;' . $root['0']['Dir']['name'];

		$level = '&nbsp;└–';
		return $this->_createDirsList($root, 0, $level, $folderId);
	}

/**
 * Create a hierarchic list of all the folders present in database.
 *
 * @param array $root The array containing our hierarchic list.
 * @param int $parentId The ID of the folder we want to retrieve the
 * content.
 * @param string $level The string to append in front of the folder’s name.
 * @param int/null $folderId The ID of the folder to move, NULL if we
 * move a file.
 * @return array
 */
	protected function _createDirsList($root, $parentId, $level, $folderId = null) {
		// We retrieve only the folders contained by the one
		// which has $parentId as ID
		$folders = $this->Dir->find(
			'all',
			array(
				'conditions' => array('parent' => $parentId),
				'order' => array('name')
			)
		);
		// If there is folders inside, then we try to retrieve
		// the folders inside these folders
		if (!empty($folders)) {
			foreach ($folders as $folder) {
				// If moved element is a folder, it will not be displayed
				if (!is_null($folderId) &&
					$folder['Dir']['id'] == $folderId) {
					continue;
				} else {
					$newLevel = $level . '–––';
					$folder['Dir']['name'] = $level . '→' .
						$folder['Dir']['name'];
					// We add the first retrieved folder to the list
					$root[] = $folder;
					// Then we retrieve the folders it contains and
					// start again by making the method calling itself
					$root = $this->_createDirsList(
						$root,
						$folder['Dir']['id'],
						$newLevel,
						$folderId
					);
				}
			}
		}

		return $root;
	}

/**
 * Return Root folder information respecting CakePHP conventions.
 *
 * @return array
 */
	protected function _createRootDir() {
		return array(
			'Dir' => array(
				'id' => 0,
				'name' => 'Racine',
				'parent' => 'none'
			)
		);
	}
}
