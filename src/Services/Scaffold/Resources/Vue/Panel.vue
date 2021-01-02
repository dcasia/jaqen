<template>

    <div :class="{ 'w-56': subPanelOpen, 'w-0 border-none': !subPanelOpen }"
         class="relative duration-150 border-r transition-width">

        <div class="panel-dragger" @click="collapse">
            <DividingLine class="z-20 p-2 text-white transform rotate-90 rounded-full opacity-0 bg-primary"/>
        </div>

        <div class="w-full h-full py-6 space-y-8 overflow-auto sub-panel">

            <div class="space-y-1 text-gray-600 whitespace-nowrap" v-for="(item, index) of activeSubItems" :key="index">

                <div class="flex mb-4 ml-4 font-medium text-gray-700 text-1xl">
                    {{ item.label }}
                </div>

                <RouterLink v-for="(item, index) of item.items"
                            :key="index"
                            :to="item.route || { path: '/' }"
                            class="flex items-center px-4 py-2 ml-2 transition-colors duration-75 rounded-l-full cursor-pointer md:py-1 hover:bg-primary hover:font-medium hover:text-white">

                    <component v-if="item.icon" :is="item.icon" size="100%" class="w-5 h-5 mr-2 md:h-4 md:w-4"/>

                    {{ item.label }}

                </RouterLink>

            </div>

        </div>

        <div class="hidden sub-panel--dragger md:flex" @click="toggleSubmenu" v-if="hasSubMenus">

            <div class="flex items-center h-16">

                <RightC
                    theme="filled"
                    :class="{ 'rotate-180': subPanelOpen }"
                    class="transition-transform duration-150 transform"/>

            </div>

        </div>

    </div>

</template>

<script lang="ts">

    import DividingLine from '@icon-park/vue-next/es/icons/DividingLine'
    import More from '@icon-park/vue-next/es/icons/More'
    import RightC from '@icon-park/vue-next/es/icons/RightC'
    import { computed, defineComponent, watchEffect, PropType, ref } from 'vue'
    import Divider from './Divider.vue'
    import Layout from './Layout.vue'
    import { MenuItemInterface } from './Interfaces/MenuItemInterface'
    import MenuItems from './MenuItems.vue'

    export default defineComponent({
        name: 'Panel',
        components: { Layout, MenuItems, More, RightC, DividingLine, Divider },
        props: {
            collapsed: { type: Boolean, default: false },
            activeItem: { type: Object as PropType<MenuItemInterface>, required: true }
        },
        emits: [ 'update:collapsed' ],
        setup(props, { emit }) {

            const activeMenu = ref()
            const subPanelOpen = ref(true)

            /**
             * Panel
             */
            const activeSubItems = computed(() => {

                if (props.activeItem) {
                    return props.activeItem.entries
                }

                return []

            })

            const hasSubMenus = computed(() => activeSubItems.value.length > 0)

            function toggleSubmenu() {
                subPanelOpen.value = !subPanelOpen.value
            }

            watchEffect(() => {

                subPanelOpen.value = hasSubMenus.value

            })

            return {
                activeMenu,
                activeSubItems,
                toggleSubmenu,
                subPanelOpen,
                hasSubMenus,
                setActiveMenu(key: string) {
                    activeMenu.value = key
                },
                collapse() {
                    emit('update:collapsed', !props.collapsed)
                }
            }
        }
    })

</script>

<style lang="scss" scoped>

    .router-link-active, .router-link-exact-active {
        @apply text-white bg-primary font-medium;
    }

    .sub-panel {
        scrollbar-width: none;
        -ms-overflow-style: none;

        &::-webkit-scrollbar {
            @apply w-0 bg-transparent;
        }
    }

    .panel-dragger {

        @apply absolute flex items-center justify-center w-2 h-full -ml-1 cursor-pointer;

        &:before {
            content: '';
            @apply absolute z-10 w-1 h-full transition-colors duration-75;
        }

        > * {
            @apply transition-opacity duration-75;
        }

        &:hover {

            &:before {
                @apply bg-primary;
            }

            > * {
                opacity: 1
            }
        }
    }

    .sub-panel--dragger {

        @apply absolute top-0 right-0 z-10 items-start justify-center w-4 h-full -mr-2 text-gray-300 transition-colors duration-75 cursor-pointer;

        &:before {
            content: '';
            @apply absolute w-1 h-full transition-colors duration-75;
        }

        &:hover {

            @apply text-primary;

            &:before {
                @apply bg-primary;
            }

        }

    }

</style>
