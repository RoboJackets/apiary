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

      <form id="form" v-on:submit.prevent="submit">
        <div class="form-group row">
          <label for="faset-name" class="offset-sm-2 col-sm-2 col-form-label">Name</label>
          <div class="col-sm-6">
            <input type="text" class="form-control" id="faset-name" name="faset-name" placeholder="George Burdell">
            <small class="form-text text-muted">First and last name</small>
          </div>
        </div>

        <div class="form-group row">
          <label for="faset-email" class="offset-sm-2 col-sm-2 col-form-label">Email</label>
          <div class="col-sm-6">
            <input type="email" class="form-control form-control-warning" id="faset-email" name="faset-email" placeholder="example@gatech.edu">
          </div>
        </div>

        <fieldset class="form-group row">
          <legend class="col-form-legend offset-sm-2 col-sm-8">How did you hear about RoboJackets?</legend>
          <div class="offset-sm-4 col-sm-6">
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="heardfrom" value="faset">
                FASET
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="heardfrom" value="tour">
                Campus tour
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="heardfrom" value="member">
                From a current member
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="heardfrom" value="nonmember">
                From friend not in RoboJackets
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="heardfrom" value="social_media">
                Social Media (Facebook, Twitter, Youtube, etc.)
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="heardfrom" value="website">
                Website (RoboJackets.org)
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="heardfrom" value="frc">
                FRC Event
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="heardfrom" value="ftc">
                FTC Event
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox" name="heardfrom" value="vex">
                Vex Event
              </label>
            </div>
          </fieldset>
          <div class="form-group row">
            <div class="offset-sm-2 col-sm-10">
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
          </div>
        </div>
        
      </form>
    </div>
    <script src="https://unpkg.com/vue"></script>
    <script src="/js/faset/vue.js"></script>
    <script src="{{ mix('/js/app.js') }}"></script>
  </body>
</html>

