import { Component } from 'vue'

type newLocation = {
    name: string
}

interface PanelEntry {
    label: string
    icon?: Component
    route?: newLocation
}

interface Entry {
    label: string,
    items: PanelEntry[]
}

export interface MenuItemInterface {
    label: string
    icon?: Component
    fixed: boolean
    route?: newLocation
    entries: Entry[]
}
