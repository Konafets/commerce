<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2008 - 2012 Ingo Schmitt <typo3@marketing-factory.de>
 * (c) 2013 Sebastian Fischer <typo3@marketing-factory.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Implements a browseable AJAX tree
 */
abstract class Tx_Commerce_Tree_Browsetree {
	/**
	 * Name of the table
	 *
	 * @var string
	 */
	protected $treeName;

	/**
	 * Should the clickmenu be disabled?
	 *
	 * @var boolean
	 */
	protected $noClickmenu;

	/**
	 * The Leafs of the tree
	 *
	 * @var array
	 */
	protected $leafs;

	/**
	 * has the tree already been initialized?
	 *
	 * @var boolean
	 */
	protected $isInit;

	/**
	 * Number of leafs in the tree
	 *
	 * @var integer
	 */
	protected $leafcount;

	/**
	 * will hold the rendering method of the tree
	 *
	 * @var string
	 */
	protected $renderBy;

	/**
	 * the uid from which to start rendering recursively, if we so chose to
	 *
	 * @var integer
	 */
	protected $startingUid;

	/**
	 * the recursive depth to choose if we chose to render recursively
	 *
	 * @var integer
	 */
	protected $depth;

	/**
	 * Constructor - init values
	 *
	 * @return self
	 */
	public function __construct() {
		$this->leafs = array();
		$this->leafcount = 0;
		$this->isInit = FALSE;
		$this->noClickmenu = FALSE;
		$this->renderBy = 'Tx_Commerce_Tree_Leaf_Mounts';
		$this->startingUid = 0;
	}

	/**
	 * Initializes the Browsetree
	 *
	 * @return void
	 */
	public function init() {
		$this->isInit = TRUE;
	}

	/**
	 * Sets the clickmenu flag for the tree
	 * Gets passed along to all leafs, which themselves pass it to their view
	 * Has to be set BEFORE initializing the tree with init()
	 *
	 * @return void
	 * @param boolean $flag [optional]	Flag
	 */
	public function noClickmenu($flag = TRUE) {
		if (!is_bool($flag)) {
			if (TYPO3_DLOG) {
				t3lib_div::devLog('noClickmenu (Tx_Commerce_Tree_Browsetree) gets a non-boolean parameter (expected boolean)!', COMMERCE_EXTKEY, 2);
			}
		}
		$this->noClickmenu = $flag;
	}

	/**
	 * Adds a leaf to the Tree
	 *
	 * @param Tx_Commerce_Tree_Leaf_Master $leaf - Treeleaf Object which holds the Tx_Commerce_Tree_Leaf_Data and the Tx_Commerce_Tree_Leaf_View
	 * @return boolean
	 */
	public function addLeaf(Tx_Commerce_Tree_Leaf_Master &$leaf) {
			// pass tree vars to the new leaf
		$leaf->setTreeName($this->treeName);
		$leaf->noClickmenu($this->noClickmenu);

			// add to collection
		$this->leafs[$this->leafcount ++] = $leaf;

		return TRUE;
	}

	/**
	 * Returns the leaf object at the given index
	 *
	 * @param integer $index Leaf index
	 * @return Tx_Commerce_Tree_Leaf_Master
	 */
	public function getLeaf($index) {
		if (!is_numeric($index) || !isset($this->leafs[$index])) {
			if (TYPO3_DLOG) {
				t3lib_div::devLog('getLeaf (Tx_Commerce_Tree_Browsetree) has an invalid parameter.', COMMERCE_EXTKEY, 3);
			}
			return NULL;
		}

		return $this->leafs[$index];
	}

	/**
	 * Sets the unique tree name
	 *
	 * @return void
	 * @param string $tree - Name of the Tree
	 */
	public function setTreeName($tree = '') {
		$this->treeName = $tree;
	}

	/**
	 * Sets the internal rendering method to 'Tx_Commerce_Tree_Leaf_Mounts'
	 * Call BEFORE initializing
	 *
	 * @return void
	 */
	public function readByMounts() {
			// set internal var
		$this->renderBy = 'Tx_Commerce_Tree_Leaf_Mounts';
	}

