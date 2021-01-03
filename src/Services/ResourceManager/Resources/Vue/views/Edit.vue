<script lang="ts">

    import { defineComponent, onBeforeMount, onBeforeUpdate, ref } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import Create from '../views/Create.vue'
    import api, { ResourceCreateSchemaInterface } from '../Api'
    import { useError } from 'jaqen-js'

    export default defineComponent({
        name: 'Edit',
        extends: Create,
        setup() {

            const route = useRoute()
            const router = useRouter()
            const fields = ref<ResourceCreateSchemaInterface[]>([])
            const itemRefs: ReturnType<typeof defineComponent>[] = []
            const { errorBag } = useError()

            onBeforeMount(async () => {

                fields.value = (await api.getEditFields(route.params.uriKey as string, route.params.key as string)).fields

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
                buttonText: 'Update',
                async onSubmit() {

                    const formData = new FormData

                    for (const element of itemRefs) {

                        const value = element.getValue()

                        if (element.includeIfNull === false && value === '') {

                            continue

                        }

                        formData.append(element.attribute, value)

                    }

                    await api.updateResource(route.params.uriKey as string, route.params.key as string, formData)
                        .then(() => {

                            return router.push({
                                name: 'ResourceIndex',
                                params: {
                                    uriKey: route.params.uriKey
                                }
                            })

                        })
                        .catch(response => {

                            errorBag.value = { ...response.errors }

                        })

                }
            }
        }
    })

</script>
