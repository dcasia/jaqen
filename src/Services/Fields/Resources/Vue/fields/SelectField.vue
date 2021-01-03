<template>

    <EditableField v-bind="$props">

        <template #input>

            <select v-model="model"
                    :id="attribute"
                    :name="attribute"
                    :class="{ 'border-red-500': errorBag?.length }"
                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">

                <option v-for="(label, value) in additionalInformation" :value="value" :key="value">
                    {{ label }}
                </option>

            </select>

        </template>

    </EditableField>

</template>

<script lang="ts">

    import { defineComponent, ref } from 'vue'
    import EditableField from './EditableField.vue'

    export default defineComponent({
        name: 'SelectField',
        components: { EditableField },
        extends: EditableField,
        setup(props, context) {

            const model = ref(props.value ?? '')

            return {
                ...EditableField.setup!(props, context),
                model,
                getValue() {
                    return model.value
                }
            }
        }
    })

</script>
