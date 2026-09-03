<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AttachmentPanel from '@/components/AttachmentPanel.vue';
import { Button } from '@/components/ui/button';

type Line = { id: string; ordered_quantity: string; received_quantity: string; returned_quantity: string; unit_price: number; allocated_additional_cost: number; line_total: number; presentation: string; unit: string };
type Receipt = { id: string; document_number: string; received_at: string };
type Payment = { id: string; document_number: string; paid_at: string; amount: number; account: string; reference: string | null };
type PurchaseReturn = { id: string; document_number: string; returned_at: string; total_amount: number };
type Purchase = { id: string; document_number: string; supplier_document_number: string | null; issued_at: string; due_at: string | null; supplier: { name: string; document_number: string | null }; payment_condition: string; receipt_status: string; payment_status: string; subtotal: number; additional_costs: number; total: number; paid_amount: number; balance_amount: number; notes: string | null; lines: Line[]; receipts: Receipt[]; payable_id: string | null; payments: Payment[]; returns: PurchaseReturn[] };

defineProps<{ purchase: Purchase; canReceive: boolean; canPay: boolean; canReturn: boolean; attachments: any }>();
const money = (value: number) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(value);
</script>

<template>
    <Head :title="purchase.document_number" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <p class="text-sm text-muted-foreground">Compra</p>
                <h1 class="text-2xl font-semibold">{{ purchase.document_number }}</h1>
                <p class="text-sm text-muted-foreground">{{ purchase.supplier.name }} · {{ purchase.issued_at }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button variant="outline" as-child><Link href="/purchasing/purchases">Volver</Link></Button>
                <Button v-if="canReturn" variant="outline" as-child><Link :href="`/purchasing/purchases/${purchase.id}/returns/create`">Devolver</Link></Button>
                <Button v-if="canPay && purchase.payable_id" variant="outline" as-child><Link :href="`/finance/payables/${purchase.payable_id}/payments/create`">Registrar pago</Link></Button>
                <Button v-if="canReceive" as-child><Link :href="`/purchasing/purchases/${purchase.id}/receipts/create`">Registrar recepción</Link></Button>
            </div>
        </header>

        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border bg-card p-4"><p class="text-xs text-muted-foreground">Total</p><p class="text-xl font-semibold">{{ money(purchase.total) }}</p></div>
            <div class="rounded-xl border bg-card p-4"><p class="text-xs text-muted-foreground">Saldo por pagar</p><p class="text-xl font-semibold">{{ money(purchase.balance_amount) }}</p></div>
            <div class="rounded-xl border bg-card p-4"><p class="text-xs text-muted-foreground">Recepción</p><p class="font-medium">{{ purchase.receipt_status }}</p></div>
            <div class="rounded-xl border bg-card p-4"><p class="text-xs text-muted-foreground">Pago</p><p class="font-medium">{{ purchase.payment_status }}</p></div>
        </section>

        <section class="overflow-x-auto rounded-xl border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b bg-muted/40 text-left"><tr><th class="px-4 py-3">Artículo</th><th class="px-4 py-3">Comprado</th><th class="px-4 py-3">Recibido</th><th class="px-4 py-3">Devuelto</th><th class="px-4 py-3">Precio</th><th class="px-4 py-3">Costo adicional</th><th class="px-4 py-3">Subtotal</th></tr></thead>
                <tbody class="divide-y">
                    <tr v-for="line in purchase.lines" :key="line.id">
                        <td class="px-4 py-3 font-medium">{{ line.presentation }}</td><td class="px-4 py-3">{{ line.ordered_quantity }} {{ line.unit }}</td><td class="px-4 py-3">{{ line.received_quantity }} {{ line.unit }}</td><td class="px-4 py-3">{{ line.returned_quantity }} {{ line.unit }}</td><td class="px-4 py-3">{{ money(line.unit_price) }}</td><td class="px-4 py-3">{{ money(line.allocated_additional_cost) }}</td><td class="px-4 py-3">{{ money(line.line_total) }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <div class="grid gap-5 lg:grid-cols-3">
            <section class="rounded-xl border bg-card p-4"><h2 class="font-semibold">Recepciones</h2><div v-if="purchase.receipts.length" class="mt-3 divide-y"><div v-for="receipt in purchase.receipts" :key="receipt.id" class="flex justify-between py-2 text-sm"><span>{{ receipt.document_number }}</span><span class="text-muted-foreground">{{ receipt.received_at }}</span></div></div><p v-else class="mt-3 text-sm text-muted-foreground">Sin recepciones.</p></section>
            <section class="rounded-xl border bg-card p-4"><h2 class="font-semibold">Pagos</h2><div v-if="purchase.payments.length" class="mt-3 divide-y"><div v-for="payment in purchase.payments" :key="payment.id" class="py-2 text-sm"><div class="flex justify-between"><span>{{ payment.document_number }}</span><strong>{{ money(payment.amount) }}</strong></div><p class="text-xs text-muted-foreground">{{ payment.paid_at }} · {{ payment.account }}</p></div></div><p v-else class="mt-3 text-sm text-muted-foreground">Sin pagos.</p></section>
            <section class="rounded-xl border bg-card p-4"><h2 class="font-semibold">Devoluciones</h2><div v-if="purchase.returns.length" class="mt-3 divide-y"><div v-for="item in purchase.returns" :key="item.id" class="py-2 text-sm"><div class="flex justify-between"><span>{{ item.document_number }}</span><strong>{{ money(item.total_amount) }}</strong></div><p class="text-xs text-muted-foreground">{{ item.returned_at }}</p></div></div><p v-else class="mt-3 text-sm text-muted-foreground">Sin devoluciones.</p></section>
        </div>

        <AttachmentPanel :attachments="attachments" />
    </main>
</template>
