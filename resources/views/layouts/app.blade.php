<!DOCTYPE html>
<html lang="en">
  <head>
    @include('layouts/head')
  </head>
  
  <body>
    <nav class="navbar navbar-inverse bg-inverse navbar-toggleable-md">
      <div class="container">
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarsExampleContainer" aria-controls="navbarsExampleContainer" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand" href="#">{{ env('APP_NAME') }}</a>

        <div class="collapse navbar-collapse" id="navbarsExampleContainer">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
              <a class="nav-link" href="#">Profile <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Link</a>
            </li>
          </ul>
          @if (true)
          <span class="navbar-text">
            <span class="font-italic">Logged in as </span> {{'Ryan Strat'}}
          </span>
          @else
          <span class="navbar-text">
            <a href="">Login</a>
          </span>
          @endif
        </div>
      </div>
    </nav>

    <div class="container">
      @yield('content')
    </div>

    @include('layouts/footer')
  </body>
</html>