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
 * @package     app.Model
 * @license     http://www.gnu.org/licenses/gpl.html
 */

App::uses('AppModel', 'Model');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');

/**
 * User Model.
 *
 * This class is a basic model for user management.
 *
 * @author Damien Carcel (https://github.com/damien-carcel)
 */
class User extends AppModel {

	public $validate = array(
		'username' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Un nom d’utilisateur est requis.'
			)
		),
		'password' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Un mot de passe est requis.'
			)
		),
		'password_again' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'Un mot de passe est requis.'
			)
		)
	);

/**
 * Hash the password before saving it in database.
 *
 * @param array $options
 * @return bool
 */
	public function beforeSave($options = array()) {
		if (isset($this->data[$this->alias]['password'])) {
			$passwordHasher = new SimplePasswordHasher();
			$this->data[$this->alias]['password'] = $passwordHasher->hash(
				$this->data[$this->alias]['password']
			);
		}
		return true;
	}
}
