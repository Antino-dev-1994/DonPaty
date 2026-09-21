<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AttachmentPanel from '@/components/AttachmentPanel.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Availability = {
    consumption_id: string;
    item: string;
    presentation: string;
    required_quantity: string;
    available_quantity: string;
    unit: string;
    is_short: boolean;
};

type Authorization = {
    id: string;
    permission: string;
    status: string;
    usable: boolean;
    reason: string;
};

const props = defineProps<{
    order: any;
    availability: Availability[];
    authorizations: Authorization[];
    canManage: boolean;
    canComplete: boolean;
    canReverse: boolean;
    canRequestAuthorization: boolean;
    canViewCosts: boolean;
    attachments: any;
}>();

const shortage = computed(() => props.availability.some((line) => line.is_short));
const usableNegative = computed(() =>
    props.authorizations.find(
        (item) => item.permission === 'inventory.authorize-negative' && item.usable,
    ),
);

const startForm = useForm({ authorization_request_id: usableNegative.value?.id ?? null });
const authForm = useForm({ authorization_type: 'negative_stock', reason: '' });
const incidentForm = useForm({
    incident_type: 'operational',
    description: '',
    quantity: null,
    amount: null,
});
const reverseForm = useForm({ reason: '', authorization_request_id: null });
const money = (value: number) =>
    new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        maximumFractionDigits: 0,
    }).format(value || 0);
</script>

