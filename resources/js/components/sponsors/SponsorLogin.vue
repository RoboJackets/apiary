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
        <p>Enter the one-time password that was sent to your email.</p>
        <p>Check your spam folder if the message is not in your inbox. If you did not receive the password, use the button below to resend it.</p>
        <p></p>
      </div>
      <div v-if="emailValidated" class="resend-section">
        <button
          class="btn btn-link"
          :disabled="!canResend"
          @click="validateEmail"
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
  beforeUnmount() {
    // Clean up interval timer when app is exited to prevent memory leaks
    clearInterval(this.resendTimer);
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
    handleApiError(error, validationField = null) {
      if (error.response?.data) {
        const data = error.response.data;
        
        // Custom error response from controller
        if (data.error && data.title && data.message) {
          Swal.fire(data.title, data.message, 'error');
        } 
        // Laravel validation error
        else if (validationField && data.errors?.[validationField]) {
          Swal.fire('Validation Error', data.errors[validationField][0], 'error');
        }
        // Any other server error
        else {
          Swal.fire('Error', 'Something went wrong. Please try again. If the issue persists, contact hello@robojackets.org.', 'error');
        }
      } else {
        // Network error (no response from server)
        Swal.fire('Connection Error', 'Unable to reach the server. Please check your connection. If the issue persists, contact hello@robojackets.org.', 'error');
      }
    },
    validateEmail() {
      axios.post('/sponsor/validate-email', { email: this.email })
        .then(res => {
          if (res.data.success) { // else need to throw error
            this.emailValidated = true;
            this.beginResendCooldown();
          
          }
        })
        .catch(error => {
          this.handleApiError(error, 'email');
        });
    },
    beginResendCooldown() {
        this.resendCooldown = 60; // may want to increase to 2 mins in future
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
    handleSubmit() {
      // TODO: ensure correct route
      axios.post('/sponsor/verify-otp', { otp: this.password })
        .then(res => {
          if (res.data.success) {
            // Redirect to the URL provided by the controller
            window.location.href = res.data.redirect;
          }
        })
        .catch(error => {
          this.handleApiError(error, 'otp');
        });
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
