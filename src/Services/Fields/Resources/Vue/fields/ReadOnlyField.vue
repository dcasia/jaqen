<script lang="ts">

    import { defineComponent } from 'vue'
    import EditableField from './EditableField.vue'

    export default defineComponent({
        name: 'ReadOnlyField',
        extends: EditableField,
        setup(...data) {
            return {
                ...EditableField.setup!(...data),
                disabled: true
            }
        }
    })

</script>
