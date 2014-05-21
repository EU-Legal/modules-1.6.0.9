<?php

class Supplier extends SupplierCore
{

	public static function getProducts($id_supplier, $id_lang, $p, $n,
		$order_by = null, $order_way = null, $get_total = false, $active = true, $active_category = true)
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* Standard Lieferzeit aus Datenbank ermitteln pl.*
		*/
		
		$context = Context::getContext();
		$front = true;
		if (!in_array($context->controller->controller_type, array('front', 'modulefront')))
			$front = false;

		if ($p < 1) $p = 1;
	 	if (empty($order_by) || $order_by == 'position') $order_by = 'name';
	 	if (empty($order_way)) $order_way = 'ASC';

		if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
			die (Tools::displayError());

		$sql_groups = '';
		if (Group::isFeatureActive())
		{
			$groups = FrontController::getCurrentCustomerGroups();
			$sql_groups = 'WHERE cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
		}

		/* Return only the number of products */
		if ($get_total)
			return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT COUNT(DISTINCT ps.`id_product`)
			FROM `'._DB_PREFIX_.'product_supplier` ps
			JOIN `'._DB_PREFIX_.'product` p ON (ps.`id_product`= p.`id_product`)
			'.Shop::addSqlAssociation('product', 'p').'
			WHERE ps.`id_supplier` = '.(int)$id_supplier.'
			AND ps.id_product_attribute = 0
			'.($active ? ' AND product_shop.`active` = 1' : '').'
			'.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
			AND p.`id_product` IN (
				SELECT cp.`id_product`
				FROM `'._DB_PREFIX_.'category_product` cp
				'.(Group::isFeatureActive() ? 'LEFT JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.`id_category` = cg.`id_category`)' : '').'
				'.($active_category ? ' INNER JOIN `'._DB_PREFIX_.'category` ca ON cp.`id_category` = ca.`id_category` AND ca.`active` = 1' : '').'
				'.$sql_groups.'
			)');

		$nb_days_new_product = Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20;

		if (strpos('.', $order_by) > 0)
		{
			$order_by = explode('.', $order_by);
			$order_by = pSQL($order_by[0]).'.`'.pSQL($order_by[1]).'`';
		}
		$alias = '';
		if (in_array($order_by, array('price', 'date_add', 'date_upd')))
			$alias = 'product_shop.';
		elseif ($order_by == 'id_product')
			$alias = 'p.';
		elseif ($order_by == 'manufacturer_name')
		{
			$order_by = 'name';
			$alias = 'm.';
		}
		
		/* Standard Lieferzeit aus Datenbank ermitteln pl.* */
		$sql = 'SELECT p.*, product_shop.*, stock.out_of_stock,
					IFNULL(stock.quantity, 0) as quantity,
					pl.`description`,
					pl.`description_short`,
					pl.`link_rewrite`,
					pl.`meta_description`,
					pl.`meta_keywords`,
					pl.`meta_title`,
					pl.`name`,
					pl.`delivery_now`, pl.`delivery_later`,
					MAX(image_shop.`id_image`) id_image,
					il.`legend`,
					s.`name` AS supplier_name,
					DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.($nb_days_new_product).' DAY)) > 0 AS new,
					m.`name` AS manufacturer_name
				FROM `'._DB_PREFIX_.'product` p
				'.Shop::addSqlAssociation('product', 'p').'
				JOIN `'._DB_PREFIX_.'product_supplier` ps ON (ps.id_product = p.id_product
					AND ps.id_product_attribute = 0)
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').')
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
				Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image`
					AND il.`id_lang` = '.(int)$id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'supplier` s ON s.`id_supplier` = p.`id_supplier`
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
				'.Product::sqlStock('p').'
				WHERE ps.`id_supplier` = '.(int)$id_supplier.'
					'.($active ? ' AND product_shop.`active` = 1' : '').'
					'.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
					AND p.`id_product` IN (
						SELECT cp.`id_product`
						FROM `'._DB_PREFIX_.'category_product` cp
						'.(Group::isFeatureActive() ? 'LEFT JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.`id_category` = cg.`id_category`)' : '').'
						'.($active_category ? ' INNER JOIN `'._DB_PREFIX_.'category` ca ON cp.`id_category` = ca.`id_category` AND ca.`active` = 1' : '').'
						'.$sql_groups.'
					)
				GROUP BY product_shop.id_product
				ORDER BY '.$alias.pSQL($order_by).' '.pSQL($order_way).'
				LIMIT '.(((int)$p - 1) * (int)$n).','.(int)$n;

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		if (!$result)
			return false;

		if ($order_by == 'price')
			Tools::orderbyPrice($result, $order_way);

		return Product::getProductsProperties($id_lang, $result);
	}
	
}

