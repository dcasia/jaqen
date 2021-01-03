import { serviceDefinition } from 'jaqen-js'

import ReadOnlyField from './fields/ReadOnlyField.vue'
import EditableField from './fields/EditableField.vue'
import TextField from './fields/TextField.vue'
import PasswordField from './fields/PasswordField.vue'
import SelectField from './fields/SelectField.vue'

export default serviceDefinition({
    components: {
        TextField,
        ReadOnlyField,
        EditableField,
        PasswordField,
        SelectField
    }
})
