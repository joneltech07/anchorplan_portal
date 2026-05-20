<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Inventory Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <section class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800 lg:col-span-1">
                        <h3 class="text-sm font-semibold text-gray-500">Products</h3>
                        <select v-model="selectedProductId" @change="changeProduct" class="mt-4 w-full rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                            <option v-for="product in props.products" :key="product.id" :value="product.id">{{ product.name }} ({{ product.sku }})</option>
                        </select>
                        <div class="mt-6 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ props.selectedProduct?.name || 'Select a product' }}</p>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Current stock: {{ props.selectedProduct?.current_stock ?? '—' }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Threshold: {{ props.selectedProduct?.min_stock_threshold ?? '—' }}</p>
                            <p v-if="lowStock" class="mt-3 rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700 dark:bg-amber-900 dark:text-amber-200">Low stock alert</p>
                        </div>
                    </section>

                    <section class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800 lg:col-span-2">
                        <h3 class="text-sm font-semibold text-gray-500">Record Movement</h3>
                        <form @submit.prevent="form.post(route('inventory.movements.store'))" class="mt-5 grid gap-4 md:grid-cols-2">
                            <select v-model="form.product_id" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                <option value="">Select product</option>
                                <option v-for="product in props.products" :key="product.id" :value="product.id">{{ product.name }}</option>
                            </select>
                            <select v-model="form.movement_type" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
                                <option value="in">Stock In</option>
                                <option value="out">Stock Out</option>
                            </select>
                            <input v-model="form.quantity" type="number" min="1" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" placeholder="Quantity" />
                            <input v-model="form.reason" type="text" class="rounded-md border-gray-300 p-3 shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" placeholder="Reason" />
                            <button type="submit" class="col-span-full rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Save Movement</button>
                        </form>
                    </section>
                </div>

                <section class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="text-sm font-semibold text-gray-500">Movement History</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Quantity</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Before</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">After</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="movement in props.movements" :key="movement.id">
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ movement.movement_type }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ movement.quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ movement.stock_before }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ movement.stock_after }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ movement.reason }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
