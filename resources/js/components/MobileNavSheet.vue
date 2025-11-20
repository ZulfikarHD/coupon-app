<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChartColumn, LayoutGrid, LogOut, Menu, ScanLine, Settings, Ticket, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');

const isCurrentRoute = computed(
    () => (url: string) => urlIsActive(url, page.url),
);

const activeItemStyles = computed(
    () => (url: string) =>
        isCurrentRoute.value(url)
            ? 'bg-accent text-accent-foreground'
            : '',
);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
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

    // Add User Management for admins
    if (isAdmin.value) {
        items.push({
            title: 'User Management',
            href: '/users',
            icon: Users,
        });
    }

    return items;
});

const kuponSubItems: NavItem[] = [
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
];
</script>

<template>
    <Sheet>
        <SheetTrigger :as-child="true">
            <Button
                variant="ghost"
                size="icon"
                class="mr-2 h-9 w-9"
            >
                <Menu class="h-5 w-5" />
            </Button>
        </SheetTrigger>
        <SheetContent side="bottom" class="h-[80vh] max-h-[600px] p-0">
            <div class="flex h-full flex-col">
                <SheetHeader class="border-b px-6 py-4">
                    <SheetTitle class="text-left">Menu</SheetTitle>
                </SheetHeader>
                <div class="flex-1 overflow-y-auto px-4 py-4">
                    <nav class="space-y-1">
                        <Link
                            v-for="item in mainNavItems"
                            :key="item.title"
                            :href="item.href"
                            class="flex items-center gap-x-3 rounded-lg px-3 py-3 text-base font-medium transition-colors"
                            :class="activeItemStyles(item.href)"
                        >
                            <component
                                v-if="item.icon"
                                :is="item.icon"
                                class="h-5 w-5"
                            />
                            {{ item.title }}
                        </Link>
                        <!-- Kupon Submenu -->
                        <div v-if="isCurrentRoute('/coupons')" class="ml-8 mt-1 space-y-1">
                            <Link
                                v-for="subItem in kuponSubItems"
                                :key="subItem.title"
                                :href="subItem.href"
                                class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                :class="activeItemStyles(subItem.href)"
                            >
                                <span class="text-xs">•</span>
                                {{ subItem.title }}
                            </Link>
                        </div>
                    </nav>
                </div>
                <div class="border-t px-4 py-4">
                    <div class="space-y-1">
                        <Link
                            :href="edit()"
                            class="flex items-center gap-x-3 rounded-lg px-3 py-3 text-base font-medium transition-colors hover:bg-accent"
                        >
                            <Settings class="h-5 w-5" />
                            Pengaturan
                        </Link>
                        <Link
                            :href="logout()"
                            class="flex items-center gap-x-3 rounded-lg px-3 py-3 text-base font-medium text-destructive transition-colors hover:bg-accent"
                        >
                            <LogOut class="h-5 w-5" />
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>
