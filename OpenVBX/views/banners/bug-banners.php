<?php if((OpenVBX::getTwilioAccountType() == 'Trial') && (1 == 0)): /* message disabled as we use Twilio subaccounts only which can not be trial */ ?>
<div id="upgrade-account" class="shout-out">
	<p>You are using a Twilio Free Trial Account.  <a href="https://www.twilio.com/user/billing/add-funds">Upgrade your Twilio account</a> to buy your own phone numbers and make outbound calls.</p>
</div><!-- #upgrade-account .shout-out -->
<?php endif; ?>