<!DOCTYPE html>
<html lang="en">
  <head>
    @include('layouts/head')
  </head>

  <body>
    @inject('request', 'Illuminate\Http\Request')
    @if (app(\Laravel\Nova\Contracts\ImpersonatesUsers::class)->impersonating($request))
    <div style="text-align: center; height: 3em; padding: 0.7em; background-color: #eed202;">
        <strong>You are impersonating another user.</strong>
        <span>This functionality should only be used when troubleshooting an issue.</span>
        <a href="{{ route('stopImpersonating') }}">Click here to stop impersonating.</a>
    </div>
    @endif
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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
            @if (config('features.resumes') && auth()->user() && auth()->user()->is_student)
            <li class="nav-item {{ $request->is('resume*') ? 'active' : '' }}">
              <a class="nav-link" href="{{ route('resume.index') }}">Resume</a>
            </li>
            @endif

            @if (auth()->user() && auth()->user()->assignments()->count() > 0)
            <li class="nav-item {{ $request->is('travel*') || $request->is('pay/travel*') || $request->is('sign/travel*') ? 'active' : '' }}">
              <a class="nav-link" href="{{ route('travel.index') }}">Travel<sub><small>&nbspbeta</small></sub></a>
            </li>
            @endif

            @can('access-nova')
            <li class="nav-item">
              <a class="nav-link" href="/nova/">Admin</a>
            </li>
            @endcan

            @can('access-horizon')
            <li class="nav-item">
              <a class="nav-link" href="/horizon/dashboard">Job Status</a>
            </li>
            @endcan

            @can('access-nova')
            <li class="nav-item">
              <a class="nav-link" href="/docs/">Docs</a>
            </li>
            @endcan

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
              <a class="nav-link text-muted" href="{{'https://'.config('cas.cas_hostname').'/cas/login?service=' . $request->fullUrl()}}">Login</a>
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
  @if (Session::has('alert.config'))
    <script>
        Swal.fire({!! Session::pull('alert.config') !!});
    </script>
  @endif
</html>
