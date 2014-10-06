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

class AuthController extends AuthControllerCore
{
	protected function sendConfirmationMail(Customer $customer)
	{
		/*
		* EU-Legal
		* Password not visible
		*/

		if (!Configuration::get('PS_CUSTOMER_CREATION_EMAIL'))
			return true;

		return Mail::Send(
			$this->context->language->id,
			'account',
			Mail::l('Welcome!'),
			array(
				'{firstname}' => $customer->firstname,
				'{lastname}' => $customer->lastname,
				'{email}' => $customer->email,
				'{passwd}' => '***'),
			$customer->email,
			$customer->firstname.' '.$customer->lastname
		);
	}

}