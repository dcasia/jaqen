<template>

    <div v-for="(item, index) of items"
         :key="index"
         @click.prevent="setActiveItem(item)"
         class="relative tracking-tight font-medium whitespace-nowrap flex items-center h-12 px-5 duration-150
                cursor-pointer hover:text-primary transition-color"
         :class="[
            selected === item.label ? 'text-primary' : 'text-gray-700',
            collapsed ? 'w-16': 'w-56'
         ]">

        <RouterLink :to="item.route || {}" class="flex items-center justify-center">

            <template #default="{ isActive, isExactActive }">

                <div v-if="isActive" class="absolute left-0 w-1 h-12 rounded-r bg-primary"/>

                <component :is="item.icon" :size="22"/>

                <div v-if="item.label"
                     class="w-full ml-4 overflow-hidden transition-opacity duration-150"
                     :class="[
                         collapsed ? 'opacity-0' : 'opacity-1'
                     ]">

                    {{ item.label }}

                </div>

            </template>

        </RouterLink>

    </div>

</template>

<script lang="ts">

    import { defineComponent, PropType } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import { MenuItemInterface } from './Interfaces/MenuItemInterface'

    export default defineComponent({
        name: 'MenuItems',
        emits: [ 'update:selected' ],
        props: {
            selected: { type: String, default: null },
            collapsed: { type: Boolean, default: true },
            items: { type: Array as PropType<MenuItemInterface[]>, required: true }
        },
        setup(props, { emit }) {
            const router = useRouter()
            const route = useRoute()
            return {
                setActiveItem(item: MenuItemInterface) {

                    // if (item.route) {
                    //
                    //     router.push(item.route)
                    //
                    // }
                    //
                    // emit('update:selected', route.name)

                }
            }
        }
    })

</script>
