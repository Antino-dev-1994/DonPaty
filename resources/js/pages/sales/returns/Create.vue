<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

interface SaleLine {
    id: string;
    product: string;
    sold_quantity: string;
    returned_quantity: string;
    available_quantity: string;
    line_total: number;
}

const props = defineProps<{
    sale: { id: string; document_number: string; customer: string; lines: SaleLine[] };
    accounts: { id: string; name: string; code: string }[];
    now: string;
}>();

const form = useForm({
    returned_at: props.now,
    reason: '',
    refund_account_id: props.accounts[0]?.id ?? '',
    lines: props.sale.lines.map((line) => ({
        sale_line_id: line.id,
        quantity: 0,
        returns_to_inventory: true,
        condition_notes: '',
    })),
});

const submit = () => form
    .transform((data) => ({
        ...data,
        refund_account_id: data.refund_account_id || null,
        lines: data.lines.filter((line) => Number(line.quantity) > 0),
    }))
    .post(`/sales/${props.sale.id}/returns`);
</script>

<template>
    <Head title="Registrar devolución" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header>
            <h1 class="text-2xl font-semibold">Registrar devolución</h1>
            <p class="text-sm text-muted-foreground">{{ sale.document_number }} · {{ sale.customer }}</p>
        </header>

        <form class="grid gap-5" @submit.prevent="submit">
            <section class="overflow-x-auto rounded-xl border bg-card">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/40 text-left">
                        <tr>
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-4 py-3">Disponible para devolver</th>
                            <th class="px-4 py-3">Cantidad</th>
                            <th class="px-4 py-3">Reintegrar inventario</th>
                            <th class="px-4 py-3">Estado / novedad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="(line, index) in sale.lines" :key="line.id">
                            <td class="px-4 py-3">{{ line.product }}</td>
                            <td class="px-4 py-3">{{ line.available_quantity }}</td>
                            <td class="px-4 py-3">
                                <Input v-model.number="form.lines[index].quantity" type="number" min="0" :max="line.available_quantity" step="0.000001" class="w-32" />
                            </td>
                            <td class="px-4 py-3">
                                <input v-model="form.lines[index].returns_to_inventory" type="checkbox" class="size-4" />
                            </td>
                            <td class="px-4 py-3">
                                <Input v-model="form.lines[index].condition_notes" placeholder="Opcional" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="grid gap-4 rounded-xl border bg-card p-5 md:grid-cols-2">
                <label class="grid gap-1 text-sm">
                    Fecha y hora
                    <Input v-model="form.returned_at" type="datetime-local" />
                </label>
                <label class="grid gap-1 text-sm">
                    Cuenta para un posible reembolso
                    <select v-model="form.refund_account_id" class="h-9 rounded-md border bg-transparent px-3 text-sm">
                        <option value="">Solo aplicar crédito a cartera</option>
                        <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                    </select>
                </label>
                <label class="grid gap-1 text-sm md:col-span-2">
                    Motivo
                    <Input v-model="form.reason" required placeholder="Motivo de la devolución" />
                </label>
                <p v-if="Object.keys(form.errors).length" class="text-sm text-destructive md:col-span-2">{{ Object.values(form.errors)[0] }}</p>
                <div class="flex justify-end gap-2 md:col-span-2">
                    <Button variant="outline" as-child><Link :href="`/sales/${sale.id}`">Cancelar</Link></Button>
                    <Button type="submit" :disabled="form.processing">Confirmar devolución</Button>
                </div>
            </section>
        </form>
    </main>
</template>
