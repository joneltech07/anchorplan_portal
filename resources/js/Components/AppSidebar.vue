<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Clock,
    CreditCard,
    ListTodo,
    Calendar,
    Package,
    UserMinus
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
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
import type { NavItem } from '@/types';

declare const route: any;

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Attendance',
        href: route('attendance.index'),
        icon: Clock,
    },
    {
        title: 'Payroll',
        href: route('payroll.index'),
        icon: CreditCard,
    },
    {
        title: 'Tasks',
        href: route('tasks.index'),
        icon: ListTodo,
    },
    {
        title: 'Calendar',
        href: route('calendar.index'),
        icon: Calendar,
    },
    {
        title: 'Inventory',
        href: route('inventory.index'),
        icon: Package,
    },
    {
        title: 'Leave Requests',
        href: route('leave.index'),
        icon: UserMinus,
    },
];

const footerNavItems: NavItem[] = [];
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