	/**
	 * Sets the internal rendering method to 'recursively'
	 * Call BEFORE initializing
	 *
	 * @return void
	 * @param integer $uid UID from which the masterleafs should start
	 * @param integer $depth
	 */
	public function readRecursively($uid, $depth = 100) {
		if (!is_numeric($uid)) {
			if (TYPO3_DLOG) {
				t3lib_div::devLog('readRecursively (Tx_Commerce_Tree_Browsetree) has an invalid parameter.', COMMERCE_EXTKEY, 3);
			}
			return;
		}

			// set internal vars
		$this->renderBy = 'recursively';
		$this->depth = $depth;
		$this->startingUid = $uid;
	}

	/**
	 * Returns a browseable Tree
	 * Tree is automatically generated by using the Mountpoints and the User position
	 *
	 * @return string
	 */
	public function getBrowseableTree() {
		$return = '';

		switch($this->renderBy) {
			case 'Tx_Commerce_Tree_Leaf_Mounts':
				$this->getTreeByMountpoints();
				$return = $this->printTreeByMountpoints();
				break;

			case 'recursively':
				$this->getTree();
					// @see printTree
				$return = $this->printTree(0);
				break;

			default:
				if (TYPO3_DLOG) {
					t3lib_div::devLog('The Browseable Tree could not be printed. No rendering method was specified', COMMERCE_EXTKEY, 3);
				}
				break;
		}

		return $return;
	}

	/**
	 * Returns a browseable Tree (only called by AJAX)
	 * Note that so far this is only supported if you work with mountpoints;
	 *
	 * @todo Make it possible as well for a recursive tree
	 *
	 * @return string HTML Code for Tree
	 * @param array $PM Array from PM link
	 */
	public function getBrowseableAjaxTree($PM) {
		if (is_null($PM) || !is_array($PM)) {
			if (TYPO3_DLOG) {
				t3lib_div::devLog('The browseable AJAX tree (getBrowseableAjaxTree) was not printed because a parameter was invalid.', COMMERCE_EXTKEY, 3);
			}
			return '';
		}

			// Create the tree by mountpoints
		$this->getTreeByMountpoints();

		return $this->printAjaxTree($PM);
	}

	/**
	 * Forms the tree based on the mountpoints and the user positions
	 *
	 * @return void
	 */
	public function getTreeByMountpoints() {
			// Alternate Approach: Read all open Categories at once
			// Select those whose parent_id is set in the positions-Array
			// and those whose UID is set as the Mountpoint

			// Get the current position of the user
		$this->initializePositionSaving();

			// Go through the leafs and feed them the ids
		$leafCount = count($this->leafs);
		for ($i = 0; $i < $leafCount; $i ++) {
			/** @var Tx_Commerce_Tree_Leaf_Master $leaf */
			$leaf = & $this->leafs[$i];
			$leaf->byMounts();
				// Pass $i as the leaf's index
			$leaf->init($i);
		}
	}

	/**
	 * Forms the tree
	 *
	 * @return void
	 */
	public function getTree() {
		$uid = $this->startingUid;
		$depth = $this->depth;

			// Go through the leafs and feed them the id
		$leafCount = count($this->leafs);
		for ($i = 0; $i < $leafCount; $i ++) {
			/** @var Tx_Commerce_Tree_Leaf_Master $leaf */
			$leaf = & $this->leafs[$i];
			$leaf->setUid($uid);
			$leaf->setDepth($depth);
			$leaf->init($i);
		}
	}

