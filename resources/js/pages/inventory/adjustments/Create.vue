<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import FirstUseGuide from '@/components/guides/FirstUseGuide.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Presentation = { id: string; name: string; sku: string; unit: string; physical_quantity: string; average_unit_cost: number; allow_negative_stock: boolean };
type Draft = { id: string; adjustment_type: 'initial' | 'manual'; effective_at: string; reason: string; lines: { presentation_id: string; counted_quantity: string; unit_cost: number | null }[] };
const props = defineProps<{ presentations: Presentation[]; adjustment?: Draft; defaultAdjustmentType: 'initial' | 'manual' }>();
const now = new Date();
const localNow = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
const form = useForm({ adjustment_type: props.adjustment?.adjustment_type ?? props.defaultAdjustmentType, effective_at: props.adjustment?.effective_at ?? localNow, reason: props.adjustment?.reason ?? '', lines: props.adjustment?.lines ?? [] as { presentation_id: string; counted_quantity: string; unit_cost: number | null }[], replaces_adjustment_id: props.adjustment?.id ?? null as string | null });
const available = computed(() => props.presentations.filter((presentation) => !form.lines.some((line) => line.presentation_id === presentation.id)));
const selected = (id: string) => props.presentations.find((presentation) => presentation.id === id);
const add = () => { const presentation = available.value[0]; if (presentation) form.lines.push({ presentation_id: presentation.id, counted_quantity: presentation.physical_quantity, unit_cost: presentation.average_unit_cost || null }); };
const remove = (index: number) => form.lines.splice(index, 1);
const lineTotal = (line: { counted_quantity: string; unit_cost: number | null }) => Math.round(Number(line.counted_quantity || 0) * Number(line.unit_cost || 0));
const money = (value: number) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(value || 0);
const submit = () => form.post('/inventory/adjustments');
const guideSteps = [
    { title: 'Elige el tipo correcto', description: 'Para el primer conteo de cada insumo usa Inventario inicial. Usa Ajuste manual solo después de tener movimientos previos.', target: '[data-guide="type"]' },
    { title: 'Registra el momento del conteo', description: 'Usa la fecha y hora reales. Haz el inventario inicial antes de compras, producciones o ventas.', target: '[data-guide="date"]' },
    { title: 'Explica el conteo', description: 'Escribe un motivo claro, por ejemplo: Conteo físico de apertura.', target: '[data-guide="reason"]' },
    { title: 'Cantidad y costo unitario', description: 'Agrega cada insumo contado. El costo es por kg, gramo, ml o unidad según se muestra; el sistema multiplica cantidad × costo unitario y te muestra el total.', target: '[data-guide="lines"]' },
    { title: 'Guarda y revisa', description: 'Guardar crea un borrador, no mueve inventario. En la siguiente pantalla revisas los totales y solo entonces confirmas.', target: '[data-guide="save"]' },
];
</script>

<template>
    <Head :title="adjustment ? 'Corregir borrador de inventario' : 'Nuevo ajuste'" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <FirstUseGuide storage-key="inventory-adjustment-first-use-v1" title="Guía para tu primer conteo" :steps="guideSteps" />
        <header><h1 class="text-2xl font-semibold">{{ adjustment ? 'Corregir borrador de inventario' : 'Nuevo ajuste de inventario' }}</h1><p class="text-sm text-muted-foreground">El movimiento se genera únicamente al confirmar el borrador.</p></header>
        <form class="max-w-4xl space-y-5" @submit.prevent="submit">
            <section class="grid gap-5 rounded-xl border bg-card p-5 md:grid-cols-2">
                <div data-guide="type" class="grid gap-2"><Label>Tipo</Label><select v-model="form.adjustment_type" class="h-9 rounded-md border bg-transparent px-3 text-sm"><option value="manual">Ajuste manual</option><option value="initial">Inventario inicial</option></select><small class="text-muted-foreground">Selecciona Inventario inicial para el primer conteo.</small></div>
                <div data-guide="date" class="grid gap-2"><Label>Fecha efectiva</Label><Input v-model="form.effective_at" type="datetime-local" required /></div>
                <div data-guide="reason" class="grid gap-2 md:col-span-2"><Label>Motivo</Label><textarea v-model="form.reason" rows="3" required class="rounded-md border bg-transparent px-3 py-2 text-sm" /><InputError :message="form.errors.reason" /></div>
            </section>
            <section data-guide="lines" class="rounded-xl border bg-card p-5">
                <div class="mb-4 flex items-center justify-between"><div><h2 class="font-semibold">Conteo</h2><p class="text-sm text-muted-foreground">El costo unitario se multiplica por la cantidad para calcular el valor de entrada.</p></div><Button type="button" variant="outline" size="sm" :disabled="available.length === 0" @click="add">Agregar presentación</Button></div>
                <div class="space-y-3"><div v-for="(line, index) in form.lines" :key="index" class="grid gap-2 rounded-lg border p-3 md:grid-cols-[1fr_8rem_10rem_9rem_auto]"><select v-model="line.presentation_id" required class="h-9 rounded-md border bg-transparent px-3 text-sm"><option v-for="presentation in presentations" :key="presentation.id" :value="presentation.id">{{ presentation.name }} ({{ presentation.sku }})</option></select><Input v-model="line.counted_quantity" type="number" step="any" required :title="`Existencia actual: ${selected(line.presentation_id)?.physical_quantity}`" /><Input v-model.number="line.unit_cost" type="number" min="0" step="1" placeholder="Costo unitario COP" /><p class="flex items-center justify-end text-sm font-medium">{{ money(lineTotal(line)) }}</p><Button type="button" variant="ghost" @click="remove(index)">Quitar</Button><p class="text-xs text-muted-foreground md:col-span-5">Actual: {{ selected(line.presentation_id)?.physical_quantity }} {{ selected(line.presentation_id)?.unit }} · Costo unitario × cantidad = total de entrada<span v-if="selected(line.presentation_id)?.allow_negative_stock"> · admite autorización negativa</span></p></div><p v-if="form.lines.length === 0" class="py-6 text-center text-sm text-muted-foreground">Agrega las presentaciones contadas.</p></div><InputError :message="form.errors.lines" />
            </section>
            <div class="flex justify-end gap-2"><Button type="button" variant="outline" as-child><Link href="/inventory/adjustments">Cancelar</Link></Button><Button data-guide="save" type="submit" :disabled="form.processing || form.lines.length === 0">{{ adjustment ? 'Guardar corrección' : 'Guardar borrador' }}</Button></div>
        </form>
    </main>
</template>
