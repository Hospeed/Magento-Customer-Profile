<?php
class Werules_Customerprofile_Model_Mysql4_Profile extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {   
        $this->_init('customerprofile/profile', 'entity_id');  // here entity_id is the primary of the table of our module. And customerprofile/profile, is the magento table name as mentioned in the config.xml file.
    }
}