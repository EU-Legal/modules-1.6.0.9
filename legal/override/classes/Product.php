<?php

class Product extends ProductCore
{
	
	/** @var string delivery_period */
	public $delivery_now;

	/** @var string delivery_period */
	public $delivery_later;
	
	public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* Standard Lieferzeit hinzufügen
		*/
		
		self::$definition['fields']['delivery_now']   = array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255);
		self::$definition['fields']['delivery_later'] = array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'IsGenericName', 'size' => 255);
		
		parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
		
		if (!$context)
			$context = Context::getContext();
		
		$id_lang = empty($id_lang) ? $context->language->id : $id_lang;
		
		if ($full && $this->id)
		{
			$this->delivery_now   = !empty($this->delivery_now)   ? $this->delivery_now   : Configuration::get('GC_DELIVERY_NOW',   (int)$id_lang);
			$this->delivery_later = !empty($this->delivery_later) ? $this->delivery_later : Configuration::get('GC_DELIVERY_LATER', (int)$id_lang);
		}
	}
	
	public static function getNewProducts(
		$id_lang, 
		$page_number = 0, 
		$nb_products = 10,
		$count = false, 
		$order_by = null, 
		$order_way = null, 
		Context $context = null
	)
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* Standard Lieferzeit aus Datenbank ermitteln pl.*
		*/
		
		if (!$context)
			$context = Context::getContext();

		$front = true;
		if (!in_array($context->controller->controller_type, array('front', 'modulefront')))
			$front = false;

		if ($page_number < 0) $page_number = 0;
		if ($nb_products < 1) $nb_products = 10;
		if (empty($order_by) || $order_by == 'position') $order_by = 'date_add';
		if (empty($order_way)) $order_way = 'DESC';
		if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add'  || $order_by == 'date_upd')
			$order_by_prefix = 'p';
		else if ($order_by == 'name')
			$order_by_prefix = 'pl';
		if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
			die(Tools::displayError());

		$sql_groups = '';
		if (Group::isFeatureActive())
		{
			$groups = FrontController::getCurrentCustomerGroups();
			$sql_groups = 'AND p.`id_product` IN (
				SELECT cp.`id_product`
				FROM `'._DB_PREFIX_.'category_group` cg
				LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
				WHERE cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1').'
			)';
		}

		if (strpos($order_by, '.') > 0)
		{
			$order_by = explode('.', $order_by);
			$order_by_prefix = $order_by[0];
			$order_by = $order_by[1];
		}

		if ($count)
		{
			$sql = 'SELECT COUNT(p.`id_product`) AS nb
					FROM `'._DB_PREFIX_.'product` p
					'.Shop::addSqlAssociation('product', 'p').'
					WHERE product_shop.`active` = 1
					AND product_shop.`date_add` > "'.date('Y-m-d', strtotime('-'.(Configuration::get('PS_NB_DAYS_NEW_PRODUCT') ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY')).'"
					'.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
					'.$sql_groups;
			return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
		}

		$sql = new DbQuery();
		
		/* Standard Lieferzeit aus Datenbank ermitteln pl.* */
		$sql->select(
			'p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, pl.*,
			MAX(image_shop.`id_image`) id_image, il.`legend`, m.`name` AS manufacturer_name,
			product_shop.`date_add` > "'.date('Y-m-d', strtotime('-'.(Configuration::get('PS_NB_DAYS_NEW_PRODUCT') ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY')).'" as new'
		);

		$sql->from('product', 'p');
		$sql->join(Shop::addSqlAssociation('product', 'p'));
		$sql->leftJoin('product_lang', 'pl', '
			p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl')
		);
		$sql->leftJoin('image', 'i', 'i.`id_product` = p.`id_product`');
		$sql->join(Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1'));
		$sql->leftJoin('image_lang', 'il', 'i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang);
		$sql->leftJoin('manufacturer', 'm', 'm.`id_manufacturer` = p.`id_manufacturer`');

		$sql->where('product_shop.`active` = 1');
		if ($front)
			$sql->where('product_shop.`visibility` IN ("both", "catalog")');
		$sql->where('product_shop.`date_add` > "'.date('Y-m-d', strtotime('-'.(Configuration::get('PS_NB_DAYS_NEW_PRODUCT') ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY')).'"');
		if (Group::isFeatureActive())
			$sql->where('p.`id_product` IN (
				SELECT cp.`id_product`
				FROM `'._DB_PREFIX_.'category_group` cg
				LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
				WHERE cg.`id_group` '.$sql_groups.'
			)');
		$sql->groupBy('product_shop.id_product');

		$sql->orderBy((isset($order_by_prefix) ? pSQL($order_by_prefix).'.' : '').'`'.pSQL($order_by).'` '.pSQL($order_way));
		$sql->limit($nb_products, $page_number * $nb_products);

		if (Combination::isFeatureActive())
		{
			$sql->select('MAX(product_attribute_shop.id_product_attribute) id_product_attribute');
			$sql->leftOuterJoin('product_attribute', 'pa', 'p.`id_product` = pa.`id_product`');
			$sql->join(Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.default_on = 1'));
		}
		$sql->join(Product::sqlStock('p', Combination::isFeatureActive() ? 'product_attribute_shop' : 0));

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		if ($order_by == 'price')
			Tools::orderbyPrice($result, $order_way);
		if (!$result)
			return false;

		$products_ids = array();
		foreach ($result as $row)
			$products_ids[] = $row['id_product'];
		// Thus you can avoid one query per product, because there will be only one query for all the products of the cart
		Product::cacheFrontFeatures($products_ids, $id_lang);

		return Product::getProductsProperties((int)$id_lang, $result);
	}
	
	public static function getRandomSpecial($id_lang, $beginning = false, $ending = false, Context $context = null)
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* Standard Lieferzeit aus Datenbank ermitteln pl.*
		*/
		
		if (!$context)
			$context = Context::getContext();

		$front = true;
		if (!in_array($context->controller->controller_type, array('front', 'modulefront')))
			$front = false;

		$current_date = date('Y-m-d H:i:s');
		$product_reductions = Product::_getProductIdByDate((!$beginning ? $current_date : $beginning), (!$ending ? $current_date : $ending), $context, true);

		if ($product_reductions)
		{
			$ids_product = ' AND (';
			foreach ($product_reductions as $product_reduction)
				$ids_product .= '( product_shop.`id_product` = '.(int)$product_reduction['id_product'].($product_reduction['id_product_attribute'] ? ' AND product_attribute_shop.`id_product_attribute`='.(int)$product_reduction['id_product_attribute'] :'').') OR';
			$ids_product = rtrim($ids_product, 'OR').')';

			$groups = FrontController::getCurrentCustomerGroups();
			$sql_groups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');

			// Please keep 2 distinct queries because RAND() is an awful way to achieve this result
			$sql = 'SELECT product_shop.id_product, MAX(product_attribute_shop.id_product_attribute) id_product_attribute
					FROM `'._DB_PREFIX_.'product` p
					'.Shop::addSqlAssociation('product', 'p').'
					LEFT JOIN  `'._DB_PREFIX_.'product_attribute` pa ON (product_shop.id_product = pa.id_product)
					'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.default_on = 1').'
					WHERE product_shop.`active` = 1
						'.(($ids_product) ? $ids_product : '').'
						AND p.`id_product` IN (
							SELECT cp.`id_product`
							FROM `'._DB_PREFIX_.'category_group` cg
							LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
							WHERE cg.`id_group` '.$sql_groups.'
						)
					'.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
					GROUP BY product_shop.id_product
					ORDER BY RAND()';

			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

			if (!$id_product = $result['id_product'])
				return false;
			
			/* Standard Lieferzeit aus Datenbank ermitteln pl.* */
			$sql = 'SELECT p.*, product_shop.*, stock.`out_of_stock` out_of_stock, pl.*,
						p.`ean13`, p.`upc`, MAX(image_shop.`id_image`) id_image, il.`legend`,
						DATEDIFF(product_shop.`date_add`, DATE_SUB(NOW(),
						INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).'
							DAY)) > 0 AS new
					FROM `'._DB_PREFIX_.'product` p
					LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
						p.`id_product` = pl.`id_product`
						AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
					)
					'.Shop::addSqlAssociation('product', 'p').'
					LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
					Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
					LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
					'.Product::sqlStock('p', 0).'
					WHERE p.id_product = '.(int)$id_product.'
					GROUP BY product_shop.id_product';

			$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
			if (!$row)
				return false;

			if ($result['id_product_attribute'])
				$row['id_product_attribute'] = $result['id_product_attribute'];
			return Product::getProductProperties($id_lang, $row);
		}
		else
			return false;
	}
	
	public static function getPricesDrop(
		$id_lang, 
		$page_number = 0, 
		$nb_products = 10, 
		$count = false,
		$order_by = null, 
		$order_way = null, 
		$beginning = false, 
		$ending = false, 
		Context $context = null
	)
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* Standard Lieferzeit aus Datenbank ermitteln pl.*
		*/
		
		if (!Validate::isBool($count))
			die(Tools::displayError());

		if (!$context) $context = Context::getContext();
		if ($page_number < 0) $page_number = 0;
		if ($nb_products < 1) $nb_products = 10;
		if (empty($order_by) || $order_by == 'position') $order_by = 'price';
		if (empty($order_way)) $order_way = 'DESC';
		if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add'  || $order_by == 'date_upd')
			$order_by_prefix = 'p';
		else if ($order_by == 'name')
			$order_by_prefix = 'pl';
		if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
			die (Tools::displayError());
		$current_date = date('Y-m-d H:i:s');
		$ids_product = Product::_getProductIdByDate((!$beginning ? $current_date : $beginning), (!$ending ? $current_date : $ending), $context);

		$tab_id_product = array();
		foreach ($ids_product as $product)
			if (is_array($product))
				$tab_id_product[] = (int)$product['id_product'];
			else
				$tab_id_product[] = (int)$product;

		$front = true;
		if (!in_array($context->controller->controller_type, array('front', 'modulefront')))
			$front = false;

		$sql_groups = '';
		if (Group::isFeatureActive())
		{
			$groups = FrontController::getCurrentCustomerGroups();
			$sql_groups = 'AND p.`id_product` IN (
				SELECT cp.`id_product`
				FROM `'._DB_PREFIX_.'category_group` cg
				LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
				WHERE cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1').'
			)';
		}

		if ($count)
		{
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT COUNT(DISTINCT p.`id_product`)
			FROM `'._DB_PREFIX_.'product` p
			'.Shop::addSqlAssociation('product', 'p').'
			WHERE product_shop.`active` = 1
			AND product_shop.`show_price` = 1
			'.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
			'.((!$beginning && !$ending) ? 'AND p.`id_product` IN('.((is_array($tab_id_product) && count($tab_id_product)) ? implode(', ', $tab_id_product) : 0).')' : '').'
			'.$sql_groups);
		}
		
		if (strpos($order_by, '.') > 0)
		{
			$order_by = explode('.', $order_by);
			$order_by = pSQL($order_by[0]).'.`'.pSQL($order_by[1]).'`';
		}

		$sql = '
		SELECT
			p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, pl.*, 
			MAX(product_attribute_shop.id_product_attribute) id_product_attribute,
			MAX(image_shop.`id_image`) id_image, il.`legend`, m.`name` AS manufacturer_name,
			DATEDIFF(
				p.`date_add`,
				DATE_SUB(
					NOW(),
					INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
				)
			) > 0 AS new
		FROM `'._DB_PREFIX_.'product` p
		'.Shop::addSqlAssociation('product', 'p').'
		LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON (pa.id_product = p.id_product)
		'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.default_on=1').'
		'.Product::sqlStock('p', 0, false, $context->shop).'
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
			p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
		)
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
		Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
		WHERE product_shop.`active` = 1
		AND product_shop.`show_price` = 1
		'.($front ? ' AND p.`visibility` IN ("both", "catalog")' : '').'
		'.((!$beginning && !$ending) ? ' AND p.`id_product` IN ('.((is_array($tab_id_product) && count($tab_id_product)) ? implode(', ', $tab_id_product) : 0).')' : '').'
		'.$sql_groups.'
		GROUP BY product_shop.id_product
		ORDER BY '.(isset($order_by_prefix) ? pSQL($order_by_prefix).'.' : '').pSQL($order_by).' '.pSQL($order_way).'
		LIMIT '.(int)($page_number * $nb_products).', '.(int)$nb_products;

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		if (!$result)
			return false;

		if ($order_by == 'price')
			Tools::orderbyPrice($result, $order_way);

		return Product::getProductsProperties($id_lang, $result);
	}
	
	public static function getProductProperties($id_lang, $row, Context $context = null)
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* Standard Lieferzeit aus Datenbank ermitteln pl.*
		*/
		
		if (!$row['id_product'])
			return false;

		if ($context == null)
			$context = Context::getContext();

		// Product::getDefaultAttribute is only called if id_product_attribute is missing from the SQL query at the origin of it:
		// consider adding it in order to avoid unnecessary queries
		$row['allow_oosp'] = Product::isAvailableWhenOutOfStock($row['out_of_stock']);
		if (Combination::isFeatureActive() && (!isset($row['id_product_attribute']) || !$row['id_product_attribute'])
			&& ((isset($row['cache_default_attribute']) && ($ipa_default = $row['cache_default_attribute']) !== null)
				|| ($ipa_default = Product::getDefaultAttribute($row['id_product'], !$row['allow_oosp']))))
			$row['id_product_attribute'] = $ipa_default;
		if (!Combination::isFeatureActive() || !isset($row['id_product_attribute']))
			$row['id_product_attribute'] = 0;

		// Tax
		$usetax = Tax::excludeTaxeOption();

		$cache_key = $row['id_product'].'-'.$row['id_product_attribute'].'-'.$id_lang.'-'.(int)$usetax;
		if (isset($row['id_product_pack']))
			$cache_key .= '-pack'.$row['id_product_pack'];

		if (isset(self::$producPropertiesCache[$cache_key]))
			return array_merge($row, self::$producPropertiesCache[$cache_key]);

		// Datas
		$row['category'] = Category::getLinkRewrite((int)$row['id_category_default'], (int)$id_lang);
		$row['link'] = $context->link->getProductLink((int)$row['id_product'], $row['link_rewrite'], $row['category'], $row['ean13']);

		$row['attribute_price'] = 0;
		if (isset($row['id_product_attribute']) && $row['id_product_attribute'])
			$row['attribute_price'] = (float)Product::getProductAttributePrice($row['id_product_attribute']);

		$row['price_tax_exc'] = Product::getPriceStatic(
			(int)$row['id_product'],
			false,
			((isset($row['id_product_attribute']) && !empty($row['id_product_attribute'])) ? (int)$row['id_product_attribute'] : null),
			(self::$_taxCalculationMethod == PS_TAX_EXC ? 2 : 6)
		);

		if (self::$_taxCalculationMethod == PS_TAX_EXC)
		{
			$row['price_tax_exc'] = Tools::ps_round($row['price_tax_exc'], 2);
			$row['price'] = Product::getPriceStatic(
				(int)$row['id_product'],
				true,
				((isset($row['id_product_attribute']) && !empty($row['id_product_attribute'])) ? (int)$row['id_product_attribute'] : null),
				6
			);
			$row['price_without_reduction'] = Product::getPriceStatic(
				(int)$row['id_product'],
				false,
				((isset($row['id_product_attribute']) && !empty($row['id_product_attribute'])) ? (int)$row['id_product_attribute'] : null),
				2,
				null,
				false,
				false
			);
		}
		else
		{
			$row['price'] = Tools::ps_round(
				Product::getPriceStatic(
					(int)$row['id_product'],
					true,
					((isset($row['id_product_attribute']) && !empty($row['id_product_attribute'])) ? (int)$row['id_product_attribute'] : null),
					2
				),
				2
			);

			$row['price_without_reduction'] = Product::getPriceStatic(
				(int)$row['id_product'],
				true,
				((isset($row['id_product_attribute']) && !empty($row['id_product_attribute'])) ? (int)$row['id_product_attribute'] : null),
				6,
				null,
				false,
				false
			);
		}

		$row['reduction'] = Product::getPriceStatic(
			(int)$row['id_product'],
			(bool)$usetax,
			(int)$row['id_product_attribute'],
			6,
			null,
			true,
			true,
			1,
			true,
			null,
			null,
			null,
			$specific_prices
		);

		$row['specific_prices'] = $specific_prices;

		$row['quantity'] = Product::getQuantity(
			(int)$row['id_product'],
			0,
			isset($row['cache_is_pack']) ? $row['cache_is_pack'] : null
		);

		$row['quantity_all_versions'] = $row['quantity'];

		if ($row['id_product_attribute'])
			$row['quantity'] = Product::getQuantity(
				(int)$row['id_product'],
    			$row['id_product_attribute'],
			   isset($row['cache_is_pack']) ? $row['cache_is_pack'] : null
			);

		$row['id_image'] = Product::defineProductImage($row, $id_lang);
		$row['features'] = Product::getFrontFeaturesStatic((int)$id_lang, $row['id_product']);

		$row['attachments'] = array();
		if (!isset($row['cache_has_attachments']) || $row['cache_has_attachments'])
			$row['attachments'] = Product::getAttachmentsStatic((int)$id_lang, $row['id_product']);

		$row['virtual'] = ((!isset($row['is_virtual']) || $row['is_virtual']) ? 1 : 0);

		// Pack management
		$row['pack'] = (!isset($row['cache_is_pack']) ? Pack::isPack($row['id_product']) : (int)$row['cache_is_pack']);
		$row['packItems'] = $row['pack'] ? Pack::getItemTable($row['id_product'], $id_lang) : array();
		$row['nopackprice'] = $row['pack'] ? Pack::noPackPrice($row['id_product']) : 0;
		if ($row['pack'] && !Pack::isInStock($row['id_product']))
			$row['quantity'] = 0;

		$row = Product::getTaxesInformations($row, $context);
		
		/* Standard Lieferzeit aus Datenbank ermitteln */
		$row['delivery_now']   = !empty($row['delivery_now'])   ? $row['delivery_now']   : Configuration::get('GC_DELIVERY_NOW',   (int)$id_lang);
		$row['delivery_later'] = !empty($row['delivery_later']) ? $row['delivery_later'] : Configuration::get('GC_DELIVERY_LATER', (int)$id_lang);
		
		self::$producPropertiesCache[$cache_key] = $row;
		return self::$producPropertiesCache[$cache_key];
	}
	
}

