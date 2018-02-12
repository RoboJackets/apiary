<!DOCTYPE html>
<html lang="en">
  <head>
    <title>FASET Form | MyRoboJackets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">

    @include('favicon')
  </head>
  <body id="faset">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <img class="mt-2 px-5 img-fluid" src="{{ asset('img/faset_form_header.svg') }}">
          <hr class="my-4">
        </div>
      </div>

      <div class="row justify-content-center">
        <form id="form" class="col-sm-8" v-on:submit.prevent="submit">
          <div class="form-group">
            <label for="faset-name">Name</label>
            <input type="text" class="form-control" id="faset-name" name="faset-name" autofocus placeholder="George Burdell" autocomplete="off" required>
            <small class="form-text text-muted">First and last name</small>
          </div>

          <div class="form-group">
            <label for="faset-email">Email</label>
            <input type="email" class="form-control" id="faset-email" name="faset-email" placeholder="example@gatech.edu" autocomplete="off" required>
          </div>

          <fieldset class="form-group">
            <label for="heardfrom">How did you hear about RoboJackets?</label>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="heardfrom-faset" name="heardfrom" value="faset">
              <label class="custom-control-label" for="heardfrom-faset">FASET</label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="heardfrom-tour" name="heardfrom" value="tour">
              <label class="custom-control-label" for="heardfrom-tour">Campus tour</label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="heardfrom-member" name="heardfrom" value="member">
              <label class="custom-control-label" for="heardfrom-member">From another member</label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="heardfrom-nonmember" name="heardfrom" value="nonmember">
              <label class="custom-control-label" for="heardfrom-nonmember">From a friend not in RoboJackets</label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="heardfrom-social_media" name="heardfrom" value="social_media">
              <label class="custom-control-label" for="heardfrom-social_media">Social Media (Facebook, Twitter, Youtube, etc.)</label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="heardfrom-website" name="heardfrom" value="website">
              <label class="custom-control-label" for="heardfrom-website">Website (RoboJackets.org)</label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="heardfrom-frc" name="heardfrom" value="frc">
              <label class="custom-control-label" for="heardfrom-frc">FRC Event</label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="heardfrom-ftc" name="heardfrom" value="ftc">
              <label class="custom-control-label" for="heardfrom-ftc">FTC Event</label>
            </div>
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="heardfrom-vex" name="heardfrom" value="vex">
              <label class="custom-control-label" for="heardfrom-vex">Vex Event</label>
            </div>
          </fieldset>

          <div class="form-group">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>

    <script src="{{ mix('/js/app.js') }}"></script>
    <script src="/js/faset/vue.js"></script>
  </body>
</html>

