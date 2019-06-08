<template>
  <div>
    <h3>Payment Instructions</h3>
      <slot></slot>
    <h3>Accepted Payment Methods</h3>
    <div class="row">
      <div class="col-12 col-lg-4">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">
              Credit/Debit Card
            </h4>
            <p class="card-text">
              Pay with your credit/debit card online. There is a $3 surcharge to cover payment processing fees.
                Prefer to pay in-person? See an officer at the shop.<br/>
                <b><a href="/dues/pay">Pay Online Now</a></b>
            </p>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-4">
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

      <div class="col-12 col-lg-4">
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
