<div id="$Name" class="field<% if extraClass %> $extraClass<% end_if %>" <% if DisplayLogic %>data-display-logic-masters="$DisplayLogicMasters"<% end_if %>>
	<% if Title %><label class="left" for="$ID">$Title</label><% end_if %>
	<div class="middleColumn">
		$Field
	</div>
	<% if RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
	<% if Message %><span class="message $MessageType">$Message</span><% end_if %>
    <% if $Description %><span class="description">$Description</span><% end_if %>

	<% if DisplayLogic %>
	<div class="display-logic-eval">$DisplayLogic</div>
	<% end_if %>
</div>
