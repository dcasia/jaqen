<template>

    <h1 class="text-3xl py-4 border-b mb-10">
        Detail
    </h1>

    <div class="py-6 border-2 border-gray-200 rounded-lg" v-if="data">

        <div class="px-6 pb-4" v-for="field of data.fields" :key="field.attribute">

            <component :is="field.component"
                       :label="field.label"
                       :attribute="field.attribute"
                       :value="field.value"/>

        </div>

    </div>

</template>

<script lang="ts">

    import { defineComponent, onBeforeMount, ref } from 'vue'
    import { useRoute } from 'vue-router'
    import api, { ResourceDetailResponse } from '../Api'

    export default defineComponent({
        name: 'Detail',
        setup() {

            const route = useRoute()
            const data = ref<ResourceDetailResponse>()

            onBeforeMount(async () => {

                data.value = await api.getDetailsField(route.params.uriKey as string, route.params.key as string)

            })

            return {
                data
            }
        }
    })

</script>
