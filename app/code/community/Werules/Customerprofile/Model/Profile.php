<?php
class Werules_Customerprofile_Model_Profile extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		//parent::_construct();
		$this->_init('customerprofile/profile'); // this is location of the resource file.
	}
}