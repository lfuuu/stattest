<H2>Настройки агента <b style="color: blue;">{$sale_channel}</b></H2>
<FORM action="?" method=post>
	<input type=hidden name=module value=stats>
	<input type=hidden name=action value=save_agent_settings>
	<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
		<TBODY>
			<TR>
				<TD class=left>
					Вид вознагрождения
				</TD>
				<td>
					{foreach from=$interests_types item="i" key="k"}
						<div>
							<input onchange="change_interest_type('{$k}');" type="radio" value="{$k}" name="interest_type" id="type_{$k}" {if $k == $interest->interest}checked{/if}>
							<label for="type_{$k}">{$i.name}</label>
						</div>
					{/foreach}
				</td>
			</tr>
			<tr>
				<td class="left">
					Вознагождение в %
				</td>
				<td>
					{foreach from=$interests_types item="i" key="k"}
						<div class="interest" id="interest_{$k}" {if $k != $interest->interest}style="display: none;"{/if}>
							{foreach from=$i.subtypes item="s_type" key="type"}
								<div>
									{assign var="key" value="`$k`_`$type`"}
									{assign var="field_name" value=$s_type.field_name}
									<input id="interest_{$k}_{$type}" style="width: 50px; font-size: 10px;" type="text" value="{$interest->$field_name}" name="interest[{$field_name}]">
									<label for="interest_{$k}_{$type}">{$s_type.name}</label>
								</div>
							{/foreach}
						</div>
					{/foreach}
				</td>
			</tr>
		</TBODY>
       </TABLE>
      <HR>

      <DIV align=center><INPUT class=button type=submit value="Сохранить"></DIV>
</FORM>
{literal}
	<script>
		function change_interest_type(type)
		{
			$('.interest').hide();
			$('#interest_' + type).show();
		}
	</script>
{/literal}