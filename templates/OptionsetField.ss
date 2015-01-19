<ul id="$ID" class="$extraClass" <% if $DisplayLogic %>data-display-logic-masters="$DisplayLogicMasters" data-display-logic-eval="$DisplayLogic"<% end_if %>>
	<% loop $Options %>
		<li class="$Class">
			<input id="$ID" class="radio" name="$Name" type="radio" value="$Value"<% if $isChecked %> checked<% end_if %><% if $isDisabled %> disabled<% end_if %> />
			<label for="$ID">$Title</label>
		</li>
	<% end_loop %>
</ul>