<template>

    <div class="flex h-full">

        <div class="md:flex"
             :class="[ isMenuOpen ? 'flex h-full fixed w-full bg-white pt-16 transition-all duration-300' : 'hidden' ]">

            <div class="flex flex-col">

                <div class="relative flex-col items-center justify-center flex-shrink-0 hidden h-16 border-r md:flex">

                    <slot name="logo"/>

                    <Divider :collapsed="collapsed" direction="bottom"/>

                </div>

                <div class="relative flex flex-col items-center flex-1 py-4 space-y-2 border-r">

                    <MenuItems :collapsed="collapsed" :items="nonFixedItems" v-model:selected="activeItem"/>

                </div>

                <div class="relative flex flex-col items-center pb-2 space-y-2 border-r">

                    <Divider :collapsed="collapsed" direction="top"/>

                    <MenuItems :collapsed="collapsed" :items="fixedItems" v-model:selected="activeItem"/>

                </div>

            </div>

            <Panel class="flex-1" v-model:collapsed="collapsed" :active-item="computedActiveItem"/>

        </div>

        <!-- right side -->
        <div class="flex flex-col flex-1">

            <div class="relative h-16 border-b">

                <div class="flex items-center justify-between h-full px-4">

                    <div class="relative flex flex-col items-center justify-center h-8 pr-4 border-r md:hidden">

                        <slot name="logo"/>

                    </div>

                    <div>Top Bar</div>

                    <div class="relative flex flex-col items-center justify-center h-8 pl-4 border-l md:hidden">

                        <Hamburger :size="22" class="md:hidden" @click="openMenu"/>

                    </div>

                </div>

            </div>

            <div class="flex-1 bg-gray-100 p-8">

                <slot name="body">

                    <div class="flex items-center justify-center h-full">
                        Main Content
                    </div>

                </slot>

            </div>

        </div>

    </div>

</template>

<script lang="ts">

    import Hamburger from '@icon-park/vue-next/es/icons/Hamburger'
    import DividingLine from '@icon-park/vue-next/es/icons/DividingLine'
    import More from '@icon-park/vue-next/es/icons/More'
    import RightC from '@icon-park/vue-next/es/icons/RightC'
    import { computed, defineComponent, onUnmounted, ref } from 'vue'
    import { useRoute } from 'vue-router'
    import Divider from './Divider.vue'
    import { MenuItemInterface } from './Interfaces/MenuItemInterface'
    import MenuItems from './MenuItems.vue'
    import Panel from './Panel.vue'
    import Api from './Api'

    export default defineComponent({
        name: 'Scaffold',
        components: { MenuItems, More, RightC, DividingLine, Divider, Panel, Hamburger },
        setup() {

            const menu = ref<MenuItemInterface[]>([])
            const currentRoute = useRoute()
            const activeItem = ref<MenuItemInterface>()
            const computedActiveItem = computed(() => {
                return menu.value.find(item => {
                    return activeItem.value ? item === activeItem.value : false
                })
            })

            Api.fetchSidebar().then(result => {

                if (result) {

                    menu.value = result

                    /**
                     * Set initial menu item once menu is loaded
                     */
                    activeItem.value = menu.value.find((item: MenuItemInterface) => {
                        return item.route?.name === currentRoute.name
                            || item.entries.find(
                                item => item.items.find(item => item.route?.name === currentRoute.name)
                            )
                    })

                }

            })


            const collapsed = ref(true)
            const isMenuOpen = ref(false)

            function closeMenu() {
                isMenuOpen.value = false
            }

            window.addEventListener('resize', closeMenu)

            onUnmounted(() => window.removeEventListener('resize', closeMenu))

            return {
                collapsed,
                isMenuOpen,
                activeItem,
                computedActiveItem,
                fixedItems: computed(() => menu.value.filter(({ fixed }) => fixed)),
                nonFixedItems: computed(() => menu.value.filter(({ fixed }) => !fixed)),
                openMenu() {
                    isMenuOpen.value = !isMenuOpen.value
                }
            }

        }
    })

</script>

<style>

    body, html {
        height: 100%;
    }

</style>
