<?php
class AuthController extends AuthControllerCore
{
	protected function sendConfirmationMail(Customer $customer) {
		
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