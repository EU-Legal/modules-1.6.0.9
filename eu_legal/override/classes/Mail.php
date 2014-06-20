<?php
class Mail extends MailCore
{
	public static function Send($id_lang, $template, $subject, $template_vars, $to, $to_name = null, $from = null, $from_name = null, $file_attachment = null, $mode_smtp = null, $template_path = _PS_MAIL_DIR_, $die = false, $id_shop = null, $bcc = null)
	{	
		/*
		* Legal 0.0.1 | 20140320
		* CMS Seiten für alle Emails verfügbar machen, sowohl als HTML als auch als TXT
		* Duplizieren der Bestellbestätigung (LEGAL_OCMAILDBL)
		*/
		
		$additional_cms = array(
			'conditions'    => 'PS_CONDITIONS_CMS_ID',    // Legal: AGB
			'revocation'    => 'LEGAL_CMS_ID_REVOCATION',    // Legal: Widerrufsrecht
			'revocationform'=> 'LEGAL_CMS_ID_REVOCATIONFORM',    // Legal: Widerrufsformular
			'privacy'       => 'LEGAL_CMS_ID_PRIVACY',       // Legal: Datenschutz
			'environmental' => 'LEGAL_CMS_ID_ENVIRONMENTAL', // Legal: Umweltverordnung
			'legal'         => 'LEGAL_CMS_ID_LEGAL' // Legal: Impressum
		);
		
		$type = Configuration::get('PS_MAIL_TYPE');
		
		foreach($additional_cms as $key => $row) {
			
			$html = CMS::getContentFromId(Configuration::get($row), (int)$id_lang);
			
			if($type == Mail::TYPE_BOTH or $type == Mail::TYPE_HTML)
				$template_vars['{cms_'.$key.'}'] = $html;
			if($type == Mail::TYPE_BOTH or $type == Mail::TYPE_TEXT)
				$template_vars['{cms_'.$key.'_txt}'] = strip_tags($html);
			
		}
		
		return parent::Send(
			$id_lang, 
			$template, 
			$subject, 
			$template_vars, 
			$to, 
			$to_name, 
			$from, 
			$from_name, 
			$file_attachment, 
			$mode_smtp, 
			$template_path, 
			$die, 
			$id_shop,
			$bcc
		);
		
	}
	
}