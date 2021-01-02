import { http } from 'jaqen-js'
import { MenuItemInterface } from './Interfaces/MenuItemInterface'

export default {

    async fetchSidebar(): Promise<MenuItemInterface[] | null> {
        return http.fetch(`/jaqen-api/scaffold/sidebar`)
    }

}
