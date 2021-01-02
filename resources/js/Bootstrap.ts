import { serviceDefinition } from 'jaqen-js'
import Home from './Home.vue'

import './scss/tailwind.scss'
import 'typeface-roboto'

import Scaffold from '../../src/Services/Scaffold/Resources/Vue/Boostrap'

const coreDefinition = serviceDefinition({
    routes: [
        {
            path: '/',
            name: 'home',
            component: Home
        }
    ]
})

export default [
    coreDefinition,
    Scaffold
]
