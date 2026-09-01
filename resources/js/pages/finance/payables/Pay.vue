<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Payable = { id:string; document_number:string; person:string; original_amount:number; paid_amount:number; balance_amount:number; back_url:string };
type Account = { id:string; code:string; name:string };
const props = defineProps<{ payable:Payable; accounts:Account[]; now:string }>();
const form = useForm({ amount:props.payable.balance_amount, financial_account_id:props.accounts[0]?.id ?? '', paid_at:props.now, reference:'' });
const money = (value:number) => new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', maximumFractionDigits:0 }).format(value);
</script>

<template>
    <Head title="Registrar pago" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header><p class="text-sm text-muted-foreground">{{ payable.document_number }} · {{ payable.person }}</p><h1 class="text-2xl font-semibold">Registrar pago de obligación</h1><p class="text-sm text-muted-foreground">Saldo actual: {{ money(payable.balance_amount) }}</p></header>
        <form class="grid max-w-xl gap-5 rounded-xl border bg-card p-5" @submit.prevent="form.post(`/finance/payables/${payable.id}/payments`)">
            <div class="grid gap-2"><Label>Monto en COP</Label><Input v-model.number="form.amount" type="number" min="1" :max="payable.balance_amount" required /></div>
            <div class="grid gap-2"><Label>Cuenta de salida</Label><select v-model="form.financial_account_id" required class="h-9 rounded-md border bg-transparent px-3 text-sm"><option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.code }} · {{ account.name }}</option></select></div>
            <div class="grid gap-2"><Label>Fecha y hora</Label><Input v-model="form.paid_at" type="datetime-local" required /></div>
            <div class="grid gap-2"><Label>Referencia o comprobante</Label><Input v-model="form.reference" placeholder="Transferencia, recibo, nota..." /></div>
            <p v-if="Object.keys(form.errors).length" class="text-sm text-destructive">{{ Object.values(form.errors)[0] }}</p>
            <div class="flex justify-end gap-2"><Button variant="outline" as-child><Link :href="payable.back_url">Cancelar</Link></Button><Button type="submit" :disabled="form.processing || accounts.length === 0">Confirmar pago</Button></div>
        </form>
    </main>
</template>
