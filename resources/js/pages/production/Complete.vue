<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Consumption = { id: string; item: string; calculated_quantity: string; unit: string };
type Product = { id: string; name: string };
type PlannedOutput = { compatible_product_id: string; quantity: string; dough_quantity: string };
type Order = {
    id: string;
    document_number: string;
    expected_dough_quantity: string;
    flour_quantity: string;
    labor_method: 'standard_per_kg' | 'actual_hours' | 'authorized_manual';
    consumptions: Consumption[];
    products: Product[];
    planned_outputs: PlannedOutput[];
};
type Person = { id: string; name: string };
type Authorization = { id: string; reason: string };

const props = defineProps<{
    order: Order;
    people: Person[];
    responsiblePersonId: string;
    manualAuthorizations: Authorization[];
    now: string;
}>();

const form = useForm({
    completed_at: props.now,
    actual_dough_quantity: Number(props.order.expected_dough_quantity),
    waste_quantity: 0,
    consumptions: props.order.consumptions.map((line) => ({
        consumption_id: line.id,
        actual_quantity: Number(line.calculated_quantity),
        difference_reason: '',
    })),
    outputs: props.order.planned_outputs.map((line) => ({
        compatible_product_id: line.compatible_product_id,
        quantity: Number(line.quantity),
        dough_quantity: Number(line.dough_quantity),
    })),
    labor_hours: [{ person_id: props.responsiblePersonId, hours: 1, hourly_rate: 0 }],
    manual_labor_amount: null as number | null,
    manual_labor_reason: '',
    manual_labor_authorization_id: props.manualAuthorizations[0]?.id ?? '',
});
const manualAuthorizationForm = useForm({ authorization_type: 'manual_labor', reason: '' });

const distributedDough = computed(() =>
    form.outputs.reduce((total, line) => total + Number(line.dough_quantity || 0), 0)
    + Number(form.waste_quantity || 0),
);
const doughDifference = computed(() => Number(form.actual_dough_quantity || 0) - distributedDough.value);
const addOutput = () => form.outputs.push({ compatible_product_id: props.order.products[0]?.id ?? '', quantity: 1, dough_quantity: 0 });
const addLabor = () => form.labor_hours.push({ person_id: props.responsiblePersonId, hours: 1, hourly_rate: 0 });
</script>

