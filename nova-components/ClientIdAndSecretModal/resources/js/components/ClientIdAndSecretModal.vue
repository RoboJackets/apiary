<template>
    <Modal @modal-close="handleClose"
           tabindex="-1"
           role="dialog"
           size="2xl">
        <div
            @submit.prevent="handleConfirm"
            slot-scope="props"
            class="bg-white rounded-lg shadow-lg overflow-hidden dark:bg-gray-800 space-y-6"
        >
            <ModalHeader>Create Client</ModalHeader>
            <p class="px-8">Save the client secret now&mdash;it will not be viewable after this!</p>
              <div :class="fieldWrapperClasses">
              <div :class="labelClasses">
              <FormLabel
                :label-for="client_id"
                class="space-x-1"
                :class="{ 'mb-2': shouldShowHelpText }"
              >
                <span>
                  Client ID
                </span>
              </FormLabel>
            </div>
              <div :class="controlWrapperClasses">
              <input
                id="client_id"
                class="w-full form-control form-input form-input-bordered"
                :value="data.client_id"
                readonly="true"
              />
            </div>
          </div>
          <div :class="fieldWrapperClasses">
              <div :class="labelClasses">
              <FormLabel
                :label-for="client_secret"
                class="space-x-1"
                :class="{ 'mb-2': shouldShowHelpText }"
              >
                <span>
                  Client Secret
                </span>
              </FormLabel>
            </div>
              <div :class="controlWrapperClasses">
              <input
                id="client_secret"
                class="w-full form-control form-input form-input-bordered"
                :value="data.client_secret"
                readonly="true"
              />
            </div>
          </div>
            <ModalFooter>
                <div class="flex items-center ms-auto">
                    <CancelButton
                        component="button"
                        type="button"
                        dusk="cancel-action-button"
                        class="ms-auto me-3"
                        @click="$emit('close')"
                    >
                        Close
                    </CancelButton>
                </div>
            </ModalFooter>
        </div>

    </Modal>
</template>

<script>
export default {
    name: "ClientIdAndSecretModal",

    props: ["data"],
    methods: {
        handleClose() {
            this.$emit('close')
        },
        handleConfirm() {
            this.$emit('confirm')
        },
    },

    computed: {
      fieldWrapperClasses() {
        // prettier-ignore
        return [
          'space-y-2',
          'md:flex @md/modal:flex',
          'md:flex-row @md/modal:flex-row',
          'md:space-y-0 @md/modal:space-y-0',
          'py-5',
        ]
      },

      labelClasses() {
        // prettier-ignore
        return [
          'w-full',
          'px-6',
          'md:mt-2 @md/modal:mt-2',
          'md:px-8 @md/modal:px-8',
          'md:w-1/5 @md/modal:w-1/5',
        ]
      },

      controlWrapperClasses() {
        // prettier-ignore
        return [
          'w-full space-y-2',
          'px-6',
          'md:w-4/5 @md/modal:w-4/5',
        ]
      },
    }
}
</script>
