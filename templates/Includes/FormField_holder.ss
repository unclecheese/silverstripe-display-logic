<div id="$Name" class="field<% if extraClass %> $extraClass<% end_if %>" <% if DisplayLogic %>data-display-logic-masters="$DisplayLogicMasters"<% end_if %>>
	<% if Title %><label class="left" for="$ID">$Title</label><% end_if %>
	<div class="middleColumn">
		$Field
	</div>
	<% if RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
	<% if Message %><span class="message $MessageType">$Message</span><% end_if %>

	<% if DisplayLogic %>
	<script type="text/template" class="display-logic-eval">$DisplayLogic</script>
	<% end_if %>
</div>