<!DOCTYPE html>
<html lang="en">
  <head>
    <title>FASET Form | MyRoboJackets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
  </head>
  <body id="faset">
    <div class="container">
      <div class="jumbotron jumbotron-fluid">
        <div class="container">
          <div class="row">
            <div class="col-md-8 offset-md-2">
              <img class="img-fluid" src="{{ asset('img/faset_form_header.svg') }}">
            </div>
          </div>
        </div>
      </div>

      <form id="form" class="col-sm-8 offset-sm-2" v-on:submit.prevent="submit">
        <div class="form-group">
          <label for="faset-name">Name</label>
          <input type="text" class="form-control" id="faset-name" name="faset-name" placeholder="George Burdell" autocomplete="off">
          <small class="form-text text-muted">First and last name</small>
        </div>

        <div class="form-group">
          <label for="faset-email">Email</label>
          <input type="email" class="form-control form-control-warning" id="faset-email" name="faset-email" placeholder="example@gatech.edu" autocomplete="off">
        </div>

        <fieldset class="form-group">
          <legend>How did you hear about RoboJackets?</legend>
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
    <script src="https://unpkg.com/vue"></script>
    <script src="/js/faset/vue.js"></script>
    <script src="{{ mix('/js/app.js') }}"></script>
  </body>
</html>

