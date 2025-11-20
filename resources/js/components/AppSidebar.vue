<script setup lang="ts">
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
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChartColumn, LayoutGrid, ScanLine, Ticket, Users, Shield } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export interface NavItemWithSubmenu extends NavItem {
    items?: NavItem[];
}

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');

const mainNavItems = computed<NavItemWithSubmenu[]>(() => {
    const items: NavItemWithSubmenu[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Kupon',
            href: '/coupons',
            icon: Ticket,
            items: [
                {
                    title: 'Semua Kupon',
                    href: '/coupons',
                    icon: Ticket,
                },
                {
                    title: 'Buat Baru',
                    href: '/coupons/create',
                    icon: Ticket,
                },
            ],
        },
        {
            title: 'Scan Kupon',
            href: '/scan',
            icon: ScanLine,
        },
        {
            title: 'Laporan',
            href: '/reports',
            icon: ChartColumn,
        },
    ];

    // Add admin menu items if user is admin
    if (isAdmin.value) {
        items.push({
            title: 'User Management',
            href: '/users',
            icon: Users,
            items: [
                {
                    title: 'Semua User',
                    href: '/users',
                    icon: Users,
                },
                {
                    title: 'Tambah User',
                    href: '/users/create',
                    icon: Shield,
                },
            ],
        });
    }

    return items;
});
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
