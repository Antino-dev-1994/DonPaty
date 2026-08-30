<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Boxes, ClipboardCheck, LayoutDashboard, PackageOpen, ScrollText, ShieldCheck, UsersRound } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { Auth, NavItem } from '@/types';

const page = usePage<{ auth: Auth }>();
const hasPermission = (permission: string) => page.props.auth.permissions.includes(permission);
const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Panel principal',
        href: dashboard(),
        icon: LayoutDashboard,
    },
    ...(hasPermission('people.view') ? [{ title: 'Personas', href: '/people', icon: UsersRound }] : []),
    ...(hasPermission('users.manage') ? [{ title: 'Usuarios', href: '/users', icon: ShieldCheck }] : []),
    ...(hasPermission('roles.manage') ? [{ title: 'Roles y permisos', href: '/roles', icon: ShieldCheck }] : []),
    ...(hasPermission('authorizations.approve') || hasPermission('authorizations.request') ? [{ title: 'Autorizaciones', href: '/authorizations', icon: ClipboardCheck }] : []),
    ...(hasPermission('audit.view') ? [{ title: 'Auditoría', href: '/audit', icon: ScrollText }] : []),
    ...(hasPermission('catalog.view') ? [{ title: 'Catálogo', href: '/catalog/items', icon: Boxes }] : []),
    ...(hasPermission('inventory.view') ? [{ title: 'Inventario', href: '/inventory', icon: PackageOpen }] : []),
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
