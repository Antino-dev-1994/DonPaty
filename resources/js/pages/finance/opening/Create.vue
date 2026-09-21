<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{ defaultDate: string; defaultCashAmount: number; defaultNequiAmount: number }>();
const form = useForm({
    opened_on: props.defaultDate,
    cash_amount: props.defaultCashAmount,
    nequi_amount: props.defaultNequiAmount,
    notes: 'Saldos reales al inicio de la operación.',
});
const total = () => Number(form.cash_amount || 0) + Number(form.nequi_amount || 0);
const money = (value: number) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(value || 0);
</script>

<template>
    <Head title="Apertura inicial del negocio" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header><h1 class="text-2xl font-semibold">Apertura inicial del negocio</h1><p class="text-sm text-muted-foreground">Registra capital disponible, no ventas ni ingresos del negocio.</p></header>
        <form class="grid max-w-xl gap-4 rounded-xl border bg-card p-5" @submit.prevent="form.post('/finance/opening')">
            <label class="grid gap-1 text-sm">Fecha de apertura<Input v-model="form.opened_on" type="date" required /></label>
            <label class="grid gap-1 text-sm">Efectivo en caja principal<Input v-model.number="form.cash_amount" type="number" min="0" required /></label>
            <label class="grid gap-1 text-sm">Saldo disponible en Nequi<Input v-model.number="form.nequi_amount" type="number" min="0" required /></label>
            <div class="rounded-lg bg-muted p-4 text-sm"><span>Capital inicial total</span><strong class="block text-xl">{{ money(total()) }}</strong></div>
            <label class="grid gap-1 text-sm">Observación<Input v-model="form.notes" maxlength="500" /></label>
            <p v-if="Object.keys(form.errors).length" class="text-sm text-destructive">{{ Object.values(form.errors)[0] }}</p>
            <div class="flex justify-end gap-2"><Button variant="outline" as-child><Link href="/finance">Cancelar</Link></Button><Button type="submit" :disabled="form.processing || total() <= 0">Registrar apertura</Button></div>
        </form>
    </main>
</template>
