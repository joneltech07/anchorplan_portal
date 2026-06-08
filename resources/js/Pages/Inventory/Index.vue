<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const props = defineProps({
    products: Array,
    selectedProduct: Object,
    movements: Array,
});

const selectedProductId = ref(props.selectedProduct ? props.selectedProduct.id : (props.products[0]?.id || ''));

const form = useForm({
    product_id: selectedProductId.value,
    movement_type: 'in',
    quantity: 1,
    reason: '',
});

const lowStock = computed(() => props.selectedProduct?.is_low_stock);

const changeProduct = () => {
    window.location.href = route('inventory.index', { product_id: selectedProductId.value });
};
</script>

<template>
    <Head title="Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Inventory Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900 lg:col-span-1">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-muted-foreground">Products</h3>
                        <select v-model="selectedProductId" @change="changeProduct" class="mt-4 w-full rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground">
                            <option v-for="product in props.products" :key="product.id" :value="product.id">{{ product.name }} ({{ product.sku }})</option>
                        </select>
                        <div class="mt-6 rounded-3xl border border-border bg-muted p-4 dark:border-sidebar-border dark:bg-zinc-950">
                            <p class="text-sm font-semibold text-foreground">{{ props.selectedProduct?.name || 'Select a product' }}</p>
                            <p class="mt-2 text-sm text-muted-foreground">Current stock: {{ props.selectedProduct?.current_stock ?? '—' }}</p>
                            <p class="mt-1 text-sm text-muted-foreground">Threshold: {{ props.selectedProduct?.min_stock_threshold ?? '—' }}</p>
                            <p v-if="lowStock" class="mt-3 inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700 dark:bg-amber-950 dark:text-amber-200">Low stock alert</p>
                        </div>
                    </section>

                    <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900 lg:col-span-2">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-muted-foreground">Record Movement</h3>
                        <form @submit.prevent="form.post(route('inventory.movements.store'))" class="mt-5 grid gap-4 md:grid-cols-2">
                            <select v-model="form.product_id" class="rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground">
                                <option value="">Select product</option>
                                <option v-for="product in props.products" :key="product.id" :value="product.id">{{ product.name }}</option>
                            </select>
                            <select v-model="form.movement_type" class="rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground">
                                <option value="in">Stock In</option>
                                <option value="out">Stock Out</option>
                            </select>
                            <input v-model="form.quantity" type="number" min="1" class="rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground" placeholder="Quantity" />
                            <input v-model="form.reason" type="text" class="rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-foreground shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-sidebar-border dark:bg-zinc-950 dark:text-foreground" placeholder="Reason" />
                            <button type="submit" class="col-span-full rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">Save Movement</button>
                        </form>
                    </section>
                </div>

                <section class="rounded-[28px] border border-border bg-card p-6 shadow-sm dark:border-sidebar-border dark:bg-zinc-900">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-muted-foreground">Movement History</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-border text-sm dark:divide-sidebar-border">
                            <thead class="bg-muted text-left text-xs uppercase tracking-[0.2em] text-muted-foreground dark:bg-zinc-950">
                                <tr>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Quantity</th>
                                    <th class="px-4 py-3">Before</th>
                                    <th class="px-4 py-3">After</th>
                                    <th class="px-4 py-3">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border dark:divide-sidebar-border">
                                <tr v-for="movement in props.movements" :key="movement.id" class="hover:bg-muted/40 dark:hover:bg-zinc-950">
                                    <td class="px-4 py-3 text-sm text-foreground">{{ movement.movement_type }}</td>
                                    <td class="px-4 py-3 text-sm text-foreground">{{ movement.quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-foreground">{{ movement.stock_before }}</td>
                                    <td class="px-4 py-3 text-sm text-foreground">{{ movement.stock_after }}</td>
                                    <td class="px-4 py-3 text-sm text-foreground">{{ movement.reason }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
