<?php
class Werules_Customerprofile_SettingsController extends Mage_Core_Controller_Front_Action {
    public function preDispatch() // function that makes the settings page only available when the user is logged in
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }
    public function indexAction() // main action, sets layout and page title
    {
        $this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->getLayout()->getBlock('head')->setTitle(Mage::app()->getStore()->getFrontendName() . " - " . $this->__('Profile Settings')); // set title. this will display as: Store Name - Profile Settings. If you want to remove the Store Name just delete Mage::app()->getStore()->getFrontendName() . " - " . 
        $this->renderLayout();
    }
	
	public function getLevel($total) // simple function that creates customers levels based on how much he/she spent on the store
	{
		$level = "level-0";
		if($total == 0){$level = "level-1";} // if total spent is equal to zero
		if((500 > $total) && ($total > 0)){$level =  "level-2";} // if total spent is between zero and 500
		if((1000 > $total) && ($total > 500)){$level =  "level-3";} // if total spent is between 500 and 1000
		if((2000 > $total) && ($total > 1000)){$level =  "level-4";} // if total spent is between 1000 and 2000
		// you can add as many as you like
		// use your translation file to translate "level-x" to whatever you like
		return $level;
	}
	
	public function getTotalSpent($clientId) // get total spent by the customer
	{
		$orders = Mage::getResourceModel('sales/order_collection')
				->addFieldToSelect('*')
				->addFieldToFilter('customer_id', $clientId); // load all orders from customer
		foreach ($orders as $order) // sum all orders grand total
		{
			$total = $order->getGrandTotal();
			$sum+= $total;
		}
		return $sum;
	}
	
	public function saveAction() // function to sabe profile settings
	{
		if ( $this->getRequest()->getPost() ) {
			$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId(); // get customer id
			//$customerId = Mage::getSingleton('customer/session')->getCustomerId();
			//$customerId = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId();
			try
			{
				$postData = $this->getRequest()->getPost(); // get all post data
				$profiler = Mage::getModel('customerprofile/profile')->load($customerId, 'customer_id'); // check if customer already have data on our module table
				$nickname = $postData['nickname']; // get nickname from post data
				$status = ((isset($postData['status'])) ? 1 : 0); // makes all check boxes as 0 and 1
				//$from = "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ";
				//$to = "aaaaeeiooouucAAAAEEIOOOUUC";
				//$nickname = strtr($nickname, $from, $to);
				//$nickname = preg_replace( '/[`^~\'"]/', null, iconv( 'UTF-8', 'ASCII//TRANSLIT', $nickname ) );
				//$nickname = preg_replace("/[^a-zA-Z0-9_.]/", "", strtr($nickname, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ ", "aaaaeeiooouucAAAAEEIOOOUUC_"));
				$convertac = array('á' => 'a','à' => 'a','ã' => 'a','â' => 'a', 'é' => 'e',
									 'ê' => 'e', 'í' => 'i', 'ï'=>'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', "ö"=>"o",
									 'ú' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ'=>'n', 'Á' => 'A', 'À' => 'A', 'Ã' => 'A',
									 'Â' => 'A', 'É' => 'E', 'Ê' => 'E', 'Í' => 'I', 'Ï'=>'I', "Ö"=>"O", 'Ó' => 'O',
									 'Ô' => 'O', 'Õ' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ç' =>'C', 'N'=>'Ñ'
									 ); 
				$nickname = strtr($nickname, $convertac); // convert all accents to non-accent letters as listed above
				$nickname = preg_replace('/[^a-zA-Z0-9]+/', '', $nickname); // remove all special caracters like %$#%! and all spaces
				$nickavailable = Mage::getModel('customerprofile/profile')->load($nickname, 'nickname'); // check if nickname is available
				if($nickavailable->getNickname() == "") // if nickname available
				{
					// Creating a rewrite
					/* @var $rewrite Mage_Core_Model_Url_Rewrite */
					$rewrite = Mage::getModel('core/url_rewrite'); // create a rewrite instance
					$store_id = Mage::app()->getStore()->getStoreId(); // get current store id
					$routeXist = Mage::getModel('core/url_rewrite')
						->getCollection()
						->addFieldToFilter('request_path','customerprofile/' . $nickname . '.html'); // check is magento already have a rewrite for thar nickname
					if(count($routeXist) > 0){} // if it does, do nothing
					else
					{
						$rewrite->setStoreId($store_id)
							->setIdPath('customerprofile/' . $nickname)
							->setRequestPath('customerprofile/' . $nickname . '.html')
							->setTargetPath('customerprofile/profile/index/?u=' . $nickname) // generate the friendly url by replacing customerprofile/profile/index/?u=nickname by customerprofile/nickname.html
							->setIsSystem(true)
							->save();
					}
				}
				if($nickname == "") // if user manage to write a empty value on nickname input...
				{
					$status = 0; // ... disable his public profile
				}
				if($nickavailable->getNickname() == "" || $nickavailable->getNickname() == $profiler->getNickname()) //checks if nickname is available or if nickname is already owned by current customer
				{
					$leveler = $this->getTotalSpent($customerId); // generate how much customer have spent on store
					$leveler = $this->getLevel($leveler); // generate levels
					if($profiler->getCustomerId() != "") // check if customer id is already on our module database, if so we need to update his info
					{
						$data = array(
									'nickname' => $nickname,
									//'customer_id' => $customerId,
									'profile_img' => $postData['profile_img'],
									'level' => $leveler,
									'show_level' => ((isset($postData['show_level'])) ? 1 : 0),
									'show_birthday' => ((isset($postData['show_birthday'])) ? 1 : 0),
									'show_fav' => ((isset($postData['show_fav'])) ? 1 : 0),
									'last_bought' => ((isset($postData['last_bought'])) ? 1 : 0),
									'status' => $status
									); // data to be insert on database
						$model = Mage::getModel('customerprofile/profile')->load($profiler->getId())->addData($data); // insert data on database
						$model->setId($profiler->getId())->save(); // save (duh)
					}
					else // if customer id isnt on our database, means that we need to insert his data
					{
						Mage::getModel('customerprofile/profile')->setId($this->getRequest()->getParam('entity_id')) // using magento model to insert data into database the proper way
							->setCustomerId($customerId)
							->setNickname($nickname)
							->setProfileImg($postData['profile_img'])
							->setLevel($leveler)
							->setShowLevel((isset($postData['show_level'])) ? 1 : 0)
							->setShowBirthday((isset($postData['show_birthday'])) ? 1 : 0)
							->setShowFav((isset($postData['show_fav'])) ? 1 : 0)
							->setLastBought((isset($postData['last_bought'])) ? 1 : 0)
							->setStatus($status)
							->save();
					}
					Mage::getSingleton('customer/session')->addSuccess(Mage::helper('customerprofile')->__('Profile was successfully saved')); // throw sucess message to the html page
				}
				else
				{
					Mage::getSingleton('customer/session')->addError(Mage::helper('customerprofile')->__('This Nickname Is Already Been Used By Someone Else. Please Try Another One')); // throw error message to the html page
				}
				$this->_redirect('customerprofile/settings/index'); // redirect customer to settings page
				return;
			} catch (Exception $e) {
				Mage::getSingleton('customer/session')->addError($e->getMessage());
				Mage::getSingleton('customer/session')->setLocalshipData($this->getRequest()->getPost());
				Mage::getSingleton('customer/session')->addError(Mage::helper('customerprofile')->__('Error')); // throw error message to the html page
				$this->_redirect('customerprofile/settings/index', array('entity_id' => $this->getRequest()->getParam('entity_id'))); // redirect customer to settings page
				return;
			}
		}
		$this->_redirect('customerprofile/settings/index'); // redirect customer to settings page
	}
}