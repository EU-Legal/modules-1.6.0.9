<form action="https://www.moneybookers.com/app/payment.pl" method="post">
    @hiddenSubmit
    <input type="hidden" name="pay_to_email" value="{$pay_to_email}" />
    <input type="hidden" name="recipient_description" value="{$recipient_description}" />
    <input type="hidden" name="transaction_id" value="{$transaction_id}" />
    <input type="hidden" name="return_url" value="{$return_url}" />
    <input type="hidden" name="return_url_text" value="{$return_url}" />
    <input type="hidden" name="cancel_url" value="{$return_url}" />
    <input type="hidden" name="status_url" value="{$status_url}" />
    <input type="hidden" name="status_url2" value="{$pay_to_email}" />
    <input type="hidden" name="language" value="{$language}" />
    <input type="hidden" name="hide_login" value="{$hide_login}" />
    <input type="hidden" name="pay_from_email" value="{$pay_from_email}" />
    <input type="hidden" name="firstname" value="{$firstname}" />
    <input type="hidden" name="lastname" value="{$lastname}" />
    {if (!empty($date_of_birth))}<input type="hidden" name="date_of_birth" value="{$date_of_birth}" />{/if}
    <input type="hidden" name="address" value="{$address}" />
    {if (!empty($address2))}<input type="hidden" name="address2" value="{$address2}" />{/if}
    {if (!empty($phone_number))}<input type="hidden" name="phone_number" value="{$phone_number}" />{/if}
    <input type="hidden" name="postal_code" value="{$postal_code}" />
    <input type="hidden" name="city" value="{$city}" />
    {if isset($state) && (!empty($state))}<input type="hidden" name="state" value="{$state}" />{/if}
    <input type="hidden" name="country" value="{$country}" />
    <input type="hidden" name="amount" value="{$amount}" />
    <input type="hidden" name="currency" value="{$currency}" />
    <input type="hidden" name="amount2_description" value="{if isset($amount2_description)}{$amount2_description}{/if}" />
    <input type="hidden" name="amount2" value="{if isset($amount2)}{$amount2}{/if}" />
    <input type="hidden" name="amount3_description" value="{if isset($amount3_description)}{$amount3_description}{/if}" />
    <input type="hidden" name="amount3" value="{if isset($amount3)}{$amount3}{/if}" />
    <input type="hidden" name="amount4_description" value="{if isset($amount4_description)}{$amount4_description}{/if}" />
    <input type="hidden" name="amount4" value="{if isset($amount4)}{$amount4}{/if}" />
    <input type="hidden" class="payment_methods" name="payment_methods" value="{$code}">
    <input type="hidden" name="return_url_target" value="2">
    <input type="hidden" name="cancel_url_target" value="2">
    <input type="hidden" name="merchant_fields" value="platform">
    <input type="hidden" name="platform" value="21445510">
</form>