<template>
    <Head :title="`Finalizar ${order.document_number}`" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header>
            <h1 class="text-2xl font-semibold">Finalizar {{ order.document_number }}</h1>
            <p class="text-sm text-muted-foreground">Registra lo ocurrido realmente. Al confirmar se afectan inventarios y costos en una sola operación.</p>
        </header>

        <section v-if="order.labor_method === 'authorized_manual' && !manualAuthorizations.length" class="rounded-xl border border-amber-300 bg-amber-50 p-4 dark:bg-amber-950/30">
            <h2 class="font-semibold">Autorización de mano de obra manual</h2>
            <p class="mt-1 text-sm">Solicita autorización antes de confirmar el valor manual.</p>
            <form class="mt-3 flex flex-col gap-2 sm:flex-row" @submit.prevent="manualAuthorizationForm.post(`/production/${order.id}/authorizations`)">
                <input v-model="manualAuthorizationForm.authorization_type" type="hidden" />
                <Input v-model="manualAuthorizationForm.reason" required placeholder="Justificación del valor manual" />
                <Button type="submit" variant="outline" :disabled="manualAuthorizationForm.processing">Solicitar autorización</Button>
            </form>
        </section>

        <form class="grid gap-6" @submit.prevent="form.post(`/production/${order.id}/complete`)">
            <section class="grid gap-4 rounded-xl border bg-card p-5 md:grid-cols-3">
                <div class="grid gap-2">
                    <Label>Fecha de finalización</Label>
                    <Input v-model="form.completed_at" type="datetime-local" />
                </div>
                <div class="grid gap-2">
                    <Label>Masa real (kg)</Label>
                    <Input v-model.number="form.actual_dough_quantity" type="number" min="0.000001" step="0.000001" />
                </div>
                <div class="grid gap-2">
                    <Label>Merma de masa (kg)</Label>
                    <Input v-model.number="form.waste_quantity" type="number" min="0" step="0.000001" />
                </div>
                <div class="rounded-lg bg-muted/40 p-3 text-sm md:col-span-3">
                    Esperada: <strong>{{ Number(order.expected_dough_quantity).toFixed(3) }} kg</strong>
                    · Distribuida + merma: <strong>{{ distributedDough.toFixed(3) }} kg</strong>
                    · Diferencia pendiente: <strong :class="Math.abs(doughDifference) > 0.0005 ? 'text-destructive' : 'text-emerald-700'">{{ doughDifference.toFixed(3) }} kg</strong>
                </div>
            </section>

            <section class="grid gap-3">
                <div>
                    <h2 class="font-semibold">Consumos reales</h2>
                    <p class="text-xs text-muted-foreground">Toda diferencia frente a la receta debe llevar un motivo.</p>
                </div>
                <div v-for="(line, index) in form.consumptions" :key="line.consumption_id" class="grid gap-3 rounded-lg border p-3 md:grid-cols-[1fr_12rem_2fr]">
                    <div class="text-sm">
                        <p class="font-medium">{{ order.consumptions[index].item }}</p>
                        <p class="text-xs text-muted-foreground">Calculado: {{ order.consumptions[index].calculated_quantity }} {{ order.consumptions[index].unit }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label>Cantidad real</Label>
                        <Input v-model.number="line.actual_quantity" type="number" min="0" step="0.000001" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Motivo de diferencia</Label>
                        <Input v-model="line.difference_reason" placeholder="Obligatorio solo si cambió" />
                    </div>
                </div>
            </section>

            <section class="grid gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">Productos obtenidos</h2>
                        <p class="text-xs text-muted-foreground">Distribuye la masa real entre productos compatibles.</p>
                    </div>
                    <Button type="button" variant="outline" size="sm" @click="addOutput"><Plus /> Producto</Button>
                </div>
                <div v-for="(line, index) in form.outputs" :key="index" class="grid items-end gap-3 rounded-lg border p-3 md:grid-cols-[1fr_10rem_10rem_auto]">
                    <div class="grid gap-2">
                        <Label>Producto</Label>
                        <select v-model="line.compatible_product_id" class="h-9 rounded-md border bg-transparent px-3 text-sm">
                            <option v-for="product in order.products" :key="product.id" :value="product.id">{{ product.name }}</option>
                        </select>
                    </div>
                    <div class="grid gap-2"><Label>Cantidad</Label><Input v-model.number="line.quantity" type="number" min="0.000001" step="0.000001" /></div>
                    <div class="grid gap-2"><Label>Masa usada (kg)</Label><Input v-model.number="line.dough_quantity" type="number" min="0.000001" step="0.000001" /></div>
                    <Button type="button" variant="ghost" size="icon" :disabled="form.outputs.length === 1" @click="form.outputs.splice(index, 1)"><Trash2 /></Button>
                </div>
            </section>

            <section class="grid gap-3 rounded-xl border bg-card p-5">
                <div>
                    <h2 class="font-semibold">Mano de obra</h2>
                    <p class="text-xs text-muted-foreground">Método seleccionado: {{ order.labor_method }}</p>
                </div>
                <p v-if="order.labor_method === 'standard_per_kg'" class="text-sm">Se calculará con la tarifa por kilogramo configurada en el periodo.</p>
                <template v-else-if="order.labor_method === 'actual_hours'">
                    <div v-for="(line, index) in form.labor_hours" :key="index" class="grid items-end gap-3 md:grid-cols-[1fr_10rem_12rem_auto]">
                        <div class="grid gap-2"><Label>Persona</Label><select v-model="line.person_id" class="h-9 rounded-md border bg-transparent px-3 text-sm"><option v-for="person in people" :key="person.id" :value="person.id">{{ person.name }}</option></select></div>
                        <div class="grid gap-2"><Label>Horas</Label><Input v-model.number="line.hours" type="number" min="0.0001" step="0.0001" /></div>
                        <div class="grid gap-2"><Label>Tarifa por hora (COP)</Label><Input v-model.number="line.hourly_rate" type="number" min="0" step="1" /></div>
                        <Button type="button" variant="ghost" size="icon" :disabled="form.labor_hours.length === 1" @click="form.labor_hours.splice(index, 1)"><Trash2 /></Button>
                    </div>
                    <Button type="button" variant="outline" size="sm" class="w-fit" @click="addLabor"><Plus /> Persona</Button>
                </template>
                <template v-else>
                    <div v-if="manualAuthorizations.length" class="grid gap-3 md:grid-cols-3">
                        <div class="grid gap-2"><Label>Valor manual (COP)</Label><Input v-model.number="form.manual_labor_amount" type="number" min="0" step="1" /></div>
                        <div class="grid gap-2"><Label>Justificación</Label><Input v-model="form.manual_labor_reason" /></div>
                        <div class="grid gap-2"><Label>Autorización</Label><select v-model="form.manual_labor_authorization_id" class="h-9 rounded-md border bg-transparent px-3 text-sm"><option v-for="authorization in manualAuthorizations" :key="authorization.id" :value="authorization.id">{{ authorization.reason }}</option></select></div>
                    </div>
                    <p v-else class="text-sm text-amber-700">Primero solicita y aprueba la autorización de mano de obra manual desde el detalle de la producción.</p>
                </template>
            </section>

            <p v-if="Object.keys(form.errors).length" class="text-sm text-destructive">{{ Object.values(form.errors)[0] }}</p>
            <div class="flex justify-end gap-2">
                <Button variant="outline" as-child><Link :href="`/production/${order.id}`">Cancelar</Link></Button>
                <Button type="submit" :disabled="form.processing || Math.abs(doughDifference) > 0.0005 || (order.labor_method === 'authorized_manual' && !manualAuthorizations.length)">Confirmar producción</Button>
            </div>
        </form>
    </main>
</template>
