<template>
    <div class="bg-surface rounded-xl border border-base overflow-hidden">
        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-base">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-subtle pointer-events-none"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    v-model="search"
                    type="search"
                    :placeholder="searchPlaceholder"
                    class="pl-9 pr-4 py-2 text-sm bg-surface-2 border border-base rounded-lg text-default placeholder:text-subtle focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-400 w-64"
                />
            </div>
            <slot name="toolbar" />
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-surface-2 border-b border-base">
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="['px-5 py-3 text-left text-xs font-semibold text-muted tracking-wide uppercase select-none', col.sortable && 'cursor-pointer hover:text-default']"
                            @click="col.sortable && toggleSort(col.key)"
                        >
                            <div class="flex items-center gap-1.5">
                                {{ col.label }}
                                <span v-if="col.sortable" class="text-subtle">
                                    <svg v-if="sortKey === col.key && sortDir === 'asc'" class="w-3 h-3 text-primary-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                    </svg>
                                    <svg v-else-if="sortKey === col.key && sortDir === 'desc'" class="w-3 h-3 text-primary-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                    <svg v-else class="w-3 h-3 opacity-30" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                    </svg>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border)]">
                    <tr v-if="loading">
                        <td :colspan="columns.length" class="py-16 text-center">
                            <LoadingSpinner center />
                        </td>
                    </tr>
                    <tr v-else-if="!paginatedRows.length">
                        <td :colspan="columns.length">
                            <EmptyState :title="emptyTitle" :description="emptyDescription" />
                        </td>
                    </tr>
                    <tr
                        v-else
                        v-for="(row, i) in paginatedRows"
                        :key="row.id ?? i"
                        class="hover:bg-surface-2 transition-colors group"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            class="px-5 py-3.5 text-default"
                        >
                            <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                                {{ row[col.key] ?? '—' }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-between px-5 py-3.5 border-t border-base text-sm">
            <span class="text-muted">
                Showing {{ pageStart }}–{{ pageEnd }} of {{ filteredRows.length }}
            </span>
            <div class="flex items-center gap-1">
                <button
                    v-for="p in pageRange"
                    :key="p"
                    @click="p !== '…' && (currentPage = p)"
                    :class="['min-w-[2rem] h-8 px-2 rounded-lg text-sm font-medium transition-colors',
                             p === currentPage
                                 ? 'bg-primary-600 text-white'
                                 : p === '…' ? 'text-subtle cursor-default'
                                             : 'text-muted hover:bg-surface-3 hover:text-default']"
                >{{ p }}</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import LoadingSpinner from './LoadingSpinner.vue';
import EmptyState from './EmptyState.vue';

const props = defineProps({
    columns:          { type: Array, required: true },
    rows:             { type: Array, default: () => [] },
    loading:          { type: Boolean, default: false },
    perPage:          { type: Number, default: 10 },
    searchPlaceholder:{ type: String, default: 'Search…' },
    emptyTitle:       { type: String, default: 'No results' },
    emptyDescription: { type: String, default: 'Try adjusting your search.' },
    searchKeys:       { type: Array, default: null },
});

const search      = ref('');
const sortKey     = ref('');
const sortDir     = ref('asc');
const currentPage = ref(1);

function toggleSort(key) {
    if (sortKey.value === key) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    else { sortKey.value = key; sortDir.value = 'asc'; }
    currentPage.value = 1;
}

const filteredRows = computed(() => {
    let rows = [...props.rows];
    const q = search.value.trim().toLowerCase();
    if (q) {
        const keys = props.searchKeys ?? props.columns.map(c => c.key);
        rows = rows.filter(r => keys.some(k => String(r[k] ?? '').toLowerCase().includes(q)));
    }
    if (sortKey.value) {
        rows.sort((a, b) => {
            const av = a[sortKey.value], bv = b[sortKey.value];
            const cmp = av < bv ? -1 : av > bv ? 1 : 0;
            return sortDir.value === 'asc' ? cmp : -cmp;
        });
    }
    return rows;
});

const totalPages  = computed(() => Math.max(1, Math.ceil(filteredRows.value.length / props.perPage)));
const pageStart   = computed(() => (currentPage.value - 1) * props.perPage + 1);
const pageEnd     = computed(() => Math.min(currentPage.value * props.perPage, filteredRows.value.length));
const paginatedRows = computed(() => filteredRows.value.slice(pageStart.value - 1, pageEnd.value));

const pageRange = computed(() => {
    const total = totalPages.value, cur = currentPage.value;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    if (cur <= 4)   return [1, 2, 3, 4, 5, '…', total];
    if (cur >= total - 3) return [1, '…', total-4, total-3, total-2, total-1, total];
    return [1, '…', cur-1, cur, cur+1, '…', total];
});
</script>
