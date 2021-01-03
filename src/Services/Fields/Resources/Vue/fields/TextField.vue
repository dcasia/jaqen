<template>

    <div class="text-gray-700 text-sm" :data-attribute="attribute">
        {{ value }}
    </div>

</template>


<script lang="ts">

    import { defineComponent } from 'vue'

    export default defineComponent({
        name: 'TextField',
        props: [ 'label', 'attribute', 'value' ]
    })

</script>
