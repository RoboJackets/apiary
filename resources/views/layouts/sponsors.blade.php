<!DOCTYPE html>
<html lang="en">
  <head>
    @include('layouts/head')
  </head>

  <body class="vh-100 d-flex flex-column overflow-hidden" style="min-height: 0;">
    @inject('request', 'Illuminate\Http\Request')
    <div class="d-flex flex-column flex-grow-1 overflow-hidden" id="app" style="min-height: 0;">
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
