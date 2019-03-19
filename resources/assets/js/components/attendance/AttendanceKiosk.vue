<template>
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
                teams: [],
                stickToTeam: false,
                submitting: false,
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
                            swal('Bueller...Bueller...', 'No teams found.', 'warning');
                        } else {
                            this.teams = response.data.teams.filter(function (item) {
                                return item.visible && item.attendable;
                            }).sort(function (a, b) {
                                return a.name > b.name ? 1 : b.name > a.name ? -1 : 0;
                            });
                            this.startKeyboardListening();
                            this.startSocketListening();
                        }
                    })
                    .catch(error => {
                        if (error.response.status === 403) {
                            swal({
                                title: 'Whoops!',
                                text: "You don't have permission to perform that action.",
                                type: 'error',
                            });
                        } else if (error.response.status === 401) {
                            this.tokenPrompt();
                        } else {
                            swal(
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

                swal({
                    title: 'Authentication',
                    text: 'Please provide an API token to process data',
                    input: 'text',
                }).then(result => {
                    if (result === false) return false;
                    if (result === '') {
                        swal.showValidationError('Token field is required!');
                        return false;
                    }
                    localStorage.setItem('api_token', result.value);
                    axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('api_token');
                    swal.close();
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
                        if (this.attendance.attendable_id == '' && e.key == 'Enter') {
                            //Enter was pressed but a team was not picked
                            buffer = '';
                            swal({
                                title: 'Whoops!',
                                text: 'Please select a team before swiping your BuzzCard',
                                type: 'warning',
                                timer: 2000,
                            });
                        } else if (e.key != 'Enter') {
                            //A key that's not enter was pressed
                            buffer += e.key;
                        } else {
                            //Enter was pressed
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
                this.socket.onmessage = ({data}) => {
                    console.log({ event: "Received message", data });
                    this.cardPresented(data);
                };
            },
            stopSocketListening() {
                let self = this;
                this.socket.close();
                swal('socket closed');
            },
            clicked: function (event) {
                //Remove focus from button
                document.activeElement.blur();
                // When a team button is clicked, show a prompt to swipe BuzzCard
                this.attendance.attendable_id = event.target.id;
                swal(this.getTeamSwalConfig(event.target.innerText));
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
                    title: 'Swipe or Tap your BuzzCard now',
                    html: '<p style="font-size: 1.25em">' + teamName + '</p>', // displays team name
                    showCancelButton: true,
                    allowOutsideClick: () => !swal.isLoading(),
                    showConfirmButton: false,
                    imageUrl: '/img/swipe-horiz-up.gif',
                    imageWidth: 450,
                    input: 'checkbox',
                    inputValue: this.stickToTeam,
                    inputPlaceholder: 'Stick to this team',
                    onOpen: () => {
                        // Remove focus from checkbox
                        document.activeElement.blur();
                        swal.getInput().addEventListener("change", checkboxEventListener.bind(this));
                    },
                    onClose: () => {
                        swal.getInput().removeEventListener("change", checkboxEventListener);
                        this.clearFields();
                    }
                }
            },
            cardPresented: function (cardData) {
                // Card is presented, process the data
                let self = this;
                console.log('first cardData: ' + cardData);

                let pattTrackRaw = new RegExp('=(9[0-9]+)=');
                let pattError = new RegExp('[%;+][eE]\\?');

                if (this.isNumeric(cardData) && cardData.length == 9 && cardData[0] == '9') {
                    // Numeric nine-digit number starting with a nine
                    this.attendance.gtid = cardData;
                    console.log('numeric cardData: ' + cardData);
                    cardData = null;
                    this.submit();
                } else if (pattTrackRaw.test(cardData)) {
                    // Raw (unformatted) data from track 2 of the magnetic stripe
                    let data = pattTrackRaw.exec(cardData)[1];
                    console.log('raw cardData: ' + data);
                    cardData = null;
                    this.attendance.gtid = data;
                    this.submit();
                } else if (pattError.test(cardData)) {
                    // Error message sent from card reader
                    console.log('error cardData: ' + pattError.exec(cardData));
                    cardData = null;
                    swal({
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
                    swal.close();
                    console.log('unknown cardData: ' + cardData);
                    cardData = null;
                    swal({
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
                // Submit attendance data
                this.submitting = true;
                swal.showLoading();
                axios
                    .post(this.attendanceBaseUrl, this.attendance)
                    .then(response => {
                        this.hasError = false;
                        let attendeeName = (response.data.attendance.attendee.name || "Non-Member");
                        swal({
                            title: "You're in!",
                            text: 'Nice to see you, ' + attendeeName + '.',
                            timer: 1000,
                            showConfirmButton: false,
                            type: 'success',
                        }).then(() => {
                            if (this.stickToTeam) {
                                swal(this.getTeamSwalConfig());
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
                            swal({
                                title: 'Whoops!',
                                text: "You don't have permission to perform that action.",
                                type: 'error',
                            });
                        } else {
                            swal(
                                'Error',
                                'Unable to process data. Check your internet connection or try refreshing the page.',
                                'error'
                            );
                        }
                    })
                    .finally(() => {
                        this.submitting = false;
                        swal.hideLoading();
                    });
            },
            clearFields() {
                //Remove focus from button
                document.activeElement.blur();
                this.attendance.attendable_id = '';
                this.attendance.gtid = '';
                this.stickToTeam = false;
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
</style>