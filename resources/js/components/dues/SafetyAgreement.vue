<template>
  <div>
    <h3>SCC Safety Agreement</h3>

    <fieldset class="form-group">
      <label class="lead" for="safety">As a member of the Student Competition Center (SCC), I agree to abide by the following rules. Additionally, I understand that failure to adhere to these rules may lead to temporary or permanent suspension from the SCC and/or sanctions from the Office of Student Integrity.</label>
      <div v-for="(rule,index) in rules" class="custom-control custom-checkbox">
        <input
          v-model="checks"
          type="checkbox"
          class="rule-agreement custom-control-input"
          :class="{ 'is-invalid': $v.$error && !checks.includes(index)}"
          :value="index"
          name="safety"
          :id="'safety'+index">
        <label class="custom-control-label" :for="'safety'+index">{{rule}}</label>
      </div>
    </fieldset>

    <div class="row">
      <div class="col-6">
        <button @click.prevent="$emit('back')" class="btn btn-secondary float-left mx-2">Back</button>
        <button @click="selectAll" type="button" class="btn btn-secondary float-left">Check All</button>
      </div>
      <div class="col-6">
        <button @click="submit" type="submit" class="btn btn-primary float-right">Continue</button>
      </div>
    </div>
  </div>
</template>

<script>
import { minLength } from 'vuelidate/lib/validators';

export default {
  props: ['userUid'],
  data() {
    return {
      checks: [],
      rules: [
        "You, and all members of Student Competition Center teams, are assumed to be in control of your faculties and will be held responsible for your actions and your enablement of other's actions around you including guests. Every safety precaution must be followed appropriate to the situation at hand even if it is not included on this list. Horsing around will not be tolerated. Working alone in any fabrication space is generally prohibited, unless such work is inherently non-hazardous.",
        "All use of machine tools must be logged through the provided means prior to every use. You must have a “buddy” in the immediate area capable of and trained to safely shut down any power equipment you might be using. At least one other person must be within talking distance when any powered, sharp, or impact tools are being used. No logged-in machine may be left unattended at any time.",
        "Active use of the Common Machining Area, and active noise-generating fabrication activities in team spaces, should not occur during announced “Cold Shop” events, such as sponsor visits to the SCC. Such activities may resume after the end of the scheduled visit, even if the sponsor is still present. All safety precautions must be applied with sponsors if they enter active work areas.",
        "Safety glasses are to be worn by everyone in the work area anytime that powered, sharp, or impact tools are being used. Respirators must be worn while cutting, sanding, or painting with materials prone to cause lung damage. Ear protection must be worn during times when the noise level is especially high or prolonged.",
        "Sandals, or other open toe shoes, are not allowed on the work floor of the shop at any time. Sandals may be worn while working in the offices and on a reasonable path from the door to the offices only.",
        "Flammables must be in sealed, correctly labeled containers and inside a marked flammables cabinet. Containers capable of holding in excess of 1 gallon of fuel must be stored in the flammables out-building when not being used to fill a vehicle. Leaking flammables (e.g., from vehicle fuel systems) must be corrected immediately upon detection. Appropriate fire extinguishers must be immediately available whenever flammables are being handled. At least one other person must be within talking distance when any chemicals or flammables are being used.",
        "Machine tools are to be used properly by trained individuals. Leaving the machine clean is a part of using the machine properly. Any malfunctions or damage to the machines that renders them unsafe for continued use must be marked “DOWN” in SUMS and immediately reported by email to scc@me.gatech.edu.  If a malfunction occurs but the machine can still be used safely (for example, if the probe is damaged on the Haas), do not mark it “DOWN” but do report it by email to scc@me.gatech.edu.",
        "All participants have the responsibility to keep both the common areas and the teams’ areas clean so that the school will not be embarrassed by bringing campus visitors into the building at any time. This includes the days just before a competition and anytime that the team is not fully active such as during summer break. Beverage cans, food boxes, oily rags, and other general garbage may not be left around any area of the building including inside the individual teams’ work areas or offices. Exterior spaces must be kept cleaned and organized, as well.",
        "Wood, body filler, paint, and composites should not be cut or sanded inside the Student Competition Center building without environmental control. These activities should be done outside the building or in spaces prepared for dust containment.",
        "Spray-painting should be done outside the building (without marking any surface).",
        "All waste fluids must be disposed of properly, in proper containers, following EH&S approved disposal practices. All waste fluids disposed of at the SCC must be generated by official SCC related activities.",
        "Only water and hand cleansers may be used in the sinks. If it’s not water, don’t put it down a drain.",
        "The conference room is to be used for discussions, planning, computer modeling, metrology, and vinyl-graphics cutting. The conference room is not for fabrication of components of any kind, and is not a storage area for any team.",
        "The horizontal surface over the entrance hallway is not to be used for storage. The un-fenced areas on the mezzanine are to remain clear at all times.",
        "Personal or classroom projects must not be a distraction or take up space in any common area of the shop or in the parking or drive areas outside the shop without (rarely granted) permission. Personal projects must not make any significant use of team or SCC consumable resources.",
        "Anytime a vehicle is parked in front of the roll-up door, the keys must be inside the building with someone who has permission to move the vehicle at a moment's notice, and contact information for that individual must be prominently visible through the windshield.",
        "Infractions of these rules may lead to individual and/or team sanctions, up to expulsion from the SCC.",
      ],
    };
  },
  methods: {
    selectAll: function() {
      //$(".rule-agreement").prop('checked', true);
      this.checks = [...Array(this.rules.length).keys()];
    },
    submit: function() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      var currentTimestamp = Math.round(Date.now() / 1000);

      var payload = {
        uid: this.userUid,
        accept_safety_agreement: currentTimestamp,
      };

      var baseUrl = '/api/v1/users/';
      var dataUrl = baseUrl + this.userUid;

      axios
        .put(dataUrl, payload)
        .then(response => {
          this.$emit('next');
        })
        .catch(response => {
          console.log(response);
          swal(
            'Connection Error',
            'Unable to save data. Check your internet connection or try refreshing the page.',
            'error'
          );
        });
    },
  },
  validations: {
    checks: {
      minLength: function(checks) {
        return checks.length >= this.rules.length;
      },
    },
  },
};
</script>
