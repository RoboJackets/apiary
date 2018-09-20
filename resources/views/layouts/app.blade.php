<!DOCTYPE html>
<html lang="en">
  <head>
    @include('layouts/head')
  </head>
  
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      @inject('request', 'Illuminate\Http\Request')
      <div class="container">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <a class="navbar-brand" href="/">{{ config('app.name') }}</a>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item {{ $request->is('/') ? 'active' : '' }}">
              <a class="nav-link" href="/">Dashboard</a>
            </li>
            <li class="nav-item {{ $request->is('teams*') ? 'active' : '' }}">
              <a class="nav-link" href="{{ route('teams.index') }}">Teams</a>
            </li>

            @hasanyrole('admin|officer-i|officer-ii')
            <li class="nav-item dropdown {{ $request->is('admin*') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#" id="navbarAdminDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Admin
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarAdminDropdown">
                @hasanyrole('admin|officer-i')
                <a class="dropdown-item" href="/admin/users">Users</a>
                <a class="dropdown-item" href="/admin/events">Events</a>
                <a class="dropdown-item" href="/admin/recruiting">Recruiting</a>
                <a class="dropdown-item" href="{{ route('admin.teams.index') }}">Teams</a>
                <a class="dropdown-item" href="{{ route('admin.notification.templates.index') }}">Notification Templates</a>
                @endhasanyrole
                @hasanyrole('admin|officer-i|officer-ii')
                <a class="dropdown-item" href="/admin/dues">Dues</a>
                <a class="dropdown-item" href="/admin/dues/pending">Accept Dues</a>
                <a class="dropdown-item" href="/admin/swag">Swag</a>
                <a class="dropdown-item" href="/admin/swag/pending">Give Swag</a>
                <a class="dropdown-item" href="/admin/attendance">Attendance</a>
                @endhasanyrole
              </div>
            </li>
            @endhasanyrole

          </ul>

          @if (auth()->user())
            <ul class="navbar-nav">
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  {{auth()->user()->name}}
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <a class="dropdown-item" href="/profile">Profile</a>
                  <a class="dropdown-item" href="{{route('logout')}}">Logout</a>
                </div>
              </li>
            </ul>
          @else
            <span class="navbar-item">
              <a class="nav-link text-muted" href="{{'https://login.gatech.edu/cas/login?service=' . $request->fullUrl()}}">Login</a>
            </span>
          @endif
        </div>
      </div>
    </nav>

    <div class="container" id="app">
      @yield('content')
    </div>

    @include('layouts/footer')
  </body>
  <script src="{{ mix('/js/app.js') }}"></script>
  @if (Session::has('sweet_alert.alert'))
    <script>
        swal({!! Session::pull('sweet_alert.alert') !!});
    </script>
  @endif
</html>
