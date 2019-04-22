<template>
    <panel-item :field="field">
        <template slot="value">
            <div v-if="hasContent">
                <p v-if="expanded" class="text-90 inline" :class="{ 'font-mono': field.monospaced }">{{ field.value }}</p>
                <a @click="toggle" class="cursor-pointer dim inline text-primary font-bold" :class="{ 'mt-6': expanded }" aria-role="button">{{ this.expanded ? 'Hide' : 'Show' }}</a>
            </div>
            <div v-else>&mdash;</div>
        </template>
    </panel-item>
</template>

<script>
export default {
    props: [ 'resource', 'resourceName', 'resourceId', 'field' ],

    data: () => {
        expanded: false
    },

    methods: {
        toggle() {
            this.expanded = !this.expanded;
        },
    },

    computed: {
        hasContent() {
            return this.field.value !== '' && this.field.value !== null;
        },
    },
}
</script>
