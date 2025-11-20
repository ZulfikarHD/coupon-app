<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ScanLine } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Scan Kupon',
        href: '/scan',
    },
];
</script>

<template>
    <Head title="Scan Kupon" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 md:p-6">
            <Card class="border-2">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <ScanLine class="h-6 w-6 text-primary" />
                        <CardTitle>Scan Kupon</CardTitle>
                    </div>
                    <CardDescription>
                        Fitur scanner akan diimplementasikan di User Story 2.3
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <p class="text-muted-foreground">
                        Halaman scanner QR code akan tersedia segera.
                    </p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
