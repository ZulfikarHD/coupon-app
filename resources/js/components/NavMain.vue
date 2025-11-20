<script setup lang="ts">
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

export interface NavItemWithSubmenu extends NavItem {
    items?: NavItem[];
}

defineProps<{
    items: NavItemWithSubmenu[];
}>();

const page = usePage();

const isItemActive = (item: NavItemWithSubmenu) => {
    if (urlIsActive(item.href, page.url)) {
        return true;
    }
    if (item.items) {
        return item.items.some((subItem) =>
            urlIsActive(subItem.href, page.url),
        );
    }
    return false;
};

const isSubItemActive = (href: string) => {
    return urlIsActive(href, page.url);
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <Collapsible
                    v-if="item.items && item.items.length > 0"
                    :default-open="isItemActive(item)"
                    class="group/collapsible"
                >
                    <CollapsibleTrigger as-child>
                        <SidebarMenuButton
                            :is-active="isItemActive(item)"
                            :tooltip="item.title"
                        >
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                            <ChevronRight
                                class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                            />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenuSub>
                            <SidebarMenuSubItem
                                v-for="subItem in item.items"
                                :key="subItem.title"
                            >
                                <SidebarMenuSubButton
                                    :as-child="true"
                                    :is-active="isSubItemActive(subItem.href)"
                                >
                                    <Link :href="subItem.href">
                                        <component
                                            v-if="subItem.icon"
                                            :is="subItem.icon"
                                        />
                                        <span>{{ subItem.title }}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </Collapsible>
                <SidebarMenuButton
                    v-else
                    as-child
                    :is-active="urlIsActive(item.href, page.url)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
