<template>

    <h1 class="text-3xl py-4 border-b mb-10">
        Page Resources
    </h1>

    <RouterLink :to="{ name: 'ResourceCreate', params: { uriKey: $route.params.uriKey } }"
                class="px-5 py-3 flex text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded-lg">
        Create
    </RouterLink>

    <FilterPanel @on-change="applyFilter" :errors="errorsBag"/>

    <div class="-mx-4 p-4 overflow-x-auto">

        <div class="inline-block min-w-full shadow rounded-lg overflow-hidden" v-if="indexData">

            <table class="min-w-full leading-normal">

                <thead>

                <tr>
                    <th v-for="header of headers" :key="header"
                        class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        {{ header }}
                    </th>
                </tr>

                </thead>

                <tbody>

                <tr v-for="resource of indexData.resources" :key="resource.key">

                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm" v-for="field of resource.fields">
                        <component :is="field.component" v-bind="field"/>
                    </td>

                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <div class="flex items-center">
                            <ViewIcon class="cursor-pointer h-4 text-gray-600 hover:text-gray-900"
                                      @click="viewResource(resource.key)"/>
                            <EditIcon class="cursor-pointer h-4 text-gray-600 hover:text-gray-900"
                                      @click="editResource(resource.key)"/>
                            <DeleteIcon class="cursor-pointer text-gray-600 hover:text-red-600"
                                        @click="deleteResource(resource.key)"/>
                        </div>
                    </td>

                </tr>

                </tbody>

            </table>

            <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center">

                <span class="text-xs text-gray-900">
                    Showing {{ indexData.from }} to {{ indexData.to }} of {{ indexData.total }} Entries
                </span>

                <div class="inline-flex mt-2">
                    <button @click="previousPage"
                            :class="{ 'opacity-50': currentPage <= 1 }"
                            :disabled="currentPage <= 1"
                            class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-l">
                        Prev
                    </button>
                    <button @click="nextPage"
                            :class="{ 'opacity-50': hasNextPage }"
                            :disabled="hasNextPage"
                            class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-r">
                        Next
                    </button>
                </div>

            </div>

        </div>

    </div>

</template>

<script lang="ts">

    import { defineComponent, ref, watchEffect, computed } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import FilterPanel from '../components/FilterPanel.vue'
    import DeleteIcon from '../components/Icons/DeleteIcon.vue'
    import EditIcon from '../components/Icons/EditIcon.vue'
    import ViewIcon from '../components/Icons/ViewIcon.vue'
    import api, { ResourceIndexInterface } from '../Api'

    export default defineComponent({
        name: 'Index',
        components: { DeleteIcon, ViewIcon, EditIcon, FilterPanel },
        setup() {

            const route = useRoute()
            const router = useRouter()
            const indexData = ref<ResourceIndexInterface>()
            const headers = ref<string[]>([])
            const currentFilter = ref<string>('')
            const errorsBag = ref<any>({})
            const currentPage = ref(1)

            watchEffect(async () => {

                await api.getIndex(route.params.uriKey as string, currentFilter.value, currentPage.value)
                    .then(data => {

                        indexData.value = data
                        errorsBag.value = {}

                        if (indexData.value.resources[ 0 ]) {

                            headers.value = indexData.value.resources[ 0 ].fields.map(field => field.label)
                            headers.value.push('Actions')

                        }

                    })
                    .catch(({ errors }) => {

                        const errorPayload: any = {}

                        for (const error in errors) {

                            errorPayload[ error ] = errors[ error ]

                        }

                        errorsBag.value = errorPayload

                    })

            })

            return {
                indexData,
                headers,
                errorsBag,
                currentPage,
                hasNextPage: computed(() => {

                    if (indexData.value !== undefined) {
                        return currentPage.value >= indexData.value.lastPage
                    }

                    return false

                }),
                previousPage() {
                    currentPage.value--
                },
                nextPage() {
                    currentPage.value++
                },
                applyFilter(value: string) {
                    currentFilter.value = value
                },
                editResource(key: string) {

                    router.push({
                        name: 'ResourceEdit',
                        params: {
                            uriKey: route.params.uriKey,
                            key
                        }
                    })

                },
                viewResource(key: string) {

                    router.push({
                        name: 'ResourceDetail',
                        params: {
                            uriKey: route.params.uriKey,
                            key
                        }
                    })

                },
                async deleteResource(key: string) {

                    await api.deleteResource(route.params.uriKey as string, key)
                    await router.go(0)

                }
            }
        }
    })

</script>
