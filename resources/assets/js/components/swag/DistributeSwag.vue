<template>
    <div class="row">
        <div class="col-12">
            <form id="DistributeSwagForm" v-on:submit.prevent="submit">
                <fieldset class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input v-model="duesTransaction.swag_shirt_provided"
                               type="checkbox"
                               class="custom-control-input"
                               id="shirt-check"
                               :disabled="this.swag_shirt_provided != null ||
                                    this.swag_shirt_status == 'Not Eligible'">
                        <label class="custom-control-label" for="shirt-check">Shirt
                            <span id="shirt-description">
                                    <small v-if="this.swag_shirt_provided != null">
                                        (Picked up {{ this.swag_shirt_provided | moment("from") }})
                                    </small>
                                    <small v-else-if="this.swag_shirt_status == 'Not Eligible'">
                                        (Not Eligible)
                                    </small>
                            </span>
                        </label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input v-model="duesTransaction.swag_polo_provided"
                               type="checkbox"
                               class="custom-control-input"
                               id="polo-check"
                               :disabled="this.swag_polo_provided != null ||
                                    this.swag_polo_status == 'Not Eligible'">
                        <label class="custom-control-label" for="polo-check">Polo
                            <span id="polo-description">
                                    <small v-if="this.swag_polo_provided != null">
                                        (Picked up {{ this.swag_polo_provided | moment("from") }})
                                    </small>
                                    <small v-else-if="this.swag_polo_status == 'Not Eligible'">
                                        (Not Eligible)
                                    </small>
                            </span>
                        </label>
                    </div>
                </fieldset>
                
                <div class="row">
                    <div class="col-12 col-md-6">
                        <button type="submit" class="btn btn-primary float-left">Submit</button>&nbsp;
                        <a href="javascript:history.back()" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</template>

<script>
    /*
     *  @props transactionType: Morph of the Transaction model to attach the payment
     *  @props transactionID: ID of the specific morph of a given Transaction Model
     *  @props swag_polo_status: Status of polo swag item (Not Eligible/Not Picked Up/Picked Up)
     *  @props swag_shirt_status: Status of shirt swag item (Not Eligible/Not Picked Up/Picked Up)
     *  @emit done: Event emitted after a payment is recorded successfully
     */

    import { numeric, required, sameAs } from 'vuelidate/lib/validators';

    export default {
        props: {
            transactionType: {
                type: String,
                required: true
            },
            transactionId: {
                type: Number,
                required: true
            },
            swag_polo_provided: {
                required: true
            },
            swag_shirt_provided: {
                required: true
            },
            swag_polo_status: {
                required: true
            },
            swag_shirt_status: {
                required: true
            }
        },
        data() {
            return {
                duesTransaction: {
                    swag_shirt_provided: false,
                    swag_polo_provided: false
                },
                baseUrl: "/api/v1/dues/transactions/",
            }
        },
        methods: {
            submit: function() {
                axios.put(this.baseUrl + this.transactionId, this.duesTransaction)
                    .then(response => {
                    this.$emit('done');
            })
            .catch(response => {
                    console.log(response);
                swal("Connection Error", "Unable to record swag distribution. Check your internet connection or try refreshing the page.", "error");
            })
            }
        }
    }
</script>