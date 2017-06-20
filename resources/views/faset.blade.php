<!DOCTYPE html>
<html lang="en">
  <head>
  <title>FASET Form | MyRoboJackets</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
  </head>
  <body>
    <div class="container">
    <div>
      <h1>I'm interested in RoboJackets!</h1>
    </div>
    <form>
      <div class="form-group row">
        <label for="faset-name" class="col-sm-2 col-form-label">Name:</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="faset-name" name="faset-name" placeholder="George Burdell">
          <small class="form-text text-muted">First and last name</small>
        </div>
      </div>

      <div class="form-group row">
        <label for="faset-email" class="col-sm-2 col-form-label">Email</label>
        <div class="col-sm-10">
          <input type="email" class="form-control form-control-warning" id="inputHorizontalWarning" placeholder="example@gatech.edu">
        </div>
      </div>

      <fieldset class="form-group row">
        <legend class="col-form-legend col-sm-12">How did you hear about RoboJackets?</legend>
        <div class="offset-sm-2 col-sm-10">
          <div class="form-check">
            <label class="form-check-label">
              <input class="form-check-input" type="checkbox" name="heardfrom" value="faset">
              FASET
            </label>
          </div>
          <div class="form-check">
            <label class="form-check-label">
              <input class="form-check-input" type="checkbox" name="heardfrom" value="option2">
              Option two can be something else and selecting it will deselect option one
            </label>
          </div>
          <div class="form-check disabled">
            <label class="form-check-label">
              <input class="form-check-input" type="checkbox" name="heardfrom" value="option3">
              Option three is disabled
            </label>
          </div>
        </div>
      </fieldset>
      
    </form>
  </div>
  <script src="https://unpkg.com/vue"></script>
  <script src="/js/faset/vue.js"></script>
  <script src="{{ mix('/js/app.js') }}"></script>
  </body>
</html>

