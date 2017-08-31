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
            <div class="custom-controls-stacked">
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="heardfrom" value="faset">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">FASET</span>
              </label>
            </div>
            <div class="custom-controls-stacked">
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="heardfrom" value="tour">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">Campus tour</span>
              </label>
            </div>
            <div class="custom-controls-stacked">
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="heardfrom" value="member">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">From another member</span>
              </label>
            </div>
            <div class="custom-controls-stacked">
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="heardfrom" value="nonmember">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">From a friend not in RoboJackets</span>
              </label>
            </div>
            <div class="custom-controls-stacked">
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="heardfrom" value="social_media">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">Social Media (Facebook, Twitter, Youtube, etc.)</span>
              </label>
            </div>
            <div class="custom-controls-stacked">
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="heardfrom" value="website">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">Website (RoboJackets.org)</span>
              </label>
            </div>
            <div class="custom-controls-stacked">
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="heardfrom" value="frc">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">FRC Event</span>
              </label>
            </div>
            <div class="custom-controls-stacked">
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="heardfrom" value="ftc">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">FTC Event</span>
              </label>
            </div>
            <div class="custom-controls-stacked">
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="heardfrom" value="vex">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">Vex Event</span>
              </label>
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

