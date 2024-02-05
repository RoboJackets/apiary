@if(request()->user()->hasRole('admin') && request()->user()->can('update-airfare-policy'))
    The airfare policy can only be changed by users with the <a href="{{ route('nova.pages.detail', ['resource' => \Vyuldashev\NovaPermission\Permission::uriKey(), 'resourceId' => \Spatie\Permission\Models\Permission::where('name', '=', 'update-airfare-policy')->sole()->id]) }}" target="_blank">update-airfare-policy</a> permission.
@elseif(request()->user()->hasRole('admin'))
    You do not have permission to change the airfare policy. The airfare policy can only be changed by users with the <a href="{{ route('nova.pages.detail', ['resource' => \Vyuldashev\NovaPermission\Permission::uriKey(), 'resourceId' => \Spatie\Permission\Models\Permission::where('name', '=', 'update-airfare-policy')->sole()->id]) }}" target="_blank">update-airfare-policy</a> permission.
@elseif(request()->user()->hasRole('officer'))
    You do not have permission to change the airfare policy. If you need access, ask in <a href="https://robojackets.slack.com/app_redirect?channel=it-helpdesk" target="_blank">#it-helpdesk</a>.
@elseif(str_contains(request()->path(), 'update-fields'))
    The airfare policy for this trip can be changed by an officer, if needed.
@else
    The airfare policy for this trip can be changed by an officer after creating the trip, if needed.
@endif