	/**
	 * Prints the subtree for AJAX requests only
	 *
	 * @return string HTML Code
	 * @param array $PM Array from PM link
	 */
	public function printAjaxTree($PM) {
		if (is_null($PM) || !is_array($PM)) {
			if (TYPO3_DLOG) {
				t3lib_div::devLog('The AJAX Tree (printAjaxTree) was not printed because the parameter was invalid.', COMMERCE_EXTKEY, 3);
			}
			return '';
		}

		$l = count($PM);

			// parent|ID is always the last Item
		$values = explode('|', $PM[count($PM) - 1]);

			// assign current uid
		$id = $values[0];
			// assign item parent
		$pid = $values[1];
			// Extract the bank
		$bank = $PM[2];
		$indexFirst = $PM[1];

		$out = '';

			// Go to the correct leaf and print it
		/** @var Tx_Commerce_Tree_Leaf_Master $leaf */
		$leaf = &$this->leafs[$indexFirst];

			// i = 4 because if we have childleafs at all, this is where they will stand in PM Array
			// l - 1 because the last entry in PM is the id
		for ($i = 4; $i < $l - 1; $i ++) {
			$leaf = &$leaf->getChildLeaf($PM[$i]);

				// If we didnt get a leaf, return
			if ($leaf == NULL) {
				return '';
			}
		}

		$out .= $leaf->printChildleafsByLoop($id, $bank, $pid);

		return $out;
	}

	/**
	 * Prints the Tree starting with the uid
	 *
	 * @todo Implement this function if it is ever needed. So far it's not. Delete this function if it is never needed.
	 *
	 * @return string
	 * @param integer $uid UID of the Item that will be started with
	 */
	public function printTree($uid) {
		die('The function printTree in Browsetree.php is not yet filled. Fill it if you are using it. Search for this text to find the code. ' . $uid);
	}

	/**
	 * Prints the Tree by the Mountpoints of each treeleaf
	 *
	 * @return string HTML Code for Tree
	 */
	public function printTreeByMountpoints() {
		$out = '<ul class="tree">';

			// Get the Tree for each leaf
		for ($i = 0; $i < $this->leafcount; $i ++) {
			/** @var Tx_Commerce_Tree_Leaf_Master $leaf */
			$leaf = & $this->leafs[$i];
			$out .= $leaf->printLeafByMounts();
		}
		$out .= '</ul>';

		return $out;
	}

	/**
	 * Returns the Records in the tree as a array
	 * Records will be sorted to represent the tree in linear order
	 *
	 * @param integer $rootUid - UId of the Item that will act as the root of the tree
	 * @return array
	 */
	public function getRecordsAsArray($rootUid) {
		if (!is_numeric($rootUid)) {
			if (TYPO3_DLOG) {
				t3lib_div::devLog('getRecordsAsArray has an invalid $rootUid', COMMERCE_EXTKEY, 3);
			}
			return array();
		}

			// Go through the leafs and get sorted array
		$leafCount = count($this->leafs);

		$sortedData = array();

			// Initialize the categories (and its leafs)
		for ($i = 0; $i < $leafCount; $i ++) {
			/** @var Tx_Commerce_Tree_Leaf_Master $leaf */
			$leaf = $this->leafs[$i];
			if ($leaf->data->hasRecords()) {
				$leaf->sort($rootUid);
				$sortedData = array_merge($sortedData, $leaf->getSortedArray());
			}
		}

		return $sortedData;
	}

	/**
	 * Returns an array that has as key the depth and as value the category ids on that depth
	 * Sorts the array in the process
	 * 		[0] => '13'
	 * 		[1] => '12, 11, 39, 54'
	 *
	 * @return array
	 * @param integer $rootUid
	 */
	public function &getRecordsPerLevelArray($rootUid) {
		if (!is_numeric($rootUid)) {
			if (TYPO3_DLOG) {
				t3lib_div::devLog('getRecordsPerLevelArray has an invalid parameter.', COMMERCE_EXTKEY, 3);
			}
			return array();
		}

			// Go through the leafs and get sorted array
		$leafCount = count($this->leafs);

		$sortedData = array();

			// Sort and return the sorted array
		for ($i = 0; $i < $leafCount; $i ++) {
			/** @var Tx_Commerce_Tree_Leaf_Master $leaf */
			$leaf = $this->leafs[$i];

			$leaf->sort($rootUid);
			$sorted = $leaf->getSortedArray();
			$sortedData = array_merge($sortedData, $sorted);
		}

			// Create the depth_catUids array
		$depth = array();

		$l = count($sortedData);

		for ($i = 0; $i < $l; $i ++) {
			if (!is_array($depth[$sortedData[$i]['depth']])) {
				$depth[$sortedData[$i]['depth']] = array($sortedData[$i]['record']['uid']);
			} else {
				$depth[$sortedData[$i]['depth']][] = $sortedData[$i]['record']['uid'];
			}
		}

		return $depth;
	}

