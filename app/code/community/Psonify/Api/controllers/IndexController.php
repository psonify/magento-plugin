<?php

/**
 * 
 */
class Psonify_Api_IndexController extends Mage_Core_Controller_Front_Action {

	/**
	 * indexAction
	 * Sets the token in session and exports the products to psonify
	 * @return NULL
	 */
	public function indexAction() {

		// change this to 'prod' on production
		$env = 'dev';

		$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);

		$token	= $this->getRequest()->getParam('token');
		$debug	= $this->getRequest()->getParam('debug');
		$export = '';

		if('dev' == $env) {
			$export =  $this->getRequest()->getParam('export');
		} else {
			$post = $this->getRequest()->getPost();
			if(isset($post['export'])) {
				$export = $post['export'];
			}
		}

		if('products_count' == $export) {
			// exporting count
			$this->exportProductsCount();
			return true;
		} else if('products' == $export) {
			// exporting products
			$this->exportProducts();
			return true;
		} else {
			// Mage::getSingleton("core/session")->unsPsonifyToken();
			$oldToken			= Mage::getSingleton("core/session")->getPsonifyToken();
			$objPsonifyCartModel= Mage::getModel('api/psonifycart');

			if($debug){
				Mage::getSingleton("core/session")->setPsonifyDebug($debug);
				$response = array(
					'status'	=> 'success' ,
					'message'	=> "debug mode ".$debug,
				);
				$this->getResponse()->setBody(json_encode($response));
			} else {
				$response = array(
					'status'	=> 'fail',
					'message'	=> 'add token in parameters'
				);
				if($token){
					Mage::getSingleton("core/session")->setPsonifyToken($token);
					$customerId = 0;
					if(Mage::getSingleton('customer/session')->isLoggedIn()) {
						$customerData	= Mage::getSingleton('customer/session')->getCustomer();
						$customerId		= $customerData->getId();
					}

					// Save data into the database table psonify_cart with the unique token value
					$objPsonifyCartModel->setData(array(
						'token'			=> $token,
						'customer_id'	=> $customerId
					))->save();

					$message = $oldToken == NULL ? 'token is set' : 'token is updated';
					$response = array(
						'status'	=> 'success',
						'message'	=> $message,
						'oldToken'	=> $oldToken
					);
				}

				$this->getResponse()->setBody(json_encode($response));
			}
		}
	}

	/**
	 * exportProducts
	 * Exports the products as json 
	 * @return NULL
	 */
	public function exportProducts(){
		$response	= array();
		$page		= $this->getRequest()->getParam('page',1);
		$limit		= $this->getRequest()->getParam('limit',1);
		$collection = Mage::getResourceModel('catalog/product_collection')
			->setStoreId(Mage::app()->getStore()->getId())
			->addAttributeToSelect('id')
			//->addAttributeToFilter('type_id','simple')
			->addFieldToFilter('visibility', array(
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
		));
		$response['total_products']	= count($collection);
		$response['page']			= $page;
		$response['limit']			= $limit;
		$collection					= Mage::getResourceModel('catalog/product_collection')
			->setStoreId(Mage::app()->getStore()->getId())
			->addAttributeToSelect('*')
			->addFieldToFilter('visibility', array(
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
			))->addAttributeToSort('name', 'ASC')
			->setPageSize($limit)
			->setCurPage($page);

		$products = array();
		foreach($collection as $index => $product){
			$attributes = $product->getAttributes();
			$attrbs = array();
			foreach ($attributes as $attribute) {
				$label = $attribute->getFrontend()->getLabel($product);
				$label = str_replace(' ','_',strtolower(trim($label)));
				$value = $attribute->getFrontend()->getValue($product);
				$attrbs[$label] = $value;
			}
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
			$products[] = array(
				'identifier'	=> 'id',
				'value'			=> $product->getId(),
				'product_name'	=> $product->getName(),
				'image'			=> Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getSmallImage()),
				'price'			=> $product->getPrice(),
				'sku'			=> $product->getSku(),
				'url'			=> $product->getProductUrl(),
				'current_stock'	=> (int) $stock->getQty(),
				'min_stock'		=> $stock->getMinQty(),
				'created_at'	=> $product->getCreatedAt(),
				'updated_at'	=> $product->getUpdatedAt(),
				'categories'	=> $categories,
				'attributes'	=> $attrbs,
				'status'		=> $product->getStatus(),
				'visibility'	=> $product->getVisibility()
			);
		}
		$response['products'] = $products;
		$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
		$this->getResponse()->setBody(json_encode($response));
	}

	/**
	 * exportProductsCount
	 * Exports the number of products as json
	 * @return NULL
	 */
	public function exportProductsCount() {
		$collection = Mage::getResourceModel('catalog/product_collection')->setStoreId(Mage::app()->getStore()->getId())
			->setStoreId(Mage::app()->getStore()->getId())
			->addAttributeToSelect('id')
			//->addAttributeToFilter('type_id','simple')
			->addFieldToFilter('visibility', array(
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
		));
		$response['products_count'] = count($collection);
		$this->getResponse()->setBody(json_encode($response));
		$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
	}

	/**
	 * restoreCartAction
	 * Function to restore abandoned cart based on token provided.
	 * @return boolean
	 */
	public function restoreCartAction() {

		// get query string data as an array.
		$arrRequest = $this->getRequest()->getParams();

		// If token is not set return to redirect the user to home.
		if(!isset($arrRequest['token'])) {
			return false;
		} else {

			//get cart items from cart session.
			$cartItems = Mage::getModel("checkout/session")->getQuote();

			//get all visible items from the cart session model.
			$items = $cartItems->getAllVisibleItems();

			//if items there set a flag in query string and send the user back to the cart page or popup the abandoned cart popup with items.
			if(count($items)) {
				$this->_redirect('checkout/cart/index/flag/'.$arrRequest['token']);
				return false;
			} else {
				
				//get system config data.
				$configFields = Mage::getStoreConfig('psonify/psonify_group');

				//set config message define in admin system configuration.
				Mage::getSingleton('core/session')->addNotice($configField['psonify_msg_input']);

				//set psonify token from query string.
				Mage::getSingleton("core/session")->setPsonifyToken($arrRequest['token']);

				//get collection from table psonify_cart_item.
				$arrPsonifyCartItem = Mage::getModel('api/psonifycartitem')->getCollection();
				
				//set join to psonify_cart table on psonify_cart.id = psonify_cart_item.psonify_cart_id
				$arrPsonifyCartItem->getSelect()->join(
					array('cart_item' => 'psonify_cart'),
					'cart_item.id = main_table.psonify_cart_id',
					'cart_item.token'
				)
				->where("cart_item.token = '".$arrRequest['token']."'");

				//get data from the collection.
				$arrPsonifyCartItemData = $arrPsonifyCartItem->getData();

				foreach($arrPsonifyCartItemData as $row) {
					
					//set cart model and initialise.
					$objCartModel = Mage::getModel('checkout/cart');
					$objCartModel->init();
					
					//load product of each itarate item.
					$productCollection = Mage::getModel('catalog/product')->load($row['cart_item_id']);


					//if product is of type simple
					if($productCollection->getTypeId() == 'simple') {
						$_product = array( 'product_id' => $row['cart_item_id'], 'qty' => $row['qty']);
					} else if($productCollection->getTypeId() == 'configurable') { // or if product is of type configurable.
						$serialize = unserialize($row['serialize_string']);
						$_product = array(
							'product_id'		=> $row['cart_item_id'],
							'qty'				=> $row['qty'],
							'super_attribute'	=> $serialize['super_attribute']//array( $optionId => $optionValue)
						);
					}
					
					//add product option into the model and save.
					$objCartModel->addProduct($productCollection, $_product);
					$objCartModel->save();
				}
				
				//redirect the user to cart page.
				$this->_redirect("checkout/cart");
				return true;
			}
		}

	}

	/**
	 * [productsExportAction description]
	 * @return [type] [description]
	 */
	public function productsExportAction() {
		$token		= $this->getRequest()->getParam('token');
		$page		= $this->getRequest()->getParam('page');
		$limit		= $this->getRequest()->getParam('limit');
		$collection	= Mage::getResourceModel('catalog/product_collection')->setStoreId($this->getStoreId());
		#echo count($collection);exit;
		$this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
	}

	/**
	 * [assigncartAction description]
	 * @return [type] [description]
	 */
	public function assigncartAction() {
		$arrRequest = $this->getRequest()->getParams();
		if(!isset($arrRequest['token'])){
			return false;
		} else {
			$configFields = Mage::getStoreConfig('psonify/psonify_group');

			Mage::getSingleton('core/session')->addNotice($configField['psonify_msg_input']);
			Mage::getSingleton("core/session")->setPsonifyToken($arrRequest['token']);

			$arrPsonifyCartItem = Mage::getModel('api/psonifycartitem')->getCollection();//->addFieldToFilter('token', $arrRequest['token']);
			$arrPsonifyCartItem->getSelect()->join(
				array('cart_item' => 'psonify_cart'),
				'cart_item.id = main_table.psonify_cart_id',
				'cart_item.token'
			)
			->where("cart_item.token = '".$arrRequest['token']."'");

			$arrPsonifyCartItemData = $arrPsonifyCartItem->getData();

			foreach($arrPsonifyCartItemData as $row) {
				
				if(array_search($row['cart_item_id'], $arrRequest['abandonedItem']) >= 0) {
					$objCartModel = Mage::getModel('checkout/cart');
					$objCartModel->init();
					$productCollection = Mage::getModel('catalog/product')->load($row['cart_item_id']);

					if($productCollection->getTypeId() == 'simple') {
						$_product = array(
							'product_id'	=> $row['cart_item_id'],
							'qty'			=> $row['qty']
						);
					} else if($productCollection->getTypeId() == 'configurable') {
						$serialize	= unserialize($row['serialize_string']);
						$_product	= array(
							'product_id'		=> $row['cart_item_id'],
							'qty'				=> $row['qty'],
							'super_attribute'	=> $serialize['super_attribute']//array( $optionId => $optionValue)
						);
					}
					$objCartModel->addProduct($productCollection, $_product);
					$objCartModel->save();
				}
			}
			$this->_redirect("checkout/cart");
		}
	}

	/**
	 * Function to echo the template html of a particular abandoned cart based on the token passed in query string.
	 * @return string
	 */
	public function cartAction(){
		
		//set query string variable.
		$arrRequest = $this->getRequest()->getParam('token');
		
		//if token is not set return.
		if(!$arrRequest){
			return false;
		}
		
		// set collection of table psonify_cart_item.
		$arrPsonifyCartItem = Mage::getModel('api/psonifycartitem')->getCollection();

		//set join with psonify_cart table.
		$arrPsonifyCartItem->getSelect()->join(
			array('cart_item' => 'psonify_cart'),
			'cart_item.id = main_table.psonify_cart_id',
			'cart_item.token'
		)->where("cart_item.token = '".$arrRequest."'");

		//get data from the model.
		$arrPsonifyCartItemData = $arrPsonifyCartItem->getData();

		//set table header.
		$html = '<div class="fixed-table-container"><table id="table-style" data-height="400" data-row-style="rowStyle" class="table table-hover">
			<thead>
			<tr>
				<th data-field="name" class="col-md-6">
					<div class="th-inner">Product Name</div>
					<div class="fht-cell"></div>
				</th>
				<th data-field="price" class="col-md-4">
					<div class="th-inner">Qty</div>
					<div class="fht-cell"></div>
				</th>
				<th data-field="price" class="col-md-4">
					<div class="th-inner"><input type="checkbox" id="selectAll"/></div>
					<div class="fht-cell"></div>
				</th>
			</tr>
			</thead><tbody>';

		//set table body part.
		foreach($arrPsonifyCartItemData as $row) {
			$serialize = unserialize($row['serialize_string']);
			$html .= '
				<tr>
					<td> <a href="'.$serialize['url'].'">'.$serialize['attributes']['name'].'</a> </td>
					<td> '.$serialize['qty'].' </td>
					<td> <input type="checkbox" name="abandonedItem[]" value="'.$serialize['identifier']['value'].'" id="abandonedItem'.$serialize['identifier']['value'].'"/> </td>
				</tr>
			';
		}
		$html .= '</tbody></table></div>';
		echo $html;
		return ;
	}
}

?>