import { serviceDefinition } from 'jaqen-js'

import Index from './views/Index.vue'
import Detail from './views/Detail.vue'
import Create from './views/Create.vue'
import Edit from './views/Edit.vue'

export default serviceDefinition({
    routes: [
        {
            path: '/resource/:uriKey',
            name: 'ResourceIndex',
            component: Index
        },
        {
            path: '/resource/detail/:uriKey/:key',
            name: 'ResourceDetail',
            component: Detail
        },

        {
            path: '/resource/create/:uriKey',
            name: 'ResourceCreate',
            component: Create
        },
        {
            path: '/resource/update/:uriKey/:key',
            name: 'ResourceEdit',
            component: Edit
        }
    ]
})
