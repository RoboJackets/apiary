<template>
  <div>
    <h3>Payment Instructions</h3>
      <slot></slot>
    <h3>Accepted Payment Methods</h3>
    <div class="row">
      <div class="col-12 col-lg-3">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">
              Online (Debit)
            </h4>
            <p class="card-text">
              Pay with your debit card online via SquareCash to $robojackets. Please put your GT username (e.g. gburdell3) in the notes.
            </p>
            <a href="https://cash.me/$RoboJackets" class="card-link" target="_blank">Pay Now at SquareCash</a>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-3">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">
              Cash
            </h4>
            <p class="card-text">
              Pay an officer or a project manager at the shop.
            </p>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-3">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">
              Credit Card
            </h4>
            <p class="card-text">
              Pay an officer at the shop. There is a $3 surcharge to cover payment processing fees.
            </p>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-3">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">
              Check
            </h4>
            <p class="card-text">
              Make a check out to RoboJackets and give it to an officer or your project manager. Don't forget to sign it!
            </p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>