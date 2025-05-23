<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const mainNavItems = computed<NavItem[]>(() => {
    if (!currentUser.value) return [];

    const role = currentUser.value.role;

    if (role === 'admin') {
        return [
            { title: 'Пользователи', href: '/users', icon: LayoutGrid },
            { title: 'Услуги', href: '/services', icon: LayoutGrid },
            { title: 'Рсписание', href: '/schedule', icon: LayoutGrid },
        ];
    }

    if (role === 'client') {
        return [{ title: 'Оказанные услуги', href: '/client-services', icon: LayoutGrid }];
    }

    if (role === 'social_worker') {
        return [{ title: 'Добавить услуги', href: '/provide-services', icon: LayoutGrid }];
    }

    return [];
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('users')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
