<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AttachmentPanel from '@/components/AttachmentPanel.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{ sale: any; authorizations: any[]; attachments: any; canConfirm: boolean; canReturn: boolean; canRequestAuthorization: boolean; canViewMargin: boolean }>();
const usable = (permission: string) => props.authorizations.find((item) => item.permission === permission && item.usable)?.id ?? '';
const confirm = useForm({ price_authorization_id: usable('prices.authorize-below-minimum'), inventory_authorization_id: usable('inventory.authorize-negative'), credit_authorization_id: usable('sales.authorize-credit') });
const authorization = useForm({ type: 'price', reason: '' });
const belowMinimum = computed(() => props.sale.lines.some((line: any) => line.minimum_price !== null && Number(line.applied_unit_price) < Number(line.minimum_price)));
const money = (value: number) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(value || 0);
</script>

<template>
    <Head :title="sale.document_number" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header class="flex justify-between gap-3">
            <div><p class="text-sm text-muted-foreground">{{ sale.customer }} <span v-if="sale.order">· pedido {{ sale.order }}</span></p><h1 class="text-2xl font-semibold">{{ sale.document_number }}</h1><p class="text-sm text-muted-foreground">{{ sale.status_label }} · {{ sale.sold_at }}</p></div>
            <div class="flex gap-2"><Button v-if="canReturn && sale.status !== 'draft' && sale.status !== 'reversed'" as-child><Link :href="`/sales/${sale.id}/returns/create`">Registrar devolución</Link></Button><Button variant="outline" as-child><Link href="/sales">Volver</Link></Button></div>
        </header>

        <section class="grid gap-3 sm:grid-cols-4">
            <div class="rounded-xl border bg-card p-4"><small>Total original</small><p class="text-xl font-semibold">{{ money(sale.total) }}</p></div>
            <div class="rounded-xl border bg-card p-4"><small>Devuelto</small><p class="text-xl font-semibold">{{ money(sale.returned_amount) }}</p></div>
            <div class="rounded-xl border bg-card p-4"><small>Pagado + anticipo</small><p class="text-xl font-semibold">{{ money(sale.paid_amount) }}</p></div>
            <div class="rounded-xl border bg-card p-4"><small>Saldo</small><p class="text-xl font-semibold">{{ money(sale.balance_amount) }}</p></div>
        </section>

        <section class="overflow-x-auto rounded-xl border bg-card"><table class="w-full text-sm"><thead class="border-b bg-muted/40 text-left"><tr><th class="px-4 py-3">Producto</th><th class="px-4 py-3">Cantidad</th><th class="px-4 py-3">Precio lista</th><th class="px-4 py-3">Aplicado</th><th class="px-4 py-3">Total</th><th v-if="canViewMargin" class="px-4 py-3">Costo</th></tr></thead><tbody class="divide-y"><tr v-for="line in sale.lines" :key="line.id"><td class="px-4 py-3">{{ line.product }}<small v-if="line.price_reason" class="block text-muted-foreground">{{ line.price_reason }}</small></td><td class="px-4 py-3">{{ line.quantity }}</td><td class="px-4 py-3">{{ money(line.list_unit_price) }}</td><td class="px-4 py-3" :class="line.minimum_price && line.applied_unit_price < line.minimum_price ? 'text-destructive' : ''">{{ money(line.applied_unit_price) }}</td><td class="px-4 py-3">{{ money(line.line_total) }}</td><td v-if="canViewMargin" class="px-4 py-3">{{ money(line.total_cost) }}</td></tr></tbody></table></section>

        <section v-if="sale.status === 'draft'" class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border bg-card p-4"><h2 class="font-semibold">Confirmar operación</h2><p class="mt-1 text-sm text-muted-foreground">La confirmación afecta inventario, pagos, ingreso, costo y cartera en una transacción.</p><form v-if="canConfirm" class="mt-4" @submit.prevent="confirm.post(`/sales/${sale.id}/confirm`)"><Button type="submit" :disabled="confirm.processing || (belowMinimum && !confirm.price_authorization_id)">Confirmar venta</Button></form></div>
            <div v-if="canRequestAuthorization" class="rounded-xl border border-amber-300 p-4"><h2 class="font-semibold">Solicitar autorización</h2><form class="mt-3 grid gap-3" @submit.prevent="authorization.post(`/sales/${sale.id}/authorizations`, { onSuccess: () => authorization.reset('reason') })"><select v-model="authorization.type" class="h-9 rounded-md border bg-transparent px-3 text-sm"><option value="price">Precio bajo mínimo</option><option value="inventory">Inventario insuficiente</option><option value="credit">Límite de crédito</option></select><Input v-model="authorization.reason" required placeholder="Motivo" /><Button type="submit" variant="outline">Solicitar</Button></form></div>
        </section>

        <section v-if="sale.returns.length" class="rounded-xl border bg-card p-4"><h2 class="font-semibold">Devoluciones</h2><div class="mt-3 divide-y"><div v-for="item in sale.returns" :key="item.id" class="flex justify-between py-3 text-sm"><div><p class="font-medium">{{ item.document_number }} · {{ item.returned_at }}</p><p class="text-muted-foreground">{{ item.reason }}</p></div><span>{{ money(item.total_refund) }}</span></div></div></section>
        <section v-if="canViewMargin && sale.status !== 'draft'" class="rounded-xl border bg-card p-4"><h2 class="font-semibold">Rentabilidad</h2><div class="mt-3 grid grid-cols-2 gap-3"><div><small>Costo de venta</small><p class="font-semibold">{{ money(sale.cost_of_goods_sold) }}</p></div><div><small>Utilidad bruta original</small><p class="font-semibold">{{ money(sale.gross_profit) }}</p></div></div></section>

        <AttachmentPanel :attachments="attachments" />
    </main>
</template>
