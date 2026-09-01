<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Supplier = { name: string; trade_name: string | null; document_type: string | null; document_number: string | null; tax_identifier: string | null; email: string | null; phone: string | null; default_payment_term_days: number; notes: string | null; is_active: boolean };
const props = defineProps<{ submitUrl: string; method: 'post' | 'put'; supplier?: Supplier }>();
const form = useForm({
    name: props.supplier?.name ?? '', trade_name: props.supplier?.trade_name ?? '',
    document_type: props.supplier?.document_type ?? '', document_number: props.supplier?.document_number ?? '',
    tax_identifier: props.supplier?.tax_identifier ?? '', email: props.supplier?.email ?? '', phone: props.supplier?.phone ?? '',
    default_payment_term_days: props.supplier?.default_payment_term_days ?? 0, notes: props.supplier?.notes ?? '',
    is_active: props.supplier?.is_active ?? true,
});
const submit = () => props.method === 'post' ? form.post(props.submitUrl) : form.put(props.submitUrl);
</script>

<template>
    <form class="grid gap-5" @submit.prevent="submit">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2"><Label for="name">Nombre o razón social</Label><Input id="name" v-model="form.name" required /><p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p></div>
            <div class="grid gap-2"><Label for="trade_name">Nombre comercial</Label><Input id="trade_name" v-model="form.trade_name" /><p v-if="form.errors.trade_name" class="text-sm text-destructive">{{ form.errors.trade_name }}</p></div>
            <div class="grid gap-2"><Label for="document_type">Tipo de documento</Label><Input id="document_type" v-model="form.document_type" placeholder="NIT, CC..." /></div>
            <div class="grid gap-2"><Label for="document_number">Número de documento</Label><Input id="document_number" v-model="form.document_number" /><p v-if="form.errors.document_number" class="text-sm text-destructive">{{ form.errors.document_number }}</p></div>
            <div class="grid gap-2"><Label for="tax_identifier">Identificación tributaria</Label><Input id="tax_identifier" v-model="form.tax_identifier" /></div>
            <div class="grid gap-2"><Label for="payment_days">Plazo habitual (días)</Label><Input id="payment_days" v-model.number="form.default_payment_term_days" type="number" min="0" required /></div>
            <div class="grid gap-2"><Label for="email">Correo</Label><Input id="email" v-model="form.email" type="email" /></div>
            <div class="grid gap-2"><Label for="phone">Teléfono</Label><Input id="phone" v-model="form.phone" /></div>
        </div>
        <div class="grid gap-2"><Label for="notes">Notas</Label><textarea id="notes" v-model="form.notes" rows="3" class="rounded-md border bg-transparent px-3 py-2 text-sm" /></div>
        <label class="flex items-center gap-2 text-sm"><input v-model="form.is_active" type="checkbox" /> Proveedor activo</label>
        <div class="flex justify-end gap-2"><Button variant="outline" as-child><Link href="/purchasing/suppliers">Cancelar</Link></Button><Button type="submit" :disabled="form.processing">Guardar proveedor</Button></div>
    </form>
</template>
