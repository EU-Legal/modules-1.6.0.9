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

class CMS extends CMSCore
{
	public static function getContentFromId($id_cms, $id_lang = null)
	{
		/*
		* EU-Legal
		* own function: returns content from CMS ID
		*/

		if (!Validate::isUnsignedInt($id_cms))
			return null;

		if (empty($id_lang))
			$id_lang = Context::getContext()->cookie->id_lang;

		$cms = new CMS((int)$id_cms, (int)$id_lang);

		if (Validate::isLoadedObject($cms))
			return $cms->content;
		else
			return '';

	}

}
