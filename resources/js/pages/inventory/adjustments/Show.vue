<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
type Line = { id: string; presentation: string; unit: string; expected_quantity: string; counted_quantity: string; difference_quantity: string; unit_cost: number | null; allow_negative_stock: boolean };
type Adjustment = { id: string; document_number: string; adjustment_type: string; reason: string; status: string; status_label: string; effective_at: string; creator: string; lines: Line[] };
type Authorization = { id: string; status: string; status_label: string; requester: string; approver: string | null };
const props = defineProps<{ adjustment: Adjustment; authorization: Authorization | null; canAdjust: boolean }>();
const authForm = useForm({ reason: props.adjustment.reason });
const hasNegative = computed(() => props.adjustment.lines.some((line) => Number(line.counted_quantity) < 0));
const hasForbiddenNegative = computed(() => props.adjustment.lines.some((line) => Number(line.counted_quantity) < 0 && !line.allow_negative_stock));
const requestAuthorization = () => authForm.post(`/inventory/adjustments/${props.adjustment.id}/authorization`, { preserveScroll: true });
const confirmAdjustment = () => router.post(`/inventory/adjustments/${props.adjustment.id}/confirm`, { authorization_request_id: props.authorization?.status === 'approved' ? props.authorization.id : null }, { preserveScroll: true });
const discard = () => { if (window.confirm('¿Descartar este borrador? No ha movido inventario.')) router.delete(`/inventory/adjustments/${props.adjustment.id}`); };
const lineTotal = (line: Line) => Math.round(Math.abs(Number(line.difference_quantity)) * Number(line.unit_cost || 0));
const money = (value: number) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(value || 0);
</script>

<template>
    <Head :title="adjustment.document_number" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header><div class="flex flex-wrap items-center gap-2"><h1 class="text-2xl font-semibold">{{ adjustment.document_number }}</h1><span class="rounded-full bg-muted px-2 py-1 text-xs">{{ adjustment.status_label }}</span></div><p class="text-sm text-muted-foreground">{{ adjustment.creator }} · {{ adjustment.effective_at }}</p></header>
        <section class="rounded-xl border bg-card p-5"><h2 class="font-semibold">{{ adjustment.adjustment_type === 'initial' ? 'Inventario inicial' : 'Ajuste manual' }}</h2><p class="mt-2 text-sm">{{ adjustment.reason }}</p></section>
        <section class="overflow-x-auto rounded-xl border bg-card"><table class="w-full text-sm"><thead class="border-b bg-muted/40 text-left"><tr><th class="px-4 py-3">Presentación</th><th class="px-4 py-3 text-right">Esperado</th><th class="px-4 py-3 text-right">Contado</th><th class="px-4 py-3 text-right">Diferencia</th><th class="px-4 py-3 text-right">Costo unitario</th><th class="px-4 py-3 text-right">Total entrada</th></tr></thead><tbody class="divide-y"><tr v-for="line in adjustment.lines" :key="line.id"><td class="px-4 py-3">{{ line.presentation }}</td><td class="px-4 py-3 text-right">{{ line.expected_quantity }} {{ line.unit }}</td><td class="px-4 py-3 text-right">{{ line.counted_quantity }}</td><td class="px-4 py-3 text-right" :class="Number(line.difference_quantity) < 0 ? 'text-destructive' : 'text-emerald-700'">{{ line.difference_quantity }}</td><td class="px-4 py-3 text-right">{{ line.unit_cost === null ? '—' : money(line.unit_cost) }}</td><td class="px-4 py-3 text-right font-medium">{{ money(lineTotal(line)) }}</td></tr></tbody></table></section>
        <section v-if="adjustment.status === 'draft' && hasNegative" class="max-w-2xl rounded-xl border border-amber-300 bg-amber-50 p-5 dark:bg-amber-950/20"><h2 class="font-semibold">Inventario negativo</h2><p v-if="hasForbiddenNegative" class="mt-2 text-sm text-destructive">Una presentación no permite inventario negativo. Debes crear un nuevo ajuste después de habilitarla o corregir el conteo.</p><template v-else-if="authorization"><p class="mt-2 text-sm">Solicitud {{ authorization.status_label }} por {{ authorization.requester }}<span v-if="authorization.approver"> · {{ authorization.approver }}</span>.</p></template><form v-else class="mt-3 flex gap-2" @submit.prevent="requestAuthorization"><Input v-model="authForm.reason" required /><Button type="submit" variant="outline">Solicitar autorización</Button></form></section>
        <div v-if="adjustment.status === 'draft' && canAdjust" class="flex flex-wrap justify-end gap-2"><Button variant="destructive" @click="discard">Descartar borrador</Button><Button variant="outline" as-child><Link :href="`/inventory/adjustments/${adjustment.id}/edit`">Corregir borrador</Link></Button><Button :disabled="hasForbiddenNegative || (hasNegative && authorization?.status !== 'approved')" @click="confirmAdjustment">Confirmar ajuste</Button></div>
    </main>
</template>
