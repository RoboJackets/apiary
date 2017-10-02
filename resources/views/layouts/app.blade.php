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
            <li class="nav-item {{ $request->is('profile*') ? 'active' : '' }}">
              <a class="nav-link" href="/profile">Profile</a>
            </li>

            @role('admin')
            <li class="nav-item dropdown {{ $request->is('admin*') ? 'active' : '' }}">
              <a class="nav-link dropdown-toggle" href="#" id="navbarAdminDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Admin
              </a>
              <div class="dropdown-menu" aria-labelledby="navbarAdminDropdown">
                <a class="dropdown-item" href="/admin/users">Users</a>
                <a class="dropdown-item" href="/admin/events">Events</a>
                <a class="dropdown-item" href="/admin/faset">Faset</a>
                <a class="dropdown-item" href="/admin/dues">Dues</a>
              </div>
            </li>
            @endrole

          </ul>

          @if (auth()->user())
          <span class="navbar-text">
            <span class="font-italic">Logged in as </span> {{auth()->user()->name}}
          </span>
          @else
          <span class="navbar-text">
            <a href="">Login</a>
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
</html>