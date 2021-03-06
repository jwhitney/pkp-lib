<?php

/**
 * @file controllers/grid/settings/sections/form/PKPSectionForm.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPSectionForm
 * @ingroup controllers_grid_settings_section_form
 *
 * @brief Form for adding/editing a section
 */

import('lib.pkp.classes.form.Form');

class PKPSectionForm extends Form {
	/** the id for the section being edited **/
	var $_sectionId;

	/** @var int The current user ID */
	var $_userId;

	/** @var string Cover image extension */
	var $_imageExtension;

	/** @var array Cover image information from getimagesize */
	var $_sizeArray;

	/**
	 * Constructor.
	 * @param $request PKPRequest
	 * @param $template string Template path
	 * @param $sectionId int optional
	 */
	function PKPSectionForm($request, $template, $sectionId = null) {
		$this->setSectionId($sectionId);

		$user = $request->getUser();
		$this->_userId = $user->getId();

		parent::Form($template);

		// Validation checks for this form
		$this->addCheck(new FormValidatorPost($this));

		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_MANAGER);
	}

	/**
	 * @copydoc Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('title', 'subEditors'));
	}

	/**
	 * Persist a section editor association
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertSubEditorEntry($request, $newRowId) {
		$context = $request->getContext();
		$userId = array_shift($newRowId);

		$subEditorsDao = Application::getSubEditorsDAO();

		// Make sure the membership doesn't already exist
		if ($subEditorsDao->editorExists($context->getId(), $this->getSectionId(), $userId)) {
			return false;
		}

		// Otherwise, insert the row.
		$subEditorsDao->insertEditor($context->getId(), $this->getSectionId(), $userId);
		return true;
	}

	/**
	 * Delete a section editor association with this section.
	 * @see ListbuilderHandler::deleteEntry
	 * @param $request PKPRequest
	 * @param $rowId int
	 * @return boolean Success
	 */
	function deleteSubEditorEntry($request, $rowId) {
		$subEditorsDao = Application::getSubEditorsDAO();
		$context = $request->getContext();

		$subEditorsDao->deleteEditor($context->getId(), $this->getSectionId(), $rowId);
		return true;
	}

	/**
	 * Update a section editor association with this section.
	 * @see ListbuilderHandler::deleteEntry
	 * @param $request PKPRequest
	 * @param $rowId int the old section editor
	 * @param $newRowId array the new section editor
	 * @return boolean Success
	 */
	function updateSubEditorEntry($request, $rowId, $newRowId) {
		$this->deleteSubEditorEntry($request, $rowId);
		$this->insertSubEditorEntry($request, $newRowId);
		return true;
	}

	/**
	 * Get the section ID for this section.
	 * @return int
	 */
	function getSectionId() {
		return $this->_sectionId;
	}

	/**
	 * Set the section ID for this section.
	 * @param $sectionId int
	 */
	function setSectionId($sectionId) {
		$this->_sectionId = $sectionId;
	}
}

?>
