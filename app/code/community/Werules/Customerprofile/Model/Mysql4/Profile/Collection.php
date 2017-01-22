<?php
	class Werules_Customerprofile_Model_Mysql4_Profile_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
		protected function _construct()
		{
			$this->_init('customerprofile/profile');
		}
	}