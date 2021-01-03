<template>

    <h1 class="text-3xl py-4 border-b mb-10">
        {{ buttonText }}
    </h1>

    <div class="py-6 border-2 border-gray-200 rounded-lg ">

        <form @submit.prevent="onSubmit" class="space-y-3">

            <div class="border-b border-l-0 border-r-0 border-dashed px-6 pb-4" v-for="field of fields"
                 :key="field.attribute">

                <component class="mb-6"
                           :ref="setItemRef"
                           :is="field.component"
                           :label="field.label"
                           :attribute="field.attribute"
                           :value="field.value"
                           :error="errorBag"/>

            </div>

            <button type="submit"
                    class="ml-6 px-4 py-2 text-sm font-medium leading-5 text-white bg-gray-600 border border-transparent rounded-lg hover:bg-gray-700">
                {{ buttonText }}
            </button>

        </form>

    </div>

</template>

<script lang="ts">

    import { defineComponent, onBeforeMount, onBeforeUpdate, ref } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import { useError } from 'jaqen-js'
    import api, { ResourceCreateSchemaInterface } from '../Api'

    export default defineComponent({
        name: 'Create',
        setup() {

            const route = useRoute()
            const router = useRouter()
            const fields = ref<ResourceCreateSchemaInterface[]>([])
            const itemRefs: ReturnType<typeof defineComponent>[] = []
            const { errorBag } = useError()

            onBeforeMount(async () => {

                fields.value = await api.getCreateFields(route.params.uriKey as string)

            })

            const setItemRef = (el: ReturnType<typeof defineComponent>) => {
                itemRefs.push(el)
            }

            onBeforeUpdate(() => {
                while (itemRefs.length) {
                    itemRefs.pop()
                }
            })

            return {
                fields,
                errorBag,
                setItemRef,
                buttonText: 'Create',
                async onSubmit() {

                    const formData = new FormData

                    for (const element of itemRefs) {

                        formData.append(element.attribute, element.getValue())

                    }

                    await api.createResource(route.params.uriKey as string, formData)
                        .then(() => {

                            return router.push({
                                name: 'ResourceIndex',
                                params: {
                                    uriKey: route.params.uriKey
                                }
                            })

                        })
                        .catch(response => {

                            errorBag.value = response.errors

                        })

                }
            }
        }
    })

</script>
