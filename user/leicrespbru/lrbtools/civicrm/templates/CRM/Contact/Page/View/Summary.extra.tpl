{literal}
<script>
cj(document).ready(function() {
	cj('.contact_details').insertBefore(cj('.contactTopBar'));
	cj('#customFields').insertBefore(cj('.contactTopBar'));
	cj('.crm-contact-preferred_mail_format').parent().hide();
	cj('.crm-contact-email_greeting_display').parent().hide();
	cj('.crm-contact-postal_greeting_display').parent().hide();
	cj('.crm-contact-addressee_display').parent().hide();
	
});
</script>
{/literal}