<template>
    <Head :title="order.document_number" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
                <p class="text-sm text-muted-foreground">
                    {{ order.recipe }} · versión {{ order.recipe_version }}
                </p>
                <h1 class="text-2xl font-semibold">{{ order.document_number }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ order.status_label }} · {{ order.planned_for }} · {{ order.responsible }}
                </p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" as-child><Link href="/production">Volver</Link></Button>
                <Button v-if="order.status === 'in_progress' && canComplete" as-child>
                    <Link :href="`/production/${order.id}/complete`">Finalizar producción</Link>
                </Button>
            </div>
        </header>

        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border bg-card p-4">
                <p class="text-xs text-muted-foreground">Harina</p>
                <strong class="text-xl">{{ order.flour_quantity }} kg</strong>
            </div>
            <div class="rounded-xl border bg-card p-4">
                <p class="text-xs text-muted-foreground">Masa esperada / real</p>
                <strong>{{ order.expected_dough_quantity }} / {{ order.actual_dough_quantity || '—' }} kg</strong>
            </div>
            <div class="rounded-xl border bg-card p-4">
                <p class="text-xs text-muted-foreground">Merma</p>
                <strong>{{ order.waste_quantity || '—' }} kg</strong>
            </div>
            <div v-if="canViewCosts" class="rounded-xl border bg-card p-4">
                <p class="text-xs text-muted-foreground">Costo total</p>
                <strong class="text-xl">{{ money(order.total_cost) }}</strong>
            </div>
        </section>

        <section v-if="order.status === 'planned'" class="grid gap-4">
            <div class="overflow-x-auto rounded-xl border bg-card">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/40 text-left">
                        <tr>
                            <th class="px-4 py-3">Ingrediente</th>
                            <th class="px-4 py-3">Requerido</th>
                            <th class="px-4 py-3">Disponible</th>
                            <th class="px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="line in availability" :key="line.consumption_id">
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ line.item }}</p>
                                <p class="text-xs text-muted-foreground">{{ line.presentation }}</p>
                            </td>
                            <td class="px-4 py-3">{{ line.required_quantity }} {{ line.unit }}</td>
                            <td class="px-4 py-3">{{ line.available_quantity }} {{ line.unit }}</td>
                            <td
                                class="px-4 py-3"
                                :class="line.is_short ? 'text-destructive' : 'text-emerald-700'"
                            >
                                {{ line.is_short ? 'Faltante' : 'Disponible' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div
                v-if="shortage && !usableNegative"
                class="rounded-xl border border-amber-300 bg-amber-50 p-4 dark:bg-amber-950/30"
            >
                <p class="text-sm">Hay faltantes. Solicita autorización antes de iniciar.</p>
                <form
                    v-if="canRequestAuthorization"
                    class="mt-3 flex flex-col gap-2 sm:flex-row"
                    @submit.prevent="authForm.post(`/production/${order.id}/authorizations`)"
                >
                    <input v-model="authForm.authorization_type" type="hidden" />
                    <Input v-model="authForm.reason" required placeholder="Motivo del faltante" />
                    <Button type="submit" variant="outline">Solicitar autorización</Button>
                </form>
            </div>
            <form
                v-if="canManage"
                class="flex justify-end"
                @submit.prevent="startForm.post(`/production/${order.id}/start`)"
            >
                <input v-model="startForm.authorization_request_id" type="hidden" />
                <Button type="submit" :disabled="shortage && !usableNegative">Iniciar producción</Button>
            </form>
        </section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="rounded-xl border bg-card p-4">
                <h2 class="font-semibold">Consumos</h2>
                <div class="mt-3 divide-y">
                    <div
                        v-for="line in order.consumptions"
                        :key="line.id"
                        class="flex justify-between py-2 text-sm"
                    >
                        <span>
                            {{ line.item }}
                            <small class="block text-muted-foreground">{{ line.difference_reason }}</small>
                        </span>
                        <span class="text-right">
                            {{ line.actual_quantity ?? line.calculated_quantity }} {{ line.unit }}
                            <small
                                v-if="canViewCosts && line.total_cost"
                                class="block text-muted-foreground"
                            >
                                {{ money(line.total_cost) }}
                            </small>
                        </span>
                    </div>
                </div>
            </section>
            <section class="rounded-xl border bg-card p-4">
                <h2 class="font-semibold">Productos</h2>
                <div class="mt-3 divide-y">
                    <div
                        v-for="line in order.outputs.length ? order.outputs : order.planned_outputs"
                        :key="line.id"
                        class="flex justify-between py-2 text-sm"
                    >
                        <span>{{ line.product }}</span>
                        <span>{{ line.quantity }} und · {{ line.dough_quantity }} kg</span>
                    </div>
                </div>
            </section>
        </div>

        <section v-if="order.status === 'in_progress' && canManage" class="rounded-xl border bg-card p-4">
            <h2 class="font-semibold">Registrar novedad</h2>
            <form
                class="mt-3 grid gap-3 md:grid-cols-[10rem_1fr_auto]"
                @submit.prevent="incidentForm.post(`/production/${order.id}/incidents`, { onSuccess: () => incidentForm.reset() })"
            >
                <select v-model="incidentForm.incident_type" class="h-9 rounded-md border bg-transparent px-3 text-sm">
                    <option value="operational">Operativa</option>
                    <option value="quality">Calidad</option>
                    <option value="waste">Merma</option>
                    <option value="equipment">Equipo</option>
                </select>
                <Input v-model="incidentForm.description" required placeholder="Describe lo ocurrido" />
                <Button type="submit" variant="outline">Registrar</Button>
            </form>
        </section>

        <section class="rounded-xl border bg-card p-4">
            <h2 class="font-semibold">Novedades</h2>
            <div v-if="order.incidents.length" class="mt-3 divide-y">
                <div v-for="incident in order.incidents" :key="incident.id" class="py-2 text-sm">
                    <p>{{ incident.description }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ incident.incident_type }} · {{ incident.recorded_at }} · {{ incident.recorder }}
                    </p>
                </div>
            </div>
            <p v-else class="mt-2 text-sm text-muted-foreground">Sin novedades.</p>
        </section>

        <AttachmentPanel :attachments="attachments" />

        <section
            v-if="order.status === 'completed' && canViewCosts"
            class="rounded-xl border bg-card p-4"
        >
            <h2 class="font-semibold">Desglose de costos</h2>
            <div class="mt-3 grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
                <div><p class="text-xs text-muted-foreground">Ingredientes</p><strong>{{ money(order.ingredient_cost) }}</strong></div>
                <div><p class="text-xs text-muted-foreground">Mano de obra</p><strong>{{ money(order.labor_cost) }}</strong></div>
                <div><p class="text-xs text-muted-foreground">Servicios</p><strong>{{ money(order.overhead_cost) }}</strong></div>
                <div><p class="text-xs text-muted-foreground">Costos por lote</p><strong>{{ money(order.batch_cost) }}</strong></div>
                <div><p class="text-xs text-muted-foreground">Total</p><strong>{{ money(order.total_cost) }}</strong></div>
            </div>
            <div v-if="order.batch_costs?.length" class="mt-4 divide-y border-t text-sm"><div v-for="cost in order.batch_costs" :key="cost.id" class="flex justify-between py-2"><span>{{ cost.label }}</span><strong>{{ money(cost.amount) }}</strong></div></div>
        </section>

        <section
            v-if="order.status === 'completed' && canReverse"
            class="rounded-xl border border-destructive/30 p-4"
        >
            <h2 class="font-semibold">Reversión controlada</h2>
            <form
                class="mt-3 flex flex-col gap-2 sm:flex-row"
                @submit.prevent="reverseForm.post(`/production/${order.id}/reverse`)"
            >
                <Input v-model="reverseForm.reason" required placeholder="Motivo de reversión" />
                <Button type="submit" variant="destructive">Revertir producción</Button>
            </form>
        </section>

        <p v-if="order.status === 'reversed'" class="rounded-xl border p-4 text-sm">
            <strong>Producción revertida.</strong> {{ order.reversal_reason }}
        </p>
    </main>
</template>
