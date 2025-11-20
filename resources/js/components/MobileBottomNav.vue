<script setup lang="ts">
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChartColumn, LayoutGrid, ScanLine, Ticket } from 'lucide-vue-next';

const page = usePage();

const navItems: NavItem[] = [
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
    {
        title: 'Laporan',
        href: '/reports',
        icon: ChartColumn,
    },
];

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
            :href="navItems[0].href"
            class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-2 transition-colors"
            :class="
                isActive(navItems[0].href)
                    ? 'text-primary'
                    : 'text-muted-foreground hover:text-foreground'
            "
        >
            <component
                :is="navItems[0].icon"
                class="h-5 w-5"
                :class="isActive(navItems[0].href) ? 'text-primary' : ''"
            />
            <span class="text-xs font-medium">{{ navItems[0].title }}</span>
        </Link>

        <!-- Kupon -->
        <Link
            :href="navItems[1].href"
            class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-2 transition-colors"
            :class="
                isActive(navItems[1].href)
                    ? 'text-primary'
                    : 'text-muted-foreground hover:text-foreground'
            "
        >
            <component
                :is="navItems[1].icon"
                class="h-5 w-5"
                :class="isActive(navItems[1].href) ? 'text-primary' : ''"
            />
            <span class="text-xs font-medium">{{ navItems[1].title }}</span>
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

        <!-- Laporan -->
        <Link
            :href="navItems[2].href"
            class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-2 transition-colors"
            :class="
                isActive(navItems[2].href)
                    ? 'text-primary'
                    : 'text-muted-foreground hover:text-foreground'
            "
        >
            <component
                :is="navItems[2].icon"
                class="h-5 w-5"
                :class="isActive(navItems[2].href) ? 'text-primary' : ''"
            />
            <span class="text-xs font-medium">{{ navItems[2].title }}</span>
        </Link>

        <!-- Placeholder for spacing -->
        <div class="flex flex-1"></div>
    </nav>
</template>

