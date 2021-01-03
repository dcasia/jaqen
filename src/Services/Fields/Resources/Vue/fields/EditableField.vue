<template>

    <label class="block text-gray-700 text-sm font-bold mb-2" :for="attribute">
        {{ label }}
    </label>

    <slot name="input">

        <input v-model="model"
               :name="attribute"
               :disabled="disabled"
               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
               :class="{ 'border-red-500': errorBag?.length }"
               :id="attribute"
               :type="type"
               :placeholder="label">

    </slot>

    <ul class="mt-2">
        <li class="text-red-500 text-xs italic" v-for="error of errorBag">
            {{ error }}
        </li>
    </ul>

</template>

<script lang="ts">

    import { computed, defineComponent, ref } from 'vue'

    export default defineComponent({
        name: 'EditableField',
        props: {
            label: { type: String, required: true },
            attribute: { type: String, required: true },
            value: { type: String, required: true },
            additionalInformation: { type: Object, required: true },
            error: { type: Object, required: true }
        },
        setup(props) {

            const model = ref(props.value ?? '')

            return {
                model,
                errorBag: computed(() => {

                    if (props.error) {
                        return props.error[ props.attribute ] ?? []
                    }

                    return []

                }),
                type: 'text',
                disabled: false,
                includeIfNull: true,
                getValue() {
                    return model.value
                }
            }
        }
    })

</script>
