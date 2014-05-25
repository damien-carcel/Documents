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
 * @package     app.Model
 * @license     http://www.gnu.org/licenses/gpl.html
 */

App::uses('AppModel', 'Model');

/**
 * Document model.
 *
 * This class is a basic model for access to the “documents” table.
 *
 * @author Damien Carcel (https://github.com/damien-carcel)
 */
class Document extends AppModel {

	public $validate = array(
		'name' => array(
			'rule' => array('minLength', 3),
			'required' => array(true)
		),
		'Document' => array(
			'rule' => 'uploadedFile',
			'message' => 'Erreur durant l’upload'
		)
	);

/**
 * Check if a file has been correctly uploaded.
 *
 * The file is considered correctly uploaded if it has a tmp_name (its
 * temporary path), its size is not null and no error is returned.
 *
 * @param array $check The uploaded file's information to validate.
 * @return bool Return true if the file has been uploaded, false if
 * it has not.
 */
	public function uploadedFile($check) {
		$uploadedData = array_shift($check);

		if ((!empty($uploadedData['size']) && $uploadedData['size'] == 0) ||
			(!empty($uploadedData['tmp_name']) && $uploadedData['tmp_name'] == 'none') ||
			(!empty($uploadedData['error']) && $uploadedData['error'] != 0)) {
			return false;
		}

		return is_uploaded_file($uploadedData['tmp_name']);
	}
}
