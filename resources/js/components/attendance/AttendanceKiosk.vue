<template>
    <div>
        <div class="row">
            <template v-for="team in teams">
                <div class="col-sm-12 col-md-4" style="padding-top:50px">
                    <!-- Yes, this is _supposed_ to be a div. Don't make it a button. -->
                    <div class="btn btn-kiosk btn-secondary" :id="team.id" v-on:click="clicked">
                        {{ team.name }}
                    </div>
                </div>
            </template>
        </div>
        <div class="row d-none">
            <div class="col-sm-1" style="padding-top: 20px">
                <object id="nfc-logo" data="/img/nfc-logo.svg" type="image/svg+xml" style="max-width: 20px"></object>
            </div>
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
                    attendable_type: 'App\\Team',
                    source: 'kiosk',
                    include: 'attendee',
                },
                attendanceBaseUrl: '/api/v1/attendance',
                teamsBaseUrl: '/api/v1/teams',
                usersBaseUrl: '/api/v1/users',
                teams: [],
                stickToTeam: false,
                submitting: false,
                cardType: null
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
                                return item.visible && item.attendable;
                            }).sort(function (a, b) {
                                return a.name > b.name ? 1 : b.name > a.name ? -1 : 0;
                            });
                            this.startKeyboardListening();
                            // this.startSocketListening();
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 403) {
                            Swal.fire({
                                title: 'Whoops!',
                                text: "You don't have permission to perform that action.",
                                type: 'error',
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
                    text: 'Please provide an API token to process data',
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
            startSocketListening() {
                //Remove focus from button
                document.activeElement.blur();
                let self = this;
                this.socket = new WebSocket("ws://localhost:9000");
                // Make NFC logo icon green
                document.getElementById('nfc-logo').contentDocument.getElementById('svg-nfc-g').setAttribute('fill', '#27AE60');

                this.socket.onerror = function(event) {
                    // Make NFC logo icon red
                    document.getElementById('nfc-logo').contentDocument.getElementById('svg-nfc-g').setAttribute('fill', '#FF0000');

                    Swal.fire({
                        title: 'Hmm...',
                        text: 'There was an error connecting to the contactless card reader.',
                        showCancelButton: true,
                        showConfirmButton: true,
                        confirmButtonText: 'Retry',
                        cancelButtonText: 'Continue',
                        type: 'info',
                    }).then(function(result) {
                      if (result.value) {
                          // handle confirm
                          console.log('Retrying socket connection per user request');
                          self.startSocketListening()
                      } else {
                          // handle dismiss, result.dismiss can be 'cancel', 'overlay', 'close', and 'timer'
                          console.log('Ignoring socket connectivity issues per user request')
                      }
                    });
                };

                this.socket.onclose = function (event) {
                    // Make NFC logo icon red
                    document.getElementById('nfc-logo').contentDocument.getElementById('svg-nfc-g').setAttribute('fill', '#FF0000');
                    console.log('Socket disconnected')
                };

                this.socket.onmessage = ({data}) => {
                    console.log({ event: "Received message", data });
                    this.cardType = 'contactless';
                    this.cardPresented(data);
                };
            },
            stopSocketListening() {
                let self = this;
                this.socket.close();
                Swal.fire('socket closed');
            },
            clicked: function (event) {
                //Remove focus from button
                document.activeElement.blur();
                // When a team button is clicked, show a prompt to swipe BuzzCard
                this.attendance.attendable_id = event.target.id;
                Swal.fire(this.getTeamSwalConfig(event.target.innerText));
            },
            getTeamSwalConfig: function (teamName) {
                // This method pulls from state (attendance.attendable_id) when teamName is not passed (or undefined)
                if (teamName === undefined) {
                    const targetTeams = this.teams.filter(team => team.id.toString() === this.attendance.attendable_id);
                    if (targetTeams.length === 1) {
                        teamName = targetTeams[0].name;
                    }
                }
                return {
                    title: 'Tap or Swipe your BuzzCard now',
                    html: '<p style="font-size: 1.25em">' + teamName + '</p>', // displays team name
                    showCancelButton: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    showConfirmButton: false,
                    imageUrl: '/img/contactless-24px.svg',
                    imageWidth: 200,
                    customClass: {
                      'image': 'contactless-symbol-kiosk',
                    },
                    input: 'checkbox',
                    inputValue: this.stickToTeam,
                    inputPlaceholder: 'Stick to this team',
                    onOpen: () => {
                        // Remove focus from checkbox
                        document.activeElement.blur();
                        Swal.getInput().addEventListener("change", checkboxEventListener.bind(this));
                        var cardImg = document.createElement('img');
                        cardImg.src = '/img/contactless-card-24px.svg';
                        var cardDiv = document.createElement('div');
                        cardDiv.className = 'animated-contactless-card';
                        cardDiv.appendChild(cardImg);
                        Swal.getHeader().insertBefore(cardDiv, Swal.getTitle());
                    },
                    onClose: () => {
                        Swal.getInput().removeEventListener("change", checkboxEventListener);
                        this.clearFields();
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
                    console.log('error cardData: ' + pattError.exec(cardData));
                    cardData = null;
                    Swal.fire({
                        title: 'Hmm...',
                        text: 'There was an error reading your card. Please swipe again.',
                        showCancelButton: true,
                        showConfirmButton: false,
                        type: 'warning',
                        onClose: () => {
                            self.clearFields();
                        }
                    })
                } else {
                    Swal.close();
                    console.log('unknown cardData: ' + cardData);
                    cardData = null;
                    Swal.fire({
                        title: 'Hmm...',
                        html: 'Card format not recognized.<br/>Contact #it-helpdesk for assistance.',
                        showConfirmButton: true,
                        type: 'error',
                        timer: 3000,
                        onClose: () => {
                            self.clearFields();
                        }
                    })
                }
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
                                console.log('Error checking permissions via API');
                                Swal.fire(
                                    'Error',
                                    'Unable to validate permissions. Please contact #it-helpdesk for assistance.',
                                    'error'
                                );
                                return false;
                            } else if (response.data.users.length == 1 && response.data.users[0].roles.filter(role => role.name.toString() === "admin").length === 1) {
                                // Roles retrieved and the user is an admin
                                console.log('User is an admin!');
                                Swal.fire({
                                    title: "Administrator Options",
                                    input: 'select',
                                    inputOptions: {
                                        'reload': 'Reload page',
                                        'socket': 'Reconnect to contactless reader',
                                        'exit': 'Exit kiosk mode',
                                    },
                                    inputPlaceholder: 'Select an option',
                                    showCancelButton: true,
                                    inputValidator: (value) => {
                                        return new Promise((resolve) => {
                                            if (value === 'reload') {
                                                location.reload();
                                                resolve()
                                            } else if (value === 'socket') {
                                                this.startSocketListening();
                                                resolve()
                                            } else if (value === 'exit') {
                                                window.location.href = 'http://exitkiosk';
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
                                Swal.fire({
                                    title: 'Whoops!',
                                    text: 'Please select a team before swiping or tapping your BuzzCard',
                                    type: 'warning',
                                    timer: 2000,
                                });
                                this.clearFields();
                                return false;
                            }
                        })
                        .catch(error => {
                            this.hasError = true;
                            this.feedback = '';
                            this.clearFields();
                            if (error.response.status === 404) {
                                // User not known, but API call succeeded
                                Swal.fire({
                                    title: 'Whoops!',
                                    text: 'Please select a team before swiping or tapping your BuzzCard',
                                    type: 'warning',
                                    timer: 2000,
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
                    Swal.showLoading();
                    axios
                        .post(this.attendanceBaseUrl, this.attendance)
                        .then(response => {
                            this.hasError = false;
                            let attendeeName = (response.data.attendance.attendee ? response.data.attendance.attendee.name : "Non-Member");
                            Swal.fire({
                                title: "You're in!",
                                text: 'Nice to see you, ' + attendeeName + '.',
                                timer: 1000,
                                showConfirmButton: false,
                                type: 'success',
                            }).then(() => {
                                if (this.stickToTeam) {
                                    Swal.fire(this.getTeamSwalConfig());
                                }
                            });
                            if (!this.stickToTeam) {
                                this.clearFields();
                            } else {
                                this.clearGTID();
                            }
                        })
                        .catch(error => {
                            console.log(error);
                            this.hasError = true;
                            this.feedback = '';
                            this.clearFields();
                            if (error.response.status == 403) {
                                Swal.fire({
                                    title: 'Whoops!',
                                    text: "You don't have permission to perform that action.",
                                    type: 'error',
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
        border-radius: 0;
        /* Vertically Center Text */
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>
<style>
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

    .nfc-logo {
        width: 20px;
        height: 20px;
        position: absolute;
        right: 20px;
        bottom: 20px;
        background-color: red;
        -webkit-mask: url(/img/nfc-logo.svg) no-repeat center;
        mask: url(/img/nfc-logo.svg) no-repeat center;
    }
</style>
