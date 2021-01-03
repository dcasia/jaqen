import { serviceDefinition } from 'jaqen-js'
import Home from './Home.vue'

import './scss/tailwind.scss'
import 'typeface-roboto'

import Scaffold from '../../src/Services/Scaffold/Resources/Vue/Bootstrap'
import ResourceManager from '../../src/Services/ResourceManager/Resources/Vue/Bootstrap'
import Fields from '../../src/Services/Fields/Resources/Vue/Bootstrap'

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
    Scaffold,
    ResourceManager,
    Fields
]
