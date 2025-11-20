<script setup lang="ts">
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChartColumn, LayoutGrid, ScanLine, Ticket, Users, User } from 'lucide-vue-next';
import { computed } from 'vue';
import { edit } from '@/routes/profile';

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');

// Base nav items for all users
const baseNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Kupon',
        href: '/coupons',
        icon: Ticket,
    },
];

// Nav items that appear after the Scan FAB
// Regular users: Laporan, Profile
// Admin users: Laporan, Users
const rightNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Laporan',
            href: '/reports',
            icon: ChartColumn,
        },
    ];

    if (isAdmin.value) {
        items.push({
            title: 'Users',
            href: '/users',
            icon: Users,
        });
    } else {
        items.push({
            title: 'Profile',
            href: edit(),
            icon: User,
        });
    }

    return items;
});

const isActive = (href: string) => {
    return urlIsActive(href, page.url);
};
</script>

<template>
    <nav
        class="fixed bottom-0 left-0 right-0 z-50 flex h-20 items-end justify-around border-t border-sidebar-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 pb-safe md:hidden"
    >
        <!-- Dashboard -->
        <Link
            :href="baseNavItems[0].href"
            class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-2 transition-colors"
            :class="
                isActive(baseNavItems[0].href)
                    ? 'text-primary'
                    : 'text-muted-foreground hover:text-foreground'
            "
        >
            <component
                :is="baseNavItems[0].icon"
                class="h-5 w-5"
                :class="isActive(baseNavItems[0].href) ? 'text-primary' : ''"
            />
            <span class="text-xs font-medium">{{ baseNavItems[0].title }}</span>
        </Link>

        <!-- Kupon -->
        <Link
            :href="baseNavItems[1].href"
            class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-2 transition-colors"
            :class="
                isActive(baseNavItems[1].href)
                    ? 'text-primary'
                    : 'text-muted-foreground hover:text-foreground'
            "
        >
            <component
                :is="baseNavItems[1].icon"
                class="h-5 w-5"
                :class="isActive(baseNavItems[1].href) ? 'text-primary' : ''"
            />
            <span class="text-xs font-medium">{{ baseNavItems[1].title }}</span>
        </Link>

        <!-- FAB Scan Button (Center) -->
        <div class="relative flex flex-1 items-center justify-center">
            <Link
                href="/scan"
                class="absolute bottom-2 flex h-14 w-14 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg transition-all hover:scale-110 hover:shadow-xl"
                :class="isActive('/scan') ? 'ring-2 ring-primary ring-offset-2' : ''"
            >
                <ScanLine class="h-6 w-6" />
            </Link>
        </div>

        <!-- Right side items (Laporan + Profile/Users) -->
        <template v-for="(item, index) in rightNavItems" :key="item.href">
            <Link
                :href="item.href"
                class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-2 transition-colors"
                :class="
                    isActive(item.href)
                        ? 'text-primary'
                        : 'text-muted-foreground hover:text-foreground'
                "
            >
                <component
                    :is="item.icon"
                    class="h-5 w-5"
                    :class="isActive(item.href) ? 'text-primary' : ''"
                />
                <span class="text-xs font-medium">{{ item.title }}</span>
            </Link>
        </template>
    </nav>
</template>

