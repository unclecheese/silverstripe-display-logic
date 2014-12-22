<div $AttributesHTML>
    <% if $Options.Count %>
        <% if $HasColumns %>
            <div class="row">
                <div class="$ColumnClasses">
                <% loop $Options %>                    
                    <div class="checkbox <% if $Up.Inline %>inline<% end_if %>">
                        <label>
                            <input id="$ID" class="checkbox" name="$Name" type="checkbox" value="$Value"<% if $isChecked %> checked="checked"<% end_if %><% if $isDisabled %> disabled="disabled"<% end_if %>>
                            $Title
                        </label>
                    </div>
                    <% if $Up.HasColumns && $MultipleOf($Up.PerColumn) %></div><div class="$Up.ColumnClasses"><% end_if %>                                    
                <% end_loop %>
                </div>
            </div>
        <% else %>
            <% loop $Options %>
                <div class="checkbox <% if $Up.Inline %>inline<% end_if %>">
                    <label>
                        <input id="$ID" class="checkbox" name="$Name" type="checkbox" value="$Value"<% if $isChecked %> checked="checked"<% end_if %><% if $isDisabled %> disabled="disabled"<% end_if %>>
                        $Title
                    </label>
                </div>                
            <% end_loop %>
        <% end_if %>
    <% else %>
        <div>No options available</div>
    <% end_if %>
</div>