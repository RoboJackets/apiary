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
            <ModalHeader>Create Personal Access Token</ModalHeader>
            <p class="px-8">Save this token now&mdash;it will not be viewable after this!</p>
            <ModalContent>
              <input
                class="w-full form-control form-input form-input-bordered"
                :value="data.token"
                readonly="true"
              />
            </ModalContent>
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
    name: "PersonalAccessTokenModal",

    props: ["data"],
    methods: {
        handleClose() {
            this.$emit('close')
        },
        handleConfirm() {
            this.$emit('confirm')
        },
    },
}
</script>
