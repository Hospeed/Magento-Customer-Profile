<?php
class Werules_Customerprofile_IndexController extends Mage_Core_Controller_Front_Action {
    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }      
    public function indexAction()
    {
        $this->loadLayout();
		$this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }
	
	public function getLevel($total)
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
	
	public function getTotalSpent($clientId)
	{
		$orders = Mage::getResourceModel('sales/order_collection')
				->addFieldToSelect('*')
				->addFieldToFilter('customer_id', $clientId);
		foreach ($orders as $order)
		{
			$total = $order->getGrandTotal();
			$sum+= $total;
		}
		return $sum;
	}
	
	public function saveAction()
	{
		if ( $this->getRequest()->getPost() ) {
			$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
			//$customerId = Mage::getSingleton('customer/session')->getCustomerId();
			//$customerId = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId();
			try
			{
				$postData = $this->getRequest()->getPost();
				$profiler = Mage::getModel('customerprofile/profile')->load($customerId, 'customer_id');
				$nickname = $postData['nickname'];
				$status = ((isset($postData['status'])) ? 1 : 0);
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
				$nickname = strtr($nickname, $convertac);
				$nickname = preg_replace('/[^a-zA-Z0-9]+/', '', $nickname);
				$nickavailable = Mage::getModel('customerprofile/profile')->load($nickname, 'nickname');
				if($nickavailable->getNickname() == "")
				{
					// Creating a rewrite
					/* @var $rewrite Mage_Core_Model_Url_Rewrite */
					$rewrite = Mage::getModel('core/url_rewrite');
					$store_id = Mage::app()->getStore()->getStoreId();
					$routeXist = Mage::getModel('core/url_rewrite')
						->getCollection()
						->addFieldToFilter('request_path','customerprofile/' . $nickname . '.html');
					if(count($routeXist) > 0){}
					else
					{
						$rewrite->setStoreId($store_id)
							->setIdPath('customerprofile/' . $nickname)
							->setRequestPath('customerprofile/' . $nickname . '.html')
							->setTargetPath('customerprofile/profile/index/?u=' . $nickname)
							->setIsSystem(true)
							->save();
					}
				}
				if($nickname == "")
				{
					$status = 0;
				}
				if($nickavailable->getNickname() == "" || $nickavailable->getNickname() == $profiler->getNickname())
				{
					$leveler = $this->getTotalSpent($customerId);
					$leveler = $this->getLevel($leveler);
					if($profiler->getCustomerId() != "")
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
									);
						$model = Mage::getModel('customerprofile/profile')->load($profiler->getId())->addData($data);
						$model->setId($profiler->getId())->save();
					}
					else
					{
						Mage::getModel('customerprofile/profile')->setId($this->getRequest()->getParam('entity_id'))
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
					Mage::getSingleton('customer/session')->addSuccess(Mage::helper('customerprofile')->__('Profile was successfully saved'));
				}
				else
				{
					Mage::getSingleton('customer/session')->addError(Mage::helper('customerprofile')->__('This Nickname Is Already Been Used By Someone Else. Please Try Another One'));
				}
				$this->_redirect('customerprofile/index/index');
				return;
			} catch (Exception $e) {
				Mage::getSingleton('customer/session')->addError($e->getMessage());
				Mage::getSingleton('customer/session')->setLocalshipData($this->getRequest()->getPost());
				Mage::getSingleton('customer/session')->addError(Mage::helper('customerprofile')->__('Error'));
				$this->_redirect('customerprofile/index/index', array('entity_id' => $this->getRequest()->getParam('entity_id')));
				return;
			}
		}
		$this->_redirect('customerprofile/index/index');
	}
}  