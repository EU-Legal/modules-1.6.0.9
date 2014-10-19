{**
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
*}
{* fancybox specific js code *}
{if $show_fancy }
	{literal}
		<script type="text/javascript">
		$(document).ready(function() {
			$("a.iframeEULegal").fancybox({
				'type' : 'iframe',
				'width':600,
				'height':600
			});
			});
			
		</script>
	{/literal}
{/if}
