<?php
class Werules_Customerprofile_ProfileController extends Mage_Core_Controller_Front_Action {	
	public function indexAction()
	{
		$this->loadLayout();
		$this->getLayout()->getBlock('head')->setTitle(Mage::app()->getStore()->getFrontendName() . " - " . $this->__('Customer Profile')); // set title. this will display as: Store Name - Customer Profile. If you want to remove the Store Name just delete Mage::app()->getStore()->getFrontendName() . " - " . 
		$this->renderLayout();
	}
}  