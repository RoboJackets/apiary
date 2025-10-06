<!DOCTYPE html>
<html lang="en">
  <head>
    @include('layouts/head')
  </head>

  <body>
    @inject('request', 'Illuminate\Http\Request')

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
