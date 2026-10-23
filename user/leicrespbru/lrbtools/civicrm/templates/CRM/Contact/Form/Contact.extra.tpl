<div id="gp" class="crm-accordion-wrapper crm-lookup-accordion crm-accordion-open">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
        GP information
  </div><!-- /.crm-accordion-header -->
  <div id="gp_link" class="crm-accordion-body">
<table class="form-layout-compressed">
   <tr>
    	<td>
    	{$form.gp_link_id.label}<br/>
        {$form.gp_link_id.html}
    	</td>
    </tr>
   
    </table>
</div>
{literal}
<script>
cj(document).ready(function() {
	cj('#customData').before(cj('#gp'));
});
</script>
{/literal}
{php}
$moduleList = module_list();
if (!array_key_exists('patientlookup',$moduleList)) return;
{/php}

{if count($form.contact_sub_type.value)>0 && in_array('Recruit',$form.contact_sub_type.value)}
{literal}
<script>
cj(document).ready(function() {
	cj('.crm-contactDetails-accordion').before(cj('#patientLookup'));
});
</script>
{/literal}

<div id="patientLookup" class="crm-accordion-wrapper crm-lookup-accordion crm-accordion-open">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
        Patient Lookup
  </div><!-- /.crm-accordion-header -->
  <div id="lookup" class="crm-accordion-body">
<table class="form-layout-compressed">
<tr style="width:100%"><td colspan="3"><b>Lookup patients using hospital number/S number:</b></td></tr>
   <tr>
    	<td colspan="3">
    	{$form.lookup_identifier.label}<br/>
        {$form.lookup_identifier.html}
    	</td>
    </tr>
    <tr style="width:100%">
    <td colspan="3">
    <b>OR patient name and date of birth:</b>
    </td>
    </tr>
     <tr>
    	<td>
    	{$form.lookup_first_name.label}<br/>
        {$form.lookup_first_name.html}
    	</td>
		<td>
    	{$form.lookup_last_name.label}<br/>
        {$form.lookup_last_name.html}
    	</td>
    	<td>
    	{$form.lookup_date_of_birth.label}<br/>
        {$form.lookup_date_of_birth.html}
    	</td>
    </tr>
    <tr>
    <td>
    {$form.lookup_button.html}
    </td>
    </tr>
    
    </table>
</div>
{/if}

