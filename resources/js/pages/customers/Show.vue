<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const props = defineProps<{ customer: any; canManage: boolean; canReceivePayments: boolean }>();
const money = (value: number) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(value || 0);
const statusClass = (receivable: any) => receivable.is_overdue ? 'text-destructive' : receivable.status === 'paid' ? 'text-emerald-700' : receivable.status === 'partial' ? 'text-amber-700' : '';
</script>

<template>
    <Head :title="customer.name" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div><p class="text-sm text-muted-foreground">Cliente</p><h1 class="text-2xl font-semibold">{{ customer.name }}</h1><p class="text-sm text-muted-foreground">{{ customer.document_number || 'Sin documento' }} · {{ customer.phone || customer.email || 'Sin contacto' }}</p></div>
            <div class="flex gap-2"><Button v-if="canManage" as-child><Link :href="`/customers/${customer.id}/edit`">Editar</Link></Button><Button variant="outline" as-child><Link href="/customers">Volver</Link></Button></div>
        </header>

        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border bg-card p-4"><p class="text-xs text-muted-foreground">Límite de crédito</p><strong class="text-xl">{{ money(customer.credit_limit) }}</strong></div>
            <div class="rounded-xl border bg-card p-4"><p class="text-xs text-muted-foreground">Cartera pendiente</p><strong class="text-xl" :class="customer.open_balance ? 'text-amber-700' : 'text-emerald-700'">{{ money(customer.open_balance) }}</strong></div>
            <div class="rounded-xl border bg-card p-4"><p class="text-xs text-muted-foreground">Crédito disponible</p><strong class="text-xl">{{ money(customer.available_credit) }}</strong></div>
            <div class="rounded-xl border bg-card p-4"><p class="text-xs text-muted-foreground">Condición</p><strong>{{ customer.default_payment_term_days }} días</strong><p class="text-xs text-muted-foreground">{{ customer.price_list || 'Lista sin definir' }}</p></div>
        </section>

        <section class="overflow-x-auto rounded-xl border bg-card"><div class="border-b p-4"><h2 class="font-semibold">Créditos y estado</h2><p class="text-sm text-muted-foreground">Incluye cartera pendiente, abonos parciales y créditos ya pagados.</p></div><table class="w-full text-sm"><thead class="border-b bg-muted/40 text-left"><tr><th class="px-4 py-3">Documento</th><th class="px-4 py-3">Emisión</th><th class="px-4 py-3">Vencimiento</th><th class="px-4 py-3">Original</th><th class="px-4 py-3">Abonado</th><th class="px-4 py-3">Saldo</th><th class="px-4 py-3">Estado</th><th></th></tr></thead><tbody class="divide-y"><tr v-for="receivable in customer.receivables" :key="receivable.id"><td class="px-4 py-3 font-medium">{{ receivable.document_number }}</td><td class="px-4 py-3">{{ receivable.issued_at }}</td><td class="px-4 py-3" :class="receivable.is_overdue ? 'text-destructive font-medium' : ''">{{ receivable.due_at || 'Sin fecha' }}<small v-if="receivable.is_overdue" class="block">Vencido</small></td><td class="px-4 py-3">{{ money(receivable.original_amount) }}</td><td class="px-4 py-3">{{ money(receivable.paid_amount) }}</td><td class="px-4 py-3 font-medium">{{ money(receivable.balance_amount) }}</td><td class="px-4 py-3" :class="statusClass(receivable)">{{ receivable.status_label }}</td><td class="px-4 py-3 text-right"><Link v-if="canReceivePayments && receivable.balance_amount > 0" :href="`/receivables/${receivable.id}/pay`" class="font-medium text-amber-700 hover:underline">Registrar abono</Link></td></tr></tbody></table><p v-if="!customer.receivables.length" class="p-10 text-center text-sm text-muted-foreground">Este cliente aún no tiene créditos registrados.</p></section>
    </main>
</template>
