<?php
/**
 * EU Legal - Better security for German and EU merchants.
 *
 * @version   : 1.0.2
 * @date      : 2014 08 26
 * @author    : Markus Engel/Chris Gurk @ Onlineshop-Module.de | George June/Alexey Dermenzhy @ Silbersaiten.de
 * @copyright : 2014 Onlineshop-Module.de | 2014 Silbersaiten.de
 * @contact   : info@onlineshop-module.de | info@silbersaiten.de
 * @homepage  : www.onlineshop-module.de | www.silbersaiten.de
 * @license   : http://opensource.org/licenses/osl-3.0.php
 * @changelog : see changelog.txt
 * @compatibility : PS == 1.6.0.9
 */

class ProductSale extends ProductSaleCore
{
	public static function getBestSales($id_lang, $page_number = 0, $nb_products = 10, $order_by = null, $order_way = null)
	{
		/*
		* EU-Legal
		* get standard shipping time from database pl.*
		*/
		
		if ($page_number < 0) $page_number = 0;
		if ($nb_products < 1) $nb_products = 10;
		$final_order_by = $order_by;
		$order_table = ''; 		
		if (is_null($order_by) || $order_by == 'position' || $order_by == 'price') $order_by = 'sales';
		if ($order_by == 'date_add' || $order_by == 'date_upd')
			$order_table = 'product_shop'; 				
		if (is_null($order_way) || $order_by == 'sales') $order_way = 'DESC';

		$sql_groups = '';
		if (Group::isFeatureActive())
		{
			$groups = FrontController::getCurrentCustomerGroups();
			$sql_groups = 'WHERE cp.`id_product` IS NOT NULL AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
		}
		$interval = Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20;

		// Subquery: get product ids in a separate query to (greatly!) improve performances and RAM usage
		$products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT cp.`id_product`
		FROM `'._DB_PREFIX_.'category_group` cg
		INNER JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
		'.$sql_groups);
	
		$ids = array();
		foreach ($products as $product)
			if (Validate::isUnsignedId($product['id_product']))
				$ids[$product['id_product']] = 1;
		$ids = array_keys($ids);
		$ids = array_filter($ids);
		sort($ids);
		$ids = count($ids) > 0 ? implode(',', $ids) : 'NULL';
		
		//Main query
		/* 
		* EU-Legal
		* get standard shipping time from database pl.* 
		*/
		$sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity,
					pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`,
					pl.`meta_keywords`, pl.`meta_title`, pl.`name`, pl.`available_now`, pl.`available_later`, pl.`delivery_now`, pl.`delivery_later`,
					m.`name` AS manufacturer_name, p.`id_manufacturer` as id_manufacturer,
					MAX(image_shop.`id_image`) id_image, il.`legend`,
					ps.`quantity` AS sales, t.`rate`, pl.`meta_keywords`, pl.`meta_title`, pl.`meta_description`,
					DATEDIFF(p.`date_add`, DATE_SUB(NOW(),
					INTERVAL '.$interval.' DAY)) > 0 AS new
				FROM `'._DB_PREFIX_.'product_sale` ps
				LEFT JOIN `'._DB_PREFIX_.'product` p ON ps.`id_product` = p.`id_product`
				'.Shop::addSqlAssociation('product', 'p', false).'
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
					ON p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
				Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
				LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (product_shop.`id_tax_rules_group` = tr.`id_tax_rules_group`)
					AND tr.`id_country` = '.(int)Context::getContext()->country->id.'
					AND tr.`id_state` = 0
				LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
				'.Product::sqlStock('p').'
				WHERE product_shop.`active` = 1
					AND p.`visibility` != \'none\'
					AND p.`id_product` IN ('.$ids.')
				GROUP BY product_shop.id_product
				ORDER BY '.(!empty($order_table) ? '`'.pSQL($order_table).'`.' : '').'`'.pSQL($order_by).'` '.pSQL($order_way).'
				LIMIT '.(int)($page_number * $nb_products).', '.(int)$nb_products;

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		if ($final_order_by == 'price')
			Tools::orderbyPrice($result, $order_way);
		if (!$result)
			return false;
		return Product::getProductsProperties($id_lang, $result);
	}

	public static function getBestSalesLight($id_lang, $page_number = 0, $nb_products = 10, Context $context = null)
	{
		if (!$context)
			$context = Context::getContext();
		if ($page_number < 0) $page_number = 0;
		if ($nb_products < 1) $nb_products = 10;

		$sql_groups = '';
		if (Group::isFeatureActive())
		{
			$groups = FrontController::getCurrentCustomerGroups();
			$sql_groups = 'AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
		}

		//Subquery: get product ids in a separate query to (greatly!) improve performances and RAM usage
		$products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT cp.`id_product`
		FROM `'._DB_PREFIX_.'category_product` cp
		LEFT JOIN `'._DB_PREFIX_.'category_group` cg ON (cg.`id_category` = cp.`id_category`)
		WHERE cg.`id_group` '.$sql_groups);
		
		$ids = array();
		foreach ($products as $product)
			$ids[$product['id_product']] = 1;

		$ids = array_keys($ids);		
		sort($ids);
		$ids = count($ids) > 0 ? implode(',', $ids) : 'NULL';

		//Main query
		$sql = '
		SELECT
			p.id_product,  MAX(product_attribute_shop.id_product_attribute) id_product_attribute, pl.`link_rewrite`, pl.`name`, pl.`description_short`, pl.`delivery_now`, pl.`delivery_later`, product_shop.`id_category_default`,
			MAX(image_shop.`id_image`) id_image, il.`legend`,
			ps.`quantity` AS sales, p.`ean13`, p.`upc`, cl.`link_rewrite` AS category, p.show_price, p.available_for_order, IFNULL(stock.quantity, 0) as quantity, p.customizable,
			IFNULL(pa.minimal_quantity, p.minimal_quantity) as minimal_quantity, stock.out_of_stock,
			product_shop.`date_add` > "'.date('Y-m-d', strtotime('-'.(Configuration::get('PS_NB_DAYS_NEW_PRODUCT') ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY')).'" as new
		FROM `'._DB_PREFIX_.'product_sale` ps
		LEFT JOIN `'._DB_PREFIX_.'product` p ON ps.`id_product` = p.`id_product`
		'.Shop::addSqlAssociation('product', 'p').'
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa
			ON (p.`id_product` = pa.`id_product`)
		'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1').'
		'.Product::sqlStock('p', 'product_attribute_shop', false, $context->shop).'
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
			ON p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
		Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
			ON cl.`id_category` = product_shop.`id_category_default`
			AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').'
		WHERE product_shop.`active` = 1
		AND p.`visibility` != \'none\'
		AND p.`id_product` IN ('.$ids.')
		GROUP BY product_shop.id_product
		ORDER BY sales DESC
		LIMIT '.(int)($page_number * $nb_products).', '.(int)$nb_products;

		if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql))
			return false;

		return Product::getProductsProperties($id_lang, $result);
	}
	
}