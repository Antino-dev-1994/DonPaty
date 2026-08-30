<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Unit = { id: string; code: string; name: string; dimension: string; dimension_label: string; scale_to_base: string; precision: number; is_active: boolean };
type Conversion = { id: string; factor: string; from_unit: { code: string; name: string }; to_unit: { code: string; name: string } };
type Option = { value: string; label: string };
const props = defineProps<{ units: Unit[]; conversions: Conversion[]; dimensions: Option[]; canManage: boolean }>();

const unitForm = useForm({ code: '', name: '', dimension: 'mass', scale_to_base: '1', precision: 3, is_active: true });
const conversionForm = useForm({ from_unit_id: '', to_unit_id: '' });
const storeUnit = () => unitForm.post('/catalog/units', { preserveScroll: true, onSuccess: () => unitForm.reset('code', 'name') });
const storeConversion = () => conversionForm.post('/catalog/unit-conversions', { preserveScroll: true });
</script>

<template>
    <Head title="Unidades" />
    <main class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <header class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center"><div><h1 class="text-2xl font-semibold">Unidades y conversiones</h1><p class="text-sm text-muted-foreground">Las conversiones solo son válidas dentro de la misma dimensión.</p></div><a href="/catalog/items" class="text-sm font-medium text-amber-700 hover:underline">Ver artículos</a></header>

        <div class="grid gap-5 xl:grid-cols-[1.4fr_1fr]">
            <section class="overflow-hidden rounded-xl border bg-card"><div class="border-b px-5 py-4"><h2 class="font-semibold">Unidades configuradas</h2></div><div class="overflow-x-auto"><table class="w-full text-sm"><thead class="border-b bg-muted/40 text-left"><tr><th class="px-4 py-3">Código</th><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Dimensión</th><th class="px-4 py-3">Escala base</th><th class="px-4 py-3">Estado</th></tr></thead><tbody class="divide-y"><tr v-for="unit in units" :key="unit.id"><td class="px-4 py-3 font-mono">{{ unit.code }}</td><td class="px-4 py-3">{{ unit.name }}</td><td class="px-4 py-3">{{ unit.dimension_label }}</td><td class="px-4 py-3">{{ unit.scale_to_base }}</td><td class="px-4 py-3" :class="unit.is_active ? 'text-emerald-700' : 'text-muted-foreground'">{{ unit.is_active ? 'Activa' : 'Inactiva' }}</td></tr></tbody></table></div></section>

            <section v-if="canManage" class="rounded-xl border bg-card p-5"><h2 class="mb-4 font-semibold">Nueva unidad</h2><form class="grid gap-4" @submit.prevent="storeUnit"><div class="grid grid-cols-2 gap-3"><div class="grid gap-2"><Label for="code">Código</Label><Input id="code" v-model="unitForm.code" required /><InputError :message="unitForm.errors.code" /></div><div class="grid gap-2"><Label for="name">Nombre</Label><Input id="name" v-model="unitForm.name" required /><InputError :message="unitForm.errors.name" /></div></div><div class="grid gap-2"><Label>Dimensión</Label><select v-model="unitForm.dimension" class="h-9 rounded-md border bg-transparent px-3 text-sm"><option v-for="dimension in dimensions" :key="dimension.value" :value="dimension.value">{{ dimension.label }}</option></select></div><div class="grid grid-cols-2 gap-3"><div class="grid gap-2"><Label>Escala a base</Label><Input v-model="unitForm.scale_to_base" type="number" min="0.00000001" step="any" required /></div><div class="grid gap-2"><Label>Decimales</Label><Input v-model.number="unitForm.precision" type="number" min="0" max="8" required /></div></div><Button type="submit" :disabled="unitForm.processing">Crear unidad</Button></form></section>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1.4fr_1fr]">
            <section class="rounded-xl border bg-card p-5"><h2 class="mb-3 font-semibold">Conversiones explícitas</h2><div class="space-y-2"><div v-for="conversion in conversions" :key="conversion.id" class="flex justify-between rounded-lg border px-3 py-2 text-sm"><span>1 {{ conversion.from_unit.code }} = {{ conversion.factor }} {{ conversion.to_unit.code }}</span><span class="text-muted-foreground">{{ conversion.from_unit.name }} → {{ conversion.to_unit.name }}</span></div><p v-if="conversions.length === 0" class="text-sm text-muted-foreground">Aún no hay conversiones adicionales.</p></div></section>
            <section v-if="canManage" class="rounded-xl border bg-card p-5"><h2 class="mb-4 font-semibold">Agregar conversión</h2><form class="grid gap-4" @submit.prevent="storeConversion"><div class="grid gap-2"><Label>Desde</Label><select v-model="conversionForm.from_unit_id" required class="h-9 rounded-md border bg-transparent px-3 text-sm"><option value="" disabled>Selecciona</option><option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }} ({{ unit.code }})</option></select></div><div class="grid gap-2"><Label>Hacia</Label><select v-model="conversionForm.to_unit_id" required class="h-9 rounded-md border bg-transparent px-3 text-sm"><option value="" disabled>Selecciona</option><option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }} ({{ unit.code }})</option></select></div><InputError :message="conversionForm.errors.from_unit_id || conversionForm.errors.to_unit_id" /><Button type="submit" :disabled="conversionForm.processing">Calcular y guardar</Button></form></section>
        </div>
    </main>
</template>
