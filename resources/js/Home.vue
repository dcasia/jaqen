<template>
    {{ $route.name }}
    <RouterView name="blog"/>
</template>

<script lang="ts">

    import { defineComponent } from 'vue'

    export default defineComponent({
        name: 'Home',
        setup() {

        }
    })

</script>
