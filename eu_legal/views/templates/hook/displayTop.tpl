
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
