<?php

class OrderHistory extends OrderHistoryCore
{

	public function addWithemail($autodate = true, $template_vars = false, Context $context = null)
	{
		
		/*
		* Legal 0.0.1 | 20140320
		* Duplizieren der Bestellbestätigung (LEGAL_OCMAILDBL)
		*/
		
		if (!$context)
			$context = Context::getContext();
		$order = new Order($this->id_order);
		
		if (!$this->add($autodate))
			return false;

		$result = Db::getInstance()->getRow('
			SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`, os.`module_name`, os.`id_order_state`
			FROM `'._DB_PREFIX_.'order_history` oh
				LEFT JOIN `'._DB_PREFIX_.'orders` o ON oh.`id_order` = o.`id_order`
				LEFT JOIN `'._DB_PREFIX_.'customer` c ON o.`id_customer` = c.`id_customer`
				LEFT JOIN `'._DB_PREFIX_.'order_state` os ON oh.`id_order_state` = os.`id_order_state`
				LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = o.`id_lang`)
			WHERE oh.`id_order_history` = '.(int)$this->id.' AND os.`send_email` = 1');
		if (isset($result['template']) && Validate::isEmail($result['email']))
		{
			ShopUrl::cacheMainDomainForShop($order->id_shop);
			
			$topic = $result['osname'];
			$data = array(
				'{lastname}' => $result['lastname'],
				'{firstname}' => $result['firstname'],
				'{id_order}' => (int)$this->id_order,
				'{order_name}' => $order->getUniqReference()
			);
			if ($template_vars)
				$data = array_merge($data, $template_vars);

			if ($result['module_name'])
			{
				$module = Module::getInstanceByName($result['module_name']);
				if (Validate::isLoadedObject($module) && isset($module->extra_mail_vars) && is_array($module->extra_mail_vars))
					$data = array_merge($data, $module->extra_mail_vars);
			}
			
			$data['{total_paid}'] = Tools::displayPrice((float)$order->total_paid, new Currency((int)$order->id_currency), false);
			$data['{order_name}'] = $order->getUniqReference();

			if (Validate::isLoadedObject($order))
			{
				// Join PDF invoice if order state is "payment accepted"
				if ((int)$result['id_order_state'] === 2 && (int)Configuration::get('PS_INVOICE') && $order->invoice_number)
				{
					$context = Context::getContext();
					$pdf = new PDF($order->getInvoicesCollection(), PDF::TEMPLATE_INVOICE, $context->smarty);
					$file_attachement['content'] = $pdf->render(false);
					$file_attachement['name'] = Configuration::get('PS_INVOICE_PREFIX', (int)$order->id_lang, null, $order->id_shop).sprintf('%06d', $order->invoice_number).'.pdf';
					$file_attachement['mime'] = 'application/pdf';
				}
				else
					$file_attachement = null;

				Mail::Send((int)$order->id_lang, $result['template'], $topic, $data, $result['email'], $result['firstname'].' '.$result['lastname'],
					null, null, $file_attachement, null, _PS_MAIL_DIR_, false, (int)$order->id_shop);
				
				/* Duplizieren der Bestellbestätigung (LEGAL_OCMAILDBL) */
				if(Validate::isEmail(Configuration::getGlobalValue('LEGAL_OCMAILDBL')))
					Mail::Send(
						Configuration::get('PS_LANG_DEFAULT'), 
						$result['template'],
						$topic, 
						$data, 
						Configuration::getGlobalValue('LEGAL_OCMAILDBL'), 
						Configuration::get('PS_SHOP_NAME'), 
						null, 
						null, 
						$file_attachment, 
						null, 
						_PS_MAIL_DIR_, 
						false, 
						(int)$order->id_shop
					);
			}

			ShopUrl::resetMainDomainCache();
		}

		return true;
	}
	
}

