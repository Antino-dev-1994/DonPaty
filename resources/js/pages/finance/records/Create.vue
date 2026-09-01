<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Category = { id:string; name:string; type:'income'|'expense'; cost_type:string|null };
type PoolEntry = { id:string; cost_type:string; amount:number; effective_at:string };
type Period = { id:string; label:string; pool_entries:PoolEntry[] };
const props = defineProps<{ categories:Category[]; people:{id:string;name:string}[]; accounts:{id:string;code:string;name:string}[]; costPeriods:Period[]; now:string }>();
const type = ref<'income'|'expense'>('expense');
const categories = computed(() => props.categories.filter((category) => category.type === type.value));
const selectedCategory = computed(() => props.categories.find((category) => category.id === form.category_id));
const selectedPeriod = computed(() => props.costPeriods.find((period) => period.id === form.cost_period_id));
const poolEntries = computed(() => selectedPeriod.value?.pool_entries.filter((entry) => entry.cost_type === selectedCategory.value?.cost_type) ?? []);
const form = useForm({ category_id:'', person_id:'', cost_period_id:'', cost_pool_entry_id:'', effective_at:props.now, due_at:'', description:'', total_amount:0, initial_payment_amount:0, financial_account_id:props.accounts[0]?.id ?? '', payment_reference:'' });
watch(type, () => { form.category_id = ''; form.cost_period_id = ''; form.cost_pool_entry_id = ''; });
watch(() => form.category_id, () => { form.cost_pool_entry_id = ''; });
const submit = () => form.transform((data) => ({ ...data, person_id:data.person_id || null, cost_period_id:data.cost_period_id || null, cost_pool_entry_id:data.cost_pool_entry_id || null, due_at:data.due_at || null, financial_account_id:Number(data.initial_payment_amount) > 0 ? data.financial_account_id : null })).post(type.value === 'income' ? '/finance/incomes' : '/finance/expenses');
const money = (value:number) => new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', maximumFractionDigits:0 }).format(value || 0);
</script>

<template>
    <Head title="Registrar ingreso o gasto" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header><h1 class="text-2xl font-semibold">Registrar ingreso o gasto</h1><p class="text-sm text-muted-foreground">El resultado se reconoce ahora; el flujo de caja solo por el valor realmente pagado.</p></header>
        <div class="flex gap-2"><Button type="button" :variant="type === 'expense' ? 'default' : 'outline'" @click="type = 'expense'">Gasto</Button><Button type="button" :variant="type === 'income' ? 'default' : 'outline'" @click="type = 'income'">Ingreso</Button></div>
        <form class="grid max-w-3xl gap-4 rounded-xl border bg-card p-5 md:grid-cols-2" @submit.prevent="submit">
            <label class="grid gap-1 text-sm">Categoría<select v-model="form.category_id" required class="h-9 rounded-md border bg-transparent px-3"><option value="" disabled>Selecciona</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></label>
            <label class="grid gap-1 text-sm">Tercero<select v-model="form.person_id" class="h-9 rounded-md border bg-transparent px-3"><option value="">Sin tercero</option><option v-for="person in people" :key="person.id" :value="person.id">{{ person.name }}</option></select><small class="text-muted-foreground">Obligatorio si queda saldo pendiente.</small></label>
            <label class="grid gap-1 text-sm">Fecha efectiva<Input v-model="form.effective_at" type="datetime-local" required /></label>
            <label class="grid gap-1 text-sm">Vencimiento<Input v-model="form.due_at" type="date" /></label>
            <label class="grid gap-1 text-sm md:col-span-2">Descripción<Input v-model="form.description" required /></label>
            <label class="grid gap-1 text-sm">Valor total<Input v-model.number="form.total_amount" type="number" min="1" required /></label>
            <label class="grid gap-1 text-sm">Pago inicial<Input v-model.number="form.initial_payment_amount" type="number" min="0" :max="form.total_amount" required /><small class="text-muted-foreground">Saldo: {{ money(Math.max(0, form.total_amount - form.initial_payment_amount)) }}</small></label>
            <template v-if="form.initial_payment_amount > 0"><label class="grid gap-1 text-sm">Cuenta<select v-model="form.financial_account_id" required class="h-9 rounded-md border bg-transparent px-3"><option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.code }} · {{ account.name }}</option></select></label><label class="grid gap-1 text-sm">Referencia<Input v-model="form.payment_reference" placeholder="Opcional" /></label></template>
            <template v-if="type === 'expense' && selectedCategory?.cost_type"><label class="grid gap-1 text-sm">Periodo de costos<select v-model="form.cost_period_id" required class="h-9 rounded-md border bg-transparent px-3"><option value="" disabled>Selecciona</option><option v-for="period in costPeriods" :key="period.id" :value="period.id">{{ period.label }}</option></select></label><label v-if="selectedCategory.cost_type !== 'labor'" class="grid gap-1 text-sm">Factura ya registrada<select v-model="form.cost_pool_entry_id" required class="h-9 rounded-md border bg-transparent px-3"><option value="" disabled>Selecciona</option><option v-for="entry in poolEntries" :key="entry.id" :value="entry.id">{{ entry.effective_at }} · {{ money(entry.amount) }}</option></select></label></template>
            <p v-if="Object.keys(form.errors).length" class="text-sm text-destructive md:col-span-2">{{ Object.values(form.errors)[0] }}</p>
            <div class="flex justify-end gap-2 md:col-span-2"><Button variant="outline" as-child><Link href="/finance">Cancelar</Link></Button><Button type="submit" :disabled="form.processing">Registrar {{ type === 'income' ? 'ingreso' : 'gasto' }}</Button></div>
        </form>
    </main>
</template>
