<!DOCTYPE html>
<html lang="en">
  <title>FASET Form | MyRoboJackets</title>
  <style>
  body {
    font-family: sans-serif;
  }
  </style>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://unpkg.com/tachyons/css/tachyons.min.css">
  <body>
    <div class="ph6-l ph5-m pa4">
      <div class="normal black-60 ph4-ns pt4-ns red">All fields are required.</div>
      <form class="pa4-ns near-black" method="POST" action="/api/v1/faset">
        <label for="name" class="f6 b db mb2">Name</label>
        <input id="name" class="input-reset ba b--black-20 pa2 mb2 db w-100 mb4" type="text" aria-describedby="name-desc" required>
        <label for="email" class="f6 b db mb2">Email</label>
        <input id="email" class="input-reset ba b--black-20 pa2 mb2 db w-100 mb4" type="email" aria-describedby="email" required>
        <legend class="fw7 mb2">How did you hear about us?</legend>
        <div class="flex items-center mb2">
          <input class="mr2" type="checkbox" id="spacejam" value="spacejam">
          <label for="spacejam" class="lh-copy">FASET</label>
        </div>
        <div class="flex items-center mb2">
          <input class="mr2" type="checkbox" id="spacejam" value="spacejam">
          <label for="spacejam" class="lh-copy">Campus tour</label>
        </div>
        <div class="flex items-center mb2">
          <input class="mr2" type="checkbox" id="spacejam" value="spacejam">
          <label for="spacejam" class="lh-copy">Current RoboJackets member</label>
        </div>
        <div class="flex items-center mb2">
          <input class="mr2" type="checkbox" id="spacejam" value="spacejam">
          <label for="spacejam" class="lh-copy">Social media</label>
        </div>
        <div class="flex items-center mb2">
          <input class="mr2" type="checkbox" id="spacejam" value="spacejam">
          <label for="spacejam" class="lh-copy">RoboJackets.org</label>
        </div>
        <div class="flex items-center mb2">
          <input class="mr2" type="checkbox" id="spacejam" value="spacejam">
          <label for="spacejam" class="lh-copy">FRC event</label>
        </div>
        <div class="flex items-center mb2">
          <input class="mr2" type="checkbox" id="spacejam" value="spacejam">
          <label for="spacejam" class="lh-copy">FTC event</label>
        </div>
        <div class="flex items-center mb2">
          <input class="mr2" type="checkbox" id="spacejam" value="spacejam">
          <label for="spacejam" class="lh-copy">VEX event</label>
        </div>
        <div id="otherbox" class="flex items-center mb4">
          <input class="mr2" type="checkbox" id="spacejam" value="spacejam" v-model="isChecked">
          <label for="spacejam" class="lh-copy">Other: &nbsp</label>
          <input class="mr2" type="text" id="spacejam" value="" v-bind:required="isRequired">
        </div>
        <input class="b ph3 pv2 input-reset ba b--black bg-transparent dim pointer f6 dib" type="submit" value="Submit">
      </form>
    </div>
    <script src="https://unpkg.com/vue"></script>
    <script type="text/javascript">
      var vm = new Vue({
        el: '#otherbox',
        data: {
          isChecked: false
        },
        computed: {
          isRequired: function () {
            return this.isChecked
          }
        }
      })
    </script>
  </body>
</html>
