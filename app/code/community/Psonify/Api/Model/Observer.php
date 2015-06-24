<?php 
class Psonify_Api_Model_Observer
{
    //this is hook to Magento's event dispatched before action is run
    public function hookToControllerActionPreDispatch($observer)
    {
	
		
		
	    //we compare action name to see if that's action for which we want to add our own event
       //var_dump($observer->getEvent()->getControllerAction()->getFullActionName() );exit;
        // checkout_onepage_saveOrder	   
		$token = $this->getToken();
		if($token !=NULL){
			if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_ajaxDelete') 
			{
				//We are dispatching our own event before action ADD is run and sending parameters we need
				//Mage::dispatchEvent("remove_from_cart_before", array('request' => $observer->getControllerAction()->getRequest()));
			}
			//if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_onepage_saveOrder') 
			//{
				//We are dispatching our own event before action ADD is run and sending parameters we need
				//Mage::dispatchEvent("order_placed_before", array('request' => $observer->getControllerAction()->getRequest()));
			//}
			
		}
    }
 
    public function hookToControllerActionPostDispatch($observer)
    {
        
         //var_dump($observer->getEvent()->getControllerAction()->getFullActionName() );	exit;
		
//		echo '<pre>';print_r($observer->getEvent()->getControllerAction()->getFullActionName());exit;
		$token = $this->getToken();
//		echo '<pre>';print_r($token);exit;
		//$token =1;
		if($token !=NULL){
			//we compare action name to see if that's action for which we want to add our own event 
			if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_add') 
			{
				//We are dispatching our own event before action ADD is run and sending parameters we need
				Mage::dispatchEvent("add_to_cart_after", array('request' => $observer->getControllerAction()->getRequest()));
			}
			if(
				$observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_updatePost' ||
				$observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_updateItemOptions'
				) 
			{
				//We are dispatching our own event after action Update is run and sending parameters we need
				Mage::dispatchEvent("update_cart_after", array('request' => $observer->getControllerAction()->getRequest()));
			}
			if($observer->getEvent()->getControllerAction()->getFullActionName() == 'catalogsearch_result_index') 
			{
				//We are dispatching our own event after action Search is run and sending parameters we need
				Mage::dispatchEvent("search_after", array('request' => $observer->getControllerAction()->getRequest()));
			}
            if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_onepage_savePayment')
			{
				Mage::dispatchEvent("save_payment_after", array('request' => $observer->getControllerAction()->getRequest()));
			}
			if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_couponPost')
			{
				Mage::dispatchEvent("discount_code_after", array('request' => $observer->getControllerAction()->getRequest()));
			}
			if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_onepage_saveShipping')
			{
				Mage::dispatchEvent("save_shipping_after", array('request' => $observer->getControllerAction()->getRequest()));
			}
			if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_onepage_saveBilling')
			{
				Mage::dispatchEvent("save_billing_after", array('request' => $observer->getControllerAction()->getRequest()));
			}
			
			if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_onepage_controller_success_action') {
				Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
			}
			
		}
    }
    
	/**
	* hookToAddToCartBefore 
	* called by system on before add to cart event
	* @retun NULL 
	*/
	
 	public function hookToAddToCartBefore($observer) 
	{  
		Mage::log("Product ".$request['product']." will be added to cart.");
	}
	
	
	/**
	* hookToUpdateCartAfter 
	* called by system on after update cart event
	* @retun NULL 
	*/
	
	public function hookToUpdateCartAfter($observer)
	{
            
		$products = $this->getAllProductsFromCart();
//                echo '<pre>';print_r($products);exit;
		$data = array(
			'data' => array(
				'products' => $products,
			),
		    'token'      => $this->getToken(),
		);
		$arrPsonifyCartData = $this->getPsonifyCartData();
		$objPsonifyCartItemModel = Mage::getModel('api/psonifycartitem');
		if($arrPsonifyCartData){
		
			$objPsonifyCartItemModelData	= $this->getPsonifyCartItemData($arrPsonifyCartData[0]['id'], $products[0]['identifier']['value']);		
			
			
			
			if($objPsonifyCartItemModelData){
				$objPsonifyCartItemModel->load($objPsonifyCartItemModelData[0]['id'])
					->setQty($products[0]['qty'])
					->save();
			}
		}
		
		//echo '<pre>';print_r($products);exit;
		
		
		//var_dump($data);exit;
		//$apiWrapper = new Psonify_Api_Model_Wrapper($this->getApiUrl());
		//$response = $apiWrapper->callApi('cart/update',$data);
	}
    
	
	
	/**
	* hookToAddToCartAfter 
	* called by system on after add to cart event
	* @retun NULL 
	*/
	
	
	public function hookToAddToCartAfter($observer) 
	{
	
	    $request = $observer->getEvent()->getRequest()->getParams();
		
		$arrPsonifyCartData = $this->getPsonifyCartData();
		$objPsonifyCartItemModel = Mage::getModel('api/psonifycartitem');
		
		$arrCartData = array_merge($this->getProductFromCart($request['product']), $request);
//                echo '<pre>';print_r($request);
//		echo '<pre>';print_r($arrCartData);exit;	
		$data = array(
		    'data' => array(
				'product' => $arrCartData,
			),
			'token'      => $this->getToken(),
		);
		
		if($arrPsonifyCartData){    
		
			$objPsonifyCartItemModelData	= $this->getPsonifyCartItemData($arrPsonifyCartData[0]['id'], $request['product']);		
												
			if($objPsonifyCartItemModelData){
				$objPsonifyCartItemModel->load($objPsonifyCartItemModelData[0]['id'])
					->setQty($arrCartData['qty'])
					->save();
			}else{
		
				$objPsonifyCartItemModel->setData(
					array(
						'psonify_cart_id' => $arrPsonifyCartData[0]['id'],
						'cart_item_id' => $request['product'],
						'qty' => $arrCartData['qty'],
						'serialize_string' => serialize($arrCartData)
					)
				)->save();
			}
		}
		
		$apiWrapper = new Psonify_Api_Model_Wrapper($this->getApiUrl());
		$response = $apiWrapper->callApi('cart/add', $data );
	}
	
	/**
	* hookToRemoveFromCart 
	* called by system on after remove cart event
	* @retun NULL 
	*/
	
	public function hookToRemoveFromCart($observer){
		
		$product =  $this->retriveDataFromProduct($observer->getQuoteItem()->getProduct());
		$data = array(
			 'data' => array(
				'product' => $product,
			),
			'token'      => $this->getToken(),
		);
		
		$objPsonifyCartItemModel = Mage::getModel('api/psonifycartitem');
		
		$objPsonifyCartData = $this->getPsonifyCartData();
		
		$objCartItemData 	= $this->getPsonifyCartItemData($objPsonifyCartData[0]['id'], $product['identifier']['value']);
		
		if($objCartItemData){
			
			$objPsonifyCartItemModel->setId($objCartItemData[0]['id'])->delete();
			
		}
		
		$apiWrapper = new Psonify_Api_Model_Wrapper($this->getApiUrl());
		$response = $apiWrapper->callApi('cart/remove',$data);
	}
	
	/**
	* hookToSearchAfter 
	* called by system on after search event
	* @retun NULL 
	*/
	
	public function hookToSearchAfter($observer){
		$request = $observer->getEvent()->getRequest()->getParams();
		$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
		$page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $protocol.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		$page = explode('?',$page);
		$page = $page[0];
		$data = array(
		    'data' => array(
				'keywords' => array(
					array(
						'keyword'  => $request['q'],
						'dateTime' => date('Y-m-d h:i:s', time()),
						'page'     => $page,
					),
				),
			),
			'token' => $this->getToken(),
		);
		$apiWrapper = new Psonify_Api_Model_Wrapper($this->getApiUrl());
		$response = $apiWrapper->callApi('search/add',$data);
	}
	
	/**
	* hookToSavePaymentAfter 
	* called by system on after payment methods is selected
	* @retun NULL 
	*/
	
	public function hookToSavePaymentAfter($observer){
		$request = $observer->getEvent()->getRequest()->getParams();
		$total = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();
		$data = array(
		    "data" => array(
				'payment' => array(
					'method' => $request['payment']['method'],
					'price'  => $total,
				),
				'token' => $this->getToken(),
			),
		);
		$apiWrapper = new Psonify_Api_Model_Wrapper($this->getApiUrl());
		$response = $apiWrapper->callApi('payment/add',$data);
		
	}
	
	/**
	* hookToDiscountCodeAfter 
	* called by system on after discount code is added
	* @retun NULL 
	*/
	
	public function hookToDiscountCodeAfter($observer){
		$request = $observer->getEvent()->getRequest()->getParams();
		$quote = Mage::getModel('checkout/cart')->getQuote();
		$discount = 0;
		foreach ($quote->getAllItems() as $item){
			$discount+=$item->getDiscountAmount();
		}
		$discount+=$quote->getGiftCardsAmountUsed();
		$data = array(
		    "data" => array(
				'discount' => array(
					'code'   => $request['coupon_code'],
					'amount' => $discount, 
				),
				'token' => $this->getToken(),
			),
		);
		$apiWrapper = new Psonify_Api_Model_Wrapper($this->getApiUrl());
		$response = $apiWrapper->callApi('discount/add',$data);
	}
	
	/**
	* hookToSaveBillingAfter 
	* called by system on after billing address is saved
	* @retun NULL 
	*/
	
	public function hookToSaveBillingAfter($observer){
		$request = $observer->getEvent()->getRequest()->getParams();
		$address = $request['billing'];
		$address['region'] = $address['region_id'] != '' ? $address['region_id'] : $address['region'];
		$data = array(
			"data" => array(
				'address' => array(
					'line1'       => $address['street'][0],
					'line2'       => $address['street'][1],
					'line3'       => '',
					'city'        => $address['city'],
					'postal_code' => $address['postcode'],
					'state'       => $address['region'],
					'country_code'=> $address['country_id'],
					'type'        => 'billing',
				),
				'token' => $this->getToken(),
			),
		);
		$apiWrapper = new Psonify_Api_Model_Wrapper($this->getApiUrl());
		$response = $apiWrapper->callApi('address/add',$data);
	}
	
	/**
	* hookToSaveShippingAfter 
	* called by system on after shipping address is saved
	* @retun NULL 
	*/
	
	public function hookToSaveShippingAfter($observer){
		$request = $observer->getEvent()->getRequest()->getParams();
		$address = $request['shipping'];
		$address['region'] = $address['region_id'] != '' ? $address['region_id'] : $address['region'];
		$data = array(
			"data" => array (
				'address' => array(
					'line1'       => $address['street'][0],
					'line2'       => $address['street'][1],
					'line3'       => '',
					'city'        => $address['city'],
					'postal_code' => $address['postcode'],
					'state'       => $address['region'],
					'country_code'=> $address['country_id'],
					'type'        => 'shipping',
				),
				'token' => $this->getToken(),
			),
		);
		$apiWrapper = new Psonify_Api_Model_Wrapper($this->getApiUrl());
		$response = $apiWrapper->callApi('address/add',$data);
	}
	
	/**
	* hookToSaveShippingAfter 
	* called by system before order is placed
	* @retun NULL 
	*/
	
  public function hookToOrderPlacedBefore($observer){
		$data = array(
			"data" => array (
				'price' => Mage::getModel('checkout/cart')->getQuote()->getGrandTotal(),
				'products' => $this->getAllProductsFromCart(),
				'token' => $this->getToken(),
			),
		);
		$apiWrapper = new Psonify_Api_Model_Wrapper($this->getApiUrl());
		$response = $apiWrapper->callApi('order/add',$data);
		
	
   }
	  
		
	/**
	* getProductDetailsArray
	* @param $product_id id of the product
	* @retun array with all product attributes that can be passed to api
	*/

	public function getProductDetailsArray($product_id) {
		$product= Mage::getModel('catalog/product')->load($product_id);
		$pArray	= array(
			"name" => $product->name
		);
	}
	
	/**
	* getProductIdentifierArray 
	* @param $product_id id of the product
	* @retun array with all product identifiers that can be passed to api
	*/
	
	public function getProductIdentifierArray($product_id){
		$product = Mage::getModel('catalog/product')->load($product_id);
		$pArray = array(
			"name" => 'id',
			"value" => $product->id,
		);
	}
	
	
	/**
	* getPsonifyCartData
	* @return array of a row from the table psonify_cart by token value
	*/
	
	public function getPsonifyCartData(){
		$objPsonifyCartModel = Mage::getModel('api/psonifycart')->getCollection()->addFieldToFilter('token', $this->getToken());
		return $objPsonifyCartModel->getData();
	}
	
	
	/**
	* getPsonifyCartItemData
	* @param $intCartId psonify_cart table primary key id
	* @param $intItemId item id
	* @return array of a row from the table psonify_cart_item by above parameters
	*/
	
	public function getPsonifyCartItemData($intCartId, $intItemId){
		
		$objPsonifyCartItemModel = Mage::getModel('api/psonifycartitem');
		//echo $intCartId .'->'.$intItemId;
		return $objPsonifyCartItemModel->getCollection()
								->addFieldToFilter('cart_item_id', $intItemId)
								->addFieldToFilter('psonify_cart_id', $intCartId)
								->getData();
	}
	
	public function getAllProductsFromCart(){
		$cart = Mage::getModel('checkout/cart')->getQuote();
		$products = array();
		foreach ($cart->getAllItems() as $item) {
			$product = $this->retriveDataFromProduct($item->getProduct());
			$product['qty'] = $item->getQty();
			$products[] = $product;
		}
		return $products;
	}
	
	public function getProductFromCart($product_id){
		$cart = Mage::getModel('checkout/cart')->getQuote();
		
		foreach ($cart->getAllItems() as $item) {
		//echo '<pre>';print_r($item);exit;
			if($item->getProduct()->getId() == $product_id){
				$product = $this->retriveDataFromProduct($item->getProduct());
				$product['qty'] = $item->getQty();
				return $product;
			}
		}
	}
	
	public function retriveDataFromProduct($product){
		
		$categories = array();
		$currentCatIds = $product->getCategoryIds();
		if(!empty($currentCatIds)){
			$categoryCollection = Mage::getResourceModel('catalog/category_collection')
					 ->addAttributeToSelect('name')
					 ->addAttributeToSelect('id')
					 ->addAttributeToSelect('url')
					 ->addAttributeToFilter('entity_id', $currentCatIds)
					 ->addIsActiveFilter();
			
			foreach($categoryCollection as $cat){
			  $categories[] = array('id' => $cat->getId(), 'name' => $cat->getName() , 'cate_page_url' => $cat->getUrl());
			}
		}
		$stock =  Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
		$productArray = array (
			'identifier' => array(
				'name'   => 'id',
				'value'  => $product->getId(),
			),
			'product_name' => $product->getName(),
			'image'=> (string)Mage::helper('catalog/image')->init($product, 'image')->resize(200),
			'price' => $product->getPrice(),
			'sku' => $product->getSku(),
			'url' => $product->getProductUrl(),
			'current_stock' => (int) $stock->getQty(),
			'min_stock' => $stock->getMinQty(),
			'created_at' => $product->getCreatedAt(),
			'updated_at' => $product->getUpdatedAt(),
			'categories' =>  $categories,
			'status' => $product->getStatus(),
			'visibility' => $product->getVisibility(),
			'attributes' => array(
			    'name' => $product->getName(),
				'price' => $product->getPrice(),
			),
		);
		return $productArray;
	}
	
	public function getToken(){
		return Mage::getSingleton("core/session")->getPsonifyToken();
	}
	
	public function getApiUrl(){
		// needs to fetched from admin settings in future
		return "http://api.psonifydev.com";
		
	}
        
        

}