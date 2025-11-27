<script setup lang="ts">
import { urlIsActive } from '@/lib/utils';
import { useHaptic } from '@/composables/useHaptic';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChartColumn, LayoutGrid, ScanLine, Ticket, Users, User } from 'lucide-vue-next';
import { computed } from 'vue';
import { edit } from '@/routes/profile';

const page = usePage();
const { trigger } = useHaptic();
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

const handleNavClick = () => {
    trigger('light');
};

const handleScanClick = () => {
    trigger('medium');
};
</script>

<template>
    <nav
        class="fixed bottom-0 left-0 right-0 z-50 md:hidden"
    >
        <!-- Glass Background -->
        <div class="glass-strong border-t border-border/50">
            <div class="flex h-20 items-end justify-around pb-safe">
                <!-- Dashboard -->
                <Link
                    :href="baseNavItems[0].href"
                    class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-3 transition-all duration-200 press-effect"
                    :class="
                        isActive(baseNavItems[0].href)
                            ? 'text-sky-600 dark:text-sky-400'
                            : 'text-muted-foreground hover:text-foreground'
                    "
                    @click="handleNavClick"
                >
                    <div
                        :class="[
                            'flex h-8 w-8 items-center justify-center rounded-xl transition-all duration-300',
                            isActive(baseNavItems[0].href)
                                ? 'bg-sky-100 dark:bg-sky-500/20'
                                : '',
                        ]"
                    >
                        <component
                            :is="baseNavItems[0].icon"
                            class="h-5 w-5 transition-transform duration-200"
                            :class="isActive(baseNavItems[0].href) ? 'scale-110' : ''"
                        />
                    </div>
                    <span
                        class="text-[10px] font-medium transition-all duration-200"
                        :class="isActive(baseNavItems[0].href) ? 'font-semibold' : ''"
                    >
                        {{ baseNavItems[0].title }}
                    </span>
                </Link>

                <!-- Kupon -->
                <Link
                    :href="baseNavItems[1].href"
                    class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-3 transition-all duration-200 press-effect"
                    :class="
                        isActive(baseNavItems[1].href)
                            ? 'text-sky-600 dark:text-sky-400'
                            : 'text-muted-foreground hover:text-foreground'
                    "
                    @click="handleNavClick"
                >
                    <div
                        :class="[
                            'flex h-8 w-8 items-center justify-center rounded-xl transition-all duration-300',
                            isActive(baseNavItems[1].href)
                                ? 'bg-sky-100 dark:bg-sky-500/20'
                                : '',
                        ]"
                    >
                        <component
                            :is="baseNavItems[1].icon"
                            class="h-5 w-5 transition-transform duration-200"
                            :class="isActive(baseNavItems[1].href) ? 'scale-110' : ''"
                        />
                    </div>
                    <span
                        class="text-[10px] font-medium transition-all duration-200"
                        :class="isActive(baseNavItems[1].href) ? 'font-semibold' : ''"
                    >
                        {{ baseNavItems[1].title }}
                    </span>
                </Link>

                <!-- FAB Scan Button (Center) -->
                <div class="relative flex flex-1 items-center justify-center">
                    <Link
                        href="/scan"
                        :class="[
                            'absolute -top-4 flex h-16 w-16 items-center justify-center rounded-2xl',
                            'bg-gradient-to-br from-sky-500 to-cyan-500 text-white',
                            'shadow-lg shadow-sky-500/30',
                            'transition-all duration-300',
                            'hover:scale-105 hover:shadow-xl hover:shadow-sky-500/40',
                            'press-effect',
                            isActive('/scan') ? 'ring-4 ring-sky-200 dark:ring-sky-800' : '',
                        ]"
                        @click="handleScanClick"
                    >
                        <ScanLine class="h-7 w-7" />
                    </Link>
                    <span class="mt-8 text-[10px] font-medium text-muted-foreground">Scan</span>
                </div>

                <!-- Right side items (Laporan + Profile/Users) -->
                <template v-for="item in rightNavItems" :key="item.href">
                    <Link
                        :href="item.href"
                        class="flex flex-1 flex-col items-center justify-center gap-1 px-2 py-3 transition-all duration-200 press-effect"
                        :class="
                            isActive(item.href)
                                ? 'text-sky-600 dark:text-sky-400'
                                : 'text-muted-foreground hover:text-foreground'
                        "
                        @click="handleNavClick"
                    >
                        <div
                            :class="[
                                'flex h-8 w-8 items-center justify-center rounded-xl transition-all duration-300',
                                isActive(item.href)
                                    ? 'bg-sky-100 dark:bg-sky-500/20'
                                    : '',
                            ]"
                        >
                            <component
                                :is="item.icon"
                                class="h-5 w-5 transition-transform duration-200"
                                :class="isActive(item.href) ? 'scale-110' : ''"
                            />
                        </div>
                        <span
                            class="text-[10px] font-medium transition-all duration-200"
                            :class="isActive(item.href) ? 'font-semibold' : ''"
                        >
                            {{ item.title }}
                        </span>
                    </Link>
                </template>
            </div>
        </div>
    </nav>
</template>
