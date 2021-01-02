<template>

    <div class="flex h-full">

        <div class="hidden md:flex">

            <slot name="left-area">

                <div class="flex flex-col">

                    <div class="relative flex flex-col items-center justify-center flex-shrink-0 h-16 border-r">

                        <slot name="logo">
                            <div class="p-4">Jaqen</div>
                        </slot>

                    </div>

                    <slot name="menu">

                        <div class="relative flex flex-col items-center flex-1 py-8 space-y-2 border-r">

                            <slot name="fixed-top-menu">
                                Top
                            </slot>

                        </div>

                        <div class="relative flex flex-col items-center pb-2 space-y-2 border-r">

                            <slot name="fixed-bottom-menu">
                                Bottom
                            </slot>

                        </div>

                    </slot>

                </div>

                <slot name="outer-menu">

                    <div class="w-56 border-r flex items-center justify-center">
                        Panel
                    </div>

                </slot>

            </slot>

        </div>

        <div class="flex flex-col flex-1">

            <slot name="right-area">

                <div class="relative h-16 border-b">

                    <slot name="top-bar">

                        <div class="flex items-center justify-center h-full">
                            Top Bar
                        </div>

                    </slot>

                </div>

                <div class="flex-1" style="background: #F8F9FD">

                    <slot name="body">

                        <div class="flex items-center justify-center h-full">
                            Main Content
                        </div>

                    </slot>

                </div>

            </slot>

        </div>

    </div>

</template>

<script lang="ts">

    import { defineComponent } from 'vue'

    export default defineComponent({
        name: 'Layout'
    })

</script>

<style>

    body, html, #app {
        height: 100%;
    }

</style>
