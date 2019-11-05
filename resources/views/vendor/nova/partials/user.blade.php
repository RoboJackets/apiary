<dropdown-trigger class="h-9 flex items-center">
    <span class="text-90">
        {{ auth()->user()->name }}
    </span>
</dropdown-trigger>

<dropdown-menu slot="menu" width="100" direction="rtl">
    <ul class="list-reset">
        <li>
            <a href="{{ config('nova.path') }}/resources/users/{{ auth()->user()->id }}" class="block no-underline text-90 hover:bg-30 p-3">
                Profile
            </a>
            <a href="{{ route('nova.logout') }}" class="block no-underline text-90 hover:bg-30 p-3">
                {{ __('Logout') }}
            </a>
        </li>
    </ul>
</dropdown-menu>
