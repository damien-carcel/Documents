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
 * @package     sub.Folder
 * @license     http://www.gnu.org/licenses/gpl.html
 */

/**
 * Users controller.
 *
 * @author Damien Carcel (https://github.com/damien-carcel)
 */
class UsersController extends AppController {

/**
 * Allow users to access login and logout methods
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('add', 'logout');
	}

/**
 * Connect a user.
 */
	public function login() {
		$connectedUser = $this->Session->read('Auth.User.username');
		if (!empty($connectedUser)) {
			return $this->redirect(
				array('controller' => 'documents', 'action' => 'index')
			);
		}

		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				return $this->redirect($this->Auth->redirectUrl());
			} else {
				$this->Session->setFlash(
					__('Nom d’utilisateur ou mot de passe invalide. Merci de bien vouloir réessayer.')
				);
			}
		}
	}

/**
 * Disconnect a user.
 */
	public function logout() {
		return $this->redirect($this->Auth->logout());
	}

/**
 * Display all existing users. The view allow administration of user
 * (deleting, changing roles…).
 */
	public function index() {
		$this->User->recursive = 0;
		$this->set('users', $this->paginate());
	}

/**
 * Display a user information.
 *
 * @param int $id The user ID.
 * @throws NotFoundException Return an exception if user does not
 * exists
 */
	public function view($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Utilisateur invalide'));
		}
		$this->set('user', $this->User->read(null, $id));
	}

/**
 * Add a new user and redirect to the index.
 */
	public function add() {
		if ($this->request->is('post')) {
			$userExists = $this->User->findByUsername(
				$this->request->data['User']['username']
			);
			if ($userExists) {
				$this->Session->setFlash(
					__('Cet utilisateur existe déjà, merci de choisir un autre pseudo.')
				);
			} elseif (!$userExists && $this->request->data['User']['password'] === $this->request->data['User']['password_again']) {
				$this->User->create();
				if ($this->User->save($this->request->data)) {
					$this->Session->setFlash(
						__('L’utilisateur a bien été sauvegardé.')
					);
					return $this->redirect(array('action' => 'login'));
				} else {
					$this->Session->setFlash(
						__('L’utilisateur n’a pu être sauvegardé. Merci de bien vouloir réessayer.')
					);
				}
			} elseif (!$userExists && $this->request->data['User']['password'] != $this->request->data['User']['password_again']) {
				$this->Session->setFlash(
					__('Le mot de passe doit être identique lors des deux saisies.  Merci de bien vouloir réessayer.')
				);
			}
		}
	}

/**
 * Allow to edit a user information.
 *
 * @param int $id The user ID.
 * @throws NotFoundException Return an exception if user does not
 */
	public function edit($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Utilisateur invalide'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(
					__('L’utilisateur a bien été sauvegardé.')
				);
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(
					__('L’utilisateur n’a pu être sauvegardé. Merci de bien vouloir réessayer.')
				);
			}
		} else {
			$this->request->data = $this->User->read(null, $id);
			unset($this->request->data['User']['password']);
		}
	}

/**
 * Delete a user from database.
 *
 * @param int $id The user ID.
 * @throws NotFoundException Return an exception if user does not
 */
	public function delete($id = null) {
		$this->request->allowMethod('post');

		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Utilisateur invalide'));
		}

		if ($this->User->delete()) {
			$this->Session->setFlash(__('L’utilisateur a bien été supprimé.'));
			return $this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('L’utilisateur n’a pas été supprimé.'));
		return $this->redirect(array('action' => 'index'));
	}
}
