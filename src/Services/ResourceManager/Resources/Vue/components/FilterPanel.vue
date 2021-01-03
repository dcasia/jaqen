<template>

    <form @submit.prevent="applyFilter" class="border p-4 mt-4">

        <div class="text-2xl py-4 border-b mb-4">
            Filters
        </div>

        <div v-for="filter of filters" :key="filter.uriKey">

            <component v-for="field of filter.fields"
                       :ref="ref => setItemRef(filter.uriKey, ref)"
                       :key="field.attribute"
                       :is="field.component"
                       :error="errors[filter.uriKey]"
                       v-bind="field"/>

        </div>

        <button
            class="px-4 py-2 mt-2 text-sm font-medium leading-5 text-white bg-gray-600 border border-transparent rounded-lg hover:bg-gray-700">
            Apply
        </button>

    </form>

</template>

<script lang="ts">

    import { defineComponent, onBeforeMount, onBeforeUpdate, ref } from 'vue'
    import { useRoute } from 'vue-router'
    import api, { FiltersResponseInterface } from '../Api'

    export default defineComponent({
        name: 'FilterPanel',
        props: { errors: { type: Object } },
        emits: [ 'on-change' ],
        setup(props, { emit }) {

            const route = useRoute()
            const filters = ref<FiltersResponseInterface[]>([])

            let itemRefs: Record<string, ReturnType<typeof defineComponent>[]> = {}

            onBeforeMount(async () => {

                filters.value = await api.getFilters(route.params.uriKey as string)

                for (const filter of filters.value) {

                    props.errors![ filter.uriKey ] = ref({ keyword: [], 'title': [] })

                }

            })

            const setItemRef = (uriKey: string, component: ReturnType<typeof defineComponent>) => {

                if (!itemRefs[ uriKey ]) {
                    itemRefs[ uriKey ] = []
                }

                itemRefs[ uriKey ].push(component)

            }

            onBeforeUpdate(() => {
                itemRefs = {}
            })

            return {
                filters,
                setItemRef,
                async applyFilter() {

                    const formData: Record<string, Record<string, any>> = {}

                    for (const filter in itemRefs) {

                        if (!formData[ filter ]) {
                            formData[ filter ] = {}
                        }

                        for (const field of itemRefs[ filter ]) {

                            const value = field.getValue()

                            if (value) {

                                formData[ filter ][ field.attribute ] = value

                            }

                        }

                    }

                    emit('on-change', btoa(JSON.stringify(formData)))

                }
            }
        }
    })

</script>