	/**
	 * Will initialize the User Position
	 * Saves it in the Session and gives the Position UIDs to the Tx_Commerce_Tree_Leaf_Data
	 *
	 * @return void
	 */
	protected function initializePositionSaving() {
			// Get stored tree structure:
		$positions = unserialize($GLOBALS['BE_USER']->uc['browseTrees'][$this->treeName]);

			// In case the array is not set, initialize it
		if (!is_array($positions) || 0 >= count($positions) || key($positions[0][key($positions[0])]) !== 'items') {
				// reinitialize damaged array
			$positions = array();
			$this->savePosition($positions);
			if (TYPO3_DLOG) {
				t3lib_div::devLog('Resetting the Positions of the Browsetree. Were damaged.', COMMERCE_EXTKEY, 2);
			}
		}

		$PM = t3lib_div::_GP('PM');
			// IE takes # as anchor
		if (($PMpos = strpos($PM, '#')) !== FALSE) {
			$PM = substr($PM, 0, $PMpos);
		}

			// 0: treeName, 1: leafIndex, 2: Mount, 3: set/clear [4:,5:,.. further leafIndices], 5[+++]: Item UID
		$PM = explode('_', $PM);

			// PM has to be at LEAST 5 Items (up to a (theoratically) unlimited count)
		if (count($PM) >= 5 && $PM[0] == $this->treeName) {
					// Get the value - is always the last item
					// so far this is 'current UID|Parent UID'
				$value = explode('|', $PM[count($PM) - 1]);
					// now it is 'current UID'
				$value = $value[0];

					// Prepare the Array
				$c = count($PM);
					// We get the Mount-Array of the corresponding leaf index
				$field = &$positions[$PM[1]][$PM[2]];

					// Move the field forward if necessary
				if ($c > 5) {
					$c -= 4;

						// Walk the PM
					$i = 4;

						// Leave out last value of the $PM Array since that is the value and no longer a leaf Index
					while ($c > 1) {
							// Mind that we increment $i on the fly on this line
						$field = &$field[$PM[$i++]];
						$c --;
					}
				}

				if ($PM[3]) {
					$field['items'][$value] = 1;
					$this->savePosition($positions);
				} else {
					unset($field['items'][$value]);
					$this->savePosition($positions);
				}
		}

			// Set the Positions for each leaf
		$leafCount = count($this->leafs);
		for ($i = 0; $i < $leafCount; $i ++) {
			/** @var Tx_Commerce_Tree_Leaf_Master $leaf */
			$leaf = & $this->leafs[$i];
			$leaf->setDataPositions($positions);
		}
	}

	/**
	 * Saves the content of ->stored (keeps track of expanded positions in the tree)
	 * $this->treeName will be used as key for BE_USER->uc[] to store it in
	 *
	 * @param array $positions	Positionsarray
	 * @return void
	 * @access private
	 */
	protected function savePosition(&$positions) {
		if (is_null($positions) || !is_array($positions)) {
			if (TYPO3_DLOG) {
				t3lib_div::devLog('The Positions were not saved because the parameter was invalid', COMMERCE_EXTKEY, 3);
			}
			return;
		}

		/** @var t3lib_beUserAuth $backendUser */
		$backendUser = & $GLOBALS['BE_USER'];
		$backendUser->uc['browseTrees'][$this->treeName] = serialize($positions);
		$backendUser->writeUC();
	}
}

class_alias('Tx_Commerce_Tree_Browsetree', 'browsetree');

?>