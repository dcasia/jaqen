<template>

    <div class="absolute w-3/5 transition-width duration-75"
         :class="[
             collapsed ? 'w-3/5' : 'w-full',
             direction === 'top' && 'border-t top-0',
             direction === 'bottom' && 'border-b bottom-0',
         ]"/>

</template>

<script lang="ts">

    import { defineComponent } from 'vue'

    export default defineComponent({
        name: 'Divider',
        props: {
            collapsed: { type: Boolean, required: true },
            direction: { type: String, required: true }
        }
    })

</script>
