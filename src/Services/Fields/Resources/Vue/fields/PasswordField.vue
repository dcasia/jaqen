<script lang="ts">

    import { defineComponent } from 'vue'
    import EditableField from './EditableField.vue'

    export default defineComponent({
        name: 'PasswordField',
        extends: EditableField,
        setup(...data) {
            return {
                ...EditableField.setup!(...data),
                includeIfNull: false,
                type: 'password'
            }
        }
    })

</script>
