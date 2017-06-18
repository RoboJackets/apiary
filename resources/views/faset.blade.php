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
      <form id="form" class="pa4-ns near-black" v-on:submit.prevent="submit">
        <div class="normal mb4"><span class="red">All fields are required.</span> @{{ queued }}</div>
        <label for="name" class="f6 b db mb2">Name</label>
        <input name="faset-name" id="name" class="input-reset ba b--black-20 pa2 mb2 db w-100 mb4" type="text" aria-describedby="name-desc" required>
        <label for="email" class="f6 b db mb2">Email</label>
        <input name="faset-email" id="email" class="input-reset ba b--black-20 pa2 mb2 db w-100 mb4" type="email" aria-describedby="email" required>
        <legend class="fw7 mb2">How did you hear about us?</legend>
        <div class="flex items-center mb2">
          <input id="faset" class="mr2" type="checkbox" name="heardfrom" value="faset">
          <label for="faset" class="lh-copy">FASET</label>
        </div>
        <div class="flex items-center mb2">
          <input id="tour" class="mr2" type="checkbox" name="heardfrom" value="tour">
          <label for="tour" class="lh-copy">Campus tour</label>
        </div>
        <div class="flex items-center mb2">
          <input id="member" class="mr2" type="checkbox" name="heardfrom" value="member">
          <label for="member" class="lh-copy">Current RoboJackets member</label>
        </div>
        <div class="flex items-center mb2">
          <input id="social" class="mr2" type="checkbox" name="heardfrom" value="social">
          <label for="social" class="lh-copy">Social media</label>
        </div>
        <div class="flex items-center mb2">
          <input id="web" class="mr2" type="checkbox" name="heardfrom" value="web">
          <label for="web" class="lh-copy">RoboJackets.org</label>
        </div>
        <div class="flex items-center mb2">
          <input id="frc" class="mr2" type="checkbox" name="heardfrom" value="frc">
          <label for="frc" class="lh-copy">FRC event</label>
        </div>
        <div class="flex items-center mb2">
          <input id="ftc" class="mr2" type="checkbox" name="heardfrom" value="ftc">
          <label for="ftc" class="lh-copy">FTC event</label>
        </div>
        <div class="flex items-center mb2">
          <input id="vex" class="mr2" type="checkbox" name="heardfrom" value="vex">
          <label for="vex" class="lh-copy">VEX event</label>
        </div>
        <div id="otherdiv" class="flex items-center mb4">
          <input id="other" class="mr2" type="checkbox" name="heardfrom" value="other" v-model="isChecked">
          <label for="other" class="lh-copy">Other: &nbsp</label>
          <input name="other" class="mr2" type="text" value="" v-bind:required="isRequired">
        </div>
        <input class="b ph3 pv2 input-reset ba b--black bg-transparent dim pointer f6 dib" type="submit" value="Submit">
      </form>
    </div>
    <script src="https://unpkg.com/vue"></script>
    <script src="/js/faset/vue.js"></script>
  </body>
</html>
