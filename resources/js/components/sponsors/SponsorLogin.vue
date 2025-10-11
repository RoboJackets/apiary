<template>
  <div class="container">
    <div class="d-flex justify-content-center p-4 ">
        <img 
            src="/img/ic_robobuzz_white_outline.svg"
            alt="RoboJackets Logo"
            height="200"
            />
    </div>
  <div class="card shadow-sm p-4">
    <h3 class="text-center mb-4">Sponsor Login</h3>

    <form @submit.prevent="handleSubmit">
      <transition name="slide-fade" mode="out-in">
        <div v-if="!emailValidated" key="email-step">
          <label for="email" class="form-label">Email</label>
          <input 
            type="email" 
            v-model="email" 
            class="form-control" 
            placeholder="you@company.com" 
            required 
            @keydown.enter.prevent="validateEmail">
          <button type="button" class="btn btn-primary mt-3" @click="validateEmail">Next</button>
        </div>

        <div v-if="emailValidated" key="password-step">
          <label for="password" class="form-label">One-Time Password</label>
          <input 
            type="text" 
            v-model="password" 
            class="form-control" 
            placeholder="One-Time Password" 
            required>
          <button type="submit" class="btn btn-success mt-3">Submit</button>
        </div>
      </transition>
    </form>
  </div>
  <transition name="slide-fade">
      <div v-if="emailValidated" class="alert alert-primary" role="alert">
        <h4>One-Time Password Sent!</h4>
        <p>Please type the one-time password sent to your email.</p>
        <p>Be sure to check your Spam folder if you do not see it. 
          If you believe the password did not send correctly, press the button below to resend it.</p>
        <p></p>
      </div>
      <div v-if="emailValidated" class="resend-section">
        <button
          class="btn btn-link"
          :disabled="!canResend"
          @click="sendOTP"
        >
          {{ canResend ? 'Resend OTP' : `Resend in ${resendCooldown}s` }}
        </button>
      </div>
    </transition>
  </div>
</template>

<script>
import Swal from 'sweetalert2';
import axios from 'axios';

export default {
  name: 'SponsorLogin',
  data() {
    return {
      email: '',
      password: '',
      emailValidated: false,
      canResend: false,
      resendTimer: null,
      resendCooldown: 30,
    };
  },
  methods: {
    showToast(message, icon = 'info') {
      Swal.fire({
        toast: true,
        position: 'bottom-end',
        icon,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
    },
    validateEmail() {
    //   axios.post('/sponsor/check-email', { email: this.email })
    //     .then(res => {
    //       if (res.data.valid) {
    //         this.emailValidated = true;
    //         this.showToast('A one-time password has been sent to your email.', 'info');
    //       } else {
    //         this.showToast('Email domain not approved.', 'error');
    //       }
    //     })
    //     .catch(() => {
    //       this.showToast('Something went wrong. Please try again.', 'error');
    //     });
        if (this.email === 'gpburdell3@gatech.edu') {
            this.emailValidated = true;
            this.sendOTP();
        } else {
          // NOTE: hello@robojackets.org is a placeholder for now; will change when I find out
          // who is a good point of contact
          Swal.fire('Authentication Error', 'Could not validate email domain. Please try again, or contact <a href="hello@robojackets.org">hello@robojackets.org</a> if issues persist.', 'error');
        }
    },
    beginResendCooldown() {
        this.resendCooldown = 30;
        this.canResend = false;
        clearInterval(this.resendTimer);
        this.resendTimer = setInterval(() => {
            if (this.resendCooldown > 0) {
                this.resendCooldown--;
            } else {
                clearInterval(this.resendTimer);
                this.canResend = true;
            }
        }, 1000);
    },
    sendOTP() {
        //TODO: OTP Logic. Rate-limiting should be implemented on backend.
        this.beginResendCooldown();
    },
    handleSubmit() {
    //   axios.post('/sponsor/login', { email: this.email, password: this.password })
    //     .then(res => {
    //       if (res.data.success) {
    //         window.location.href = '/sponsor/dashboard';
    //       } else {
    //         this.showToast('Invalid password.', 'error');
    //       }
    //     })
    //     .catch(() => {
    //       this.showToast('Something went wrong. Please try again.', 'error');
    //     });
      if (this.password === 'hello') {
        window.location.href='/';
      } else {
        Swal.fire(
          'Authentication Error', 
          'Could not validate password. Please try again or contact <a href="hello@robojackets.org">hello@robojackets.org</a> if the issue persists.', 
          'error');
      }
    }
  }
};
</script>

<style scoped>
.card {
  max-width: 450px;
  margin: 2rem auto;
}

/* Slide + Fade Transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.35s ease;
}

.resend-section {
  margin-top: 12px;
  text-align: center;
}

.slide-fade-enter,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
