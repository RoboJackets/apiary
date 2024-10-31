<template>
    <div>
        <div class="row justify-content-center">
            <template v-for="team in teams">
                <div :class="rowclass(team)" style="padding-top:50px">
                    <!-- Yes, this is _supposed_ to be a div. Don't make it a button. -->
                    <div class="btn btn-kiosk btn-primary" :id="team.id" v-on:click="clicked">
                        {{ team.name }}
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    function checkboxEventListener(e) {
        this.stickToTeam = e.target.checked;
        document.activeElement.blur();
    }

    export default {
        data() {
            return {
                attendance: {
                    gtid: '',
                    attendable_id: '',
                    attendable_type: 'team',
                    source: 'kiosk',
                    include: 'attendee',
                },
                attendanceBaseUrl: '/api/v1/attendance',
                teamsBaseUrl: '/api/v1/teams',
                usersBaseUrl: '/api/v1/users',
                teams: [],
                stickToTeam: false,
                submitting: false,
                cardType: null,
                sounds: {
                  in: '/sounds/kiosk_in_short.mp3',
                  notice: '/sounds/kiosk_notice.mp3',
                  notice2: '/sounds/kiosk_notice2.mp3',
                  error: '/sounds/kiosk_error_xp.mp3',
                  dohs: [
                    '/sounds/kiosk_doh1.mp3',
                    '/sounds/kiosk_doh2.mp3',
                    '/sounds/kiosk_doh3.mp3',
                    '/sounds/kiosk_doh4.mp3',
                    '/sounds/kiosk_doh5.mp3',
                    '/sounds/kiosk_doh6.mp3',
                  ]
                }
            };
        },
        mounted() {
            //Remove focus from button
            document.activeElement.blur();
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('api_token');
            this.loadTeams();
        },
        methods: {
            loadTeams() {
                // Fetch teams from the API to populate buttons
                axios
                    .get(this.teamsBaseUrl)
                    .then(response => {
                        let rawTeams = response.data.teams;
                        if (rawTeams.length < 1) {
                            Swal.fire('Bueller...Bueller...', 'No teams found.', 'warning');
                        } else {
                            this.teams = response.data.teams.filter(function (item) {
                                return item.visible && item.visible_on_kiosk && item.attendable;
                            }).sort(function (a, b) {
                                return a.name > b.name ? 1 : b.name > a.name ? -1 : 0;
                            });
                            if (this.teams.length < 1) {
                              Swal.fire('Bueller...Bueller...', 'All teams hidden from kiosk.', 'warning');
                            }
                            this.teams.forEach(function (team) {
                                // If the team name starts with Robo and the next letter is a capital letter, insert
                                // 0xAD (an invisible hyphen) to allow the browser to break the word up if necessary.
                                if (team.name.length < 5) return;
                                let startsWithRobo = team.name.startsWith('Robo');
                                let charCode = team.name.charCodeAt(4);
                                let nextLetterCapital = charCode >= 65 && charCode <= 90;
                                if (startsWithRobo && nextLetterCapital) {
                                    team.name = team.name.substring(0, 4) + "\u00AD" + team.name.substring(4);
                                }
                            });
                            this.startKeyboardListening();
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 403) {
                            Swal.fire({
                                title: 'Whoops!',
                                text: "You don't have permission to perform that action.",
                                icon: 'error',
                            });
                        } else if (error.response.status === 401) {
                            this.tokenPrompt();
                        } else {
                            Swal.fire(
                                'Error',
                                'Unable to process data. Check your internet connection or try refreshing the page.',
                                'error'
                            );
                        }
                    });
            },
            tokenPrompt() {
                // Prompt for API token to be stored in local browser storage
                let self = this;

                Swal.fire({
                    title: 'Authentication',
                    text: 'Provide an API token to process data',
                    input: 'text',
                }).then(result => {
                    if (result === false) return false;
                    if (result === '') {
                        Swal.showValidationError('Token field is required!');
                        return false;
                    }
                    localStorage.setItem('api_token', result.value);
                    axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('api_token');
                    Swal.close();
                    self.loadTeams();
                });
            },
            startKeyboardListening() {
                //Remove focus from button
                document.activeElement.blur();
                // Listen for keystrokes from card swipe (or keyboard)
                let buffer = '';
                window.addEventListener(
                    'keypress',
                    function (e) {
                        if (this.submitting) {
                            return;
                        }
                        if (e.key != 'Enter') {
                            //A key that's not enter was pressed
                            buffer += e.key;
                        } else {
                            //Enter was pressed
                            this.cardType = 'magstripe';
                            this.cardPresented(buffer);
                            buffer = '';
                        }
                    }.bind(this)
                );
            },
            clicked: function (event) {
                //Remove focus from button
                document.activeElement.blur();
                // When a team button is clicked, show a prompt to swipe BuzzCard
                this.attendance.attendable_id = event.target.id;
                Swal.fire(this.getTeamSwalConfig(event.target.innerText, false)).then(() => {
                    // Clear fields in case of any modal dismissal
                    // This *does not* fire in normal card processing flow
                    this.clearFields();
                })
            },
            getTeamSwalConfig: function (teamName, sticky) {
                // This method pulls from state (attendance.attendable_id) when teamName is not passed (or undefined)
                if (teamName === undefined) {
                    const targetTeams = this.teams.filter(team => team.id.toString() === this.attendance.attendable_id);
                    if (targetTeams.length === 1) {
                        teamName = targetTeams[0].name;
                    }
                }
                return {
                    title: 'Tap your BuzzCard now',
                    html: `<div>
                            Recording attendance for<br/>
                            <b style='font-size: 2em'>${teamName}</b><br/><br/>
                            <span onClick="document.getElementById('stick-checkbox').click()">
                            <em>Multiple people attending the same team?</em>
                            </span>
                            <div class="form-check">
                              <input class="form-check-input stick-checkbox" type="checkbox" id="stick-checkbox">
                              <label class="form-check-label" for="stick-checkbox">
                                Stick to this team
                              </label>
                            </div>
                            </div>`,
                    showCancelButton: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    backdrop: true,
                    showConfirmButton: false,
                    timer: (sticky) ? 600000 : 30000,
                    timerProgressBar: true,
                    didOpen: () => {
                        // Remove focus from checkbox
                        document.activeElement.blur();
                        let checkbox = document.getElementById('stick-checkbox');
                        checkbox.checked = this.stickToTeam;
                        checkbox.addEventListener("change", checkboxEventListener.bind(this));

                        // Add animated contactless card symbol
                        const cardImg = document.createElement('img');
                        cardImg.src = '/img/Universal_Contactless_Card_Symbol.svg';
                        const cardDiv = document.createElement('div');
                        cardDiv.className = 'animated-contactless-card';
                        cardDiv.appendChild(cardImg);
                        Swal.getTitle().parentNode.insertBefore(cardDiv, Swal.getTitle());
                    },
                    didDestroy: () => {
                      let checkbox = document.getElementById('stick-checkbox')
                      if (checkbox) {
                        checkbox.removeEventListener("change", checkboxEventListener);
                      }
                    }
                }
            },
            cardPresented: function (cardData) {
                // Card is presented, process the data
                let self = this;
                this.attendance.source = 'kiosk';
                console.log('first cardData: ' + cardData);

                let pattTrackRaw = new RegExp('=(9[0-9]+)=');
                let pattError = new RegExp('[%;+][eE]\\?');
                let pattTrackNFC = new RegExp('NFC-(9[0-9]+)');

                if (this.isNumeric(cardData) && cardData.length == 9 && cardData[0] == '9') {
                    // Numeric nine-digit number starting with a nine
                    this.attendance.gtid = cardData;
                    this.attendance.source += '-' + this.cardType;
                    console.log('numeric cardData: ' + cardData);
                    cardData = null;
                    this.submit();
                } else if (pattTrackRaw.test(cardData)) {
                    // Raw (unformatted) data from track 2 of the magnetic stripe
                    let data = pattTrackRaw.exec(cardData)[1];
                    console.log('raw cardData: ' + data);
                    cardData = null;
                    this.attendance.gtid = data;
                    this.attendance.source += '-' + this.cardType;
                    this.submit();
                } else if (pattTrackNFC.test(cardData)) {
                    // Raw (unformatted) data from track 2 of the magnetic stripe
                    let data = pattTrackNFC.exec(cardData)[1];
                    console.log('NFC-prefixed cardData: ' + data);
                    cardData = null;
                    this.attendance.gtid = data;
                    this.attendance.source += '-contactless';
                    this.submit();
                } else if (pattError.test(cardData)) {
                    // Error message sent from card reader
                    new Audio(this.sounds.error).play()
                    console.log('error cardData: ' + pattError.exec(cardData));
                    cardData = null;
                    Swal.fire({
                        title: 'Hmm...',
                        text: 'There was an error reading your card. Try again.',
                        showConfirmButton: false,
                        icon: 'warning',
                        timer: 3000,
                        timerProgressBar: true,
                        didDestroy: () => {
                            self.clearFields();
                        }
                    })
                } else {
                    Swal.close();
                    new Audio(this.sounds.error).play()
                    console.log('unknown cardData: ' + cardData);
                    cardData = null;
                    Swal.fire({
                        title: 'Hmm...',
                        html: 'Card format not recognized.<br/>Contact #it-helpdesk for assistance.',
                        showConfirmButton: false,
                        icon: 'error',
                        timer: 3000,
                        timerProgressBar: true,
                        didDestroy: () => {
                            self.clearFields();
                        }
                    })
                }
            },
            randomIntFromInterval: function(min, max) { // min and max included
              // from a kind StackOverflower: https://stackoverflow.com/a/7228322
              return Math.floor(Math.random() * (max - min + 1) + min);
            },
            submit() {
                // Check for lack of team selection
                if (this.attendance.attendable_id === '') {
                    // We have a valid card read and no team picked, check if user is an admin for hidden menu access
                    axios
                        .get(this.usersBaseUrl + "/search", {
                            params: {
                                include: 'roles',
                                keyword: this.attendance.gtid,
                            }
                        })
                        .then(response => {
                            if (response.data.users.length == 1 && typeof response.data.users[0].roles === "undefined") {
                                // Unable to read roles? That's an error.
                                new Audio(this.sounds.error).play()
                                console.log('Error checking permissions via API');
                                Swal.fire(
                                    'Error',
                                    'Unable to validate permissions. Contact #it-helpdesk for assistance.',
                                    'error'
                                );
                                return false;
                            } else if (response.data.users.length == 1 && response.data.users[0].roles.filter(role => role.name.toString() === "admin").length === 1) {
                                // Roles retrieved and the user is an admin
                                let self = this;
                                new Audio(this.sounds.notice).play()
                                Swal.fire({
                                    title: "Administrator Options",
                                    input: 'select',
                                    inputOptions: {
                                        'reload': 'Reload page',
                                        'exit': 'Exit kiosk mode',
                                        'sounds': 'Test all sounds'
                                    },
                                    inputPlaceholder: 'Select an option',
                                    showCancelButton: true,
                                    inputValidator: (value) => {
                                        return new Promise((resolve) => {
                                            if (value === 'reload') {
                                                location.reload();
                                                resolve()
                                            } else if (value === 'exit') {
                                                window.location.href = 'http://exitkiosk';
                                                resolve()
                                            } else if (value === 'sounds') {
                                                Object.entries(self.sounds).forEach((entry) => {
                                                  const [key, value] = entry;
                                                  if (typeof value == 'string') {
                                                    new Audio(value).play()
                                                  } else if (Array.isArray(value)) {
                                                    value.forEach(arrayElement => {
                                                      new Audio(arrayElement).play()
                                                    })
                                                  } else {
                                                    console.log('Unknown sound type in object')
                                                  }
                                                })
                                                resolve()
                                            } else {
                                                resolve("That's not a valid option.")
                                            }
                                        })
                                    }
                                });
                                return false;
                            } else {
                                // Roles retried and the user is not an admin
                                console.log('User is not an admin');
                                new Audio(this.sounds.dohs[this.randomIntFromInterval(0, this.sounds.dohs.length - 1)]).play()
                                Swal.fire({
                                    title: "D'oh!",
                                    text: 'Select a team before tapping your BuzzCard',
                                    icon: 'warning',
                                    timer: 2500,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                });
                                this.clearFields();
                                return false;
                            }
                        })
                        .catch(error => {
                            this.hasError = true;
                            this.feedback = '';
                            this.clearFields();
                            new Audio(this.sounds.error).play()
                            if (error.response.status === 404) {
                                // User not known, but API call succeeded
                                Swal.fire({
                                    title: 'Whoops!',
                                    text: 'Select a team before tapping your BuzzCard',
                                    icon: 'warning',
                                    timer: 2500,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire(
                                    'Error',
                                    'Unable to process data. Check your internet connection or try refreshing the page.',
                                    'error'
                                );
                            }
                        });
                } else {
                    // Submit attendance data
                    this.submitting = true;
                    let self = this;
                    Swal.showLoading();
                    axios
                        .post(this.attendanceBaseUrl, this.attendance)
                        .then(response => {
                            this.hasError = false;
                            let attendeeName = (response.data.attendance.attendee ? response.data.attendance.attendee.name : "Non-Member");
                            new Audio(this.sounds.in).play()
                            /*Swal.fire({
                                title: "You're in!",
                                text: 'Nice to see you, ' + attendeeName + '.',
                                timer: 1000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                icon: 'success',
                            }).then(() => {
                                if (self.stickToTeam) {
                                  Swal.fire(this.getTeamSwalConfig(undefined, true)).then(() => {
                                    // Clear fields in case of any modal dismissal
                                    // This *does not* fire in normal card processing flow
                                    this.clearFields();
                                  });
                                }
                            });*/
                            if (!self.stickToTeam) {
                                Swal.fire({
                                title: "You're in!",
                                text: 'Nice to see you, Lord ' + attendeeName + '.',
                                timer: 1000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                icon: 'success',
                                });
                                this.clearFields();
                            } else {
                                this.clearGTID();
                                Swal.fire(this.getTeamSwalConfig(undefined, true)).then(() => {
                                    // Clear fields in case of any modal dismissal
                                    // This *does not* fire in normal card processing flow
                                    this.clearFields();
                                });
                            }
                        })
                        .catch(error => {
                            new Audio(this.sounds.error).play()
                            console.log(error);
                            this.hasError = true;
                            this.feedback = '';
                            this.clearFields();
                            if (error.hasOwnProperty('response') && error.response.status == 403) {
                                Swal.fire({
                                    title: 'Whoops!',
                                    text: "You don't have permission to perform that action.",
                                    icon: 'error',
                                });
                            } else {
                                Swal.fire(
                                    'Error',
                                    'Unable to process data. Check your internet connection or try refreshing the page.',
                                    'error'
                                );
                            }
                        })
                        .finally(() => {
                            this.submitting = false;
                            Swal.hideLoading();
                        });
                }

            },
            clearFields() {
                //Remove focus from button
                document.activeElement.blur();
                this.attendance.attendable_id = '';
                this.attendance.gtid = '';
                this.stickToTeam = false;
                this.cardType = null;
                this.attendance.source = 'kiosk';
                console.log('fields cleared');
            },
            clearGTID() {
                document.activeElement.blur();
                this.attendance.gtid = '';
            },
            isNumeric(n) {
                return !isNaN(parseFloat(n)) && isFinite(n);
            },
            rowclass: function(team) {
                // This is broken out as a function because the teams starting with Robo- used to be wider. This will
                // likely need to be dynamic again when a new team is added, so I'm leaving it as a function.
                return 'col-sm-12 col-md-6';
            },
        },
    };
</script>

<style scoped>
    /* Combination of btn-lg and btn-block with some customizations */
    .btn-kiosk {
        min-height: 250px;
        font-weight: bolder;
        font-size: 2.75rem;
        width: 100%;
        padding: 0.5rem 1rem;
        line-height: 1.5;
        border-radius: 20px;
        box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
        /* Vertically Center Text */
        display: flex;
        justify-content: center;
        align-items: center;
        hyphens: manual;
    }
</style>
<style scoped>
    /* Global styles */
    .swal2-checkbox {
        font-size: 110%;
        margin: 1.5em auto !important;
    }

    .swal2-loading {
        flex-direction: column;
    }

    .swal2-loading button {
        margin-bottom: 2em !important;
    }

    .swal2-cancel, .swal2-confirm {
      width: 70%;
    }

    .stick-checkbox {
      width: 25px;
      height: 25px;
      vertical-align: text-bottom;
    }

    .form-check-input {
      position: relative !important;
    }
</style>
