<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CheckCircle2, Circle } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';

type Configuration = { status: string; cutoff_at: string | null; attestations: Record<string, boolean>; notes: string | null; activated_at: string | null; activated_by: string | null };
type Readiness = { key: string; label: string; complete: boolean; url: string };
type AttestationKey = 'inventory' | 'balances' | 'obligations' | 'orders' | 'permissions';
const props = defineProps<{ configuration: Configuration; readiness: Readiness[] }>();

const form = useForm({
    cutoff_at: props.configuration.cutoff_at ?? '',
    notes: props.configuration.notes ?? '',
    attestations: {
        inventory: props.configuration.attestations.inventory ?? false,
        balances: props.configuration.attestations.balances ?? false,
        obligations: props.configuration.attestations.obligations ?? false,
        orders: props.configuration.attestations.orders ?? false,
        permissions: props.configuration.attestations.permissions ?? false,
    },
});
const activation = useForm({ current_password: '', confirmation: '' });
const attestationItems: { key: AttestationKey; label: string }[] = [
    { key: 'inventory', label: 'Confirmo que el conteo e inventario inicial fueron revisados.' },
    { key: 'balances', label: 'Confirmo que caja, bancos y cuentas del hogar están conciliados.' },
    { key: 'obligations', label: 'Confirmo que cartera, obligaciones y deudas abiertas están cargadas.' },
    { key: 'orders', label: 'Confirmo que pedidos y anticipos pendientes están cargados.' },
    { key: 'permissions', label: 'Confirmo que usuarios, roles y permisos fueron revisados.' },
];
</script>

<template>
    <Head title="Puesta en marcha" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header><h1 class="text-2xl font-semibold">Puesta en marcha</h1><p class="text-sm text-muted-foreground">Registra la carga inicial y activa una fecha de corte única antes de depender del sistema.</p></header>

        <section v-if="configuration.status === 'active'" class="rounded-xl border border-emerald-300 bg-emerald-50 p-5 dark:bg-emerald-950/20"><h2 class="font-semibold">Operación oficial activa</h2><p class="mt-1 text-sm">Fecha de corte: {{ configuration.cutoff_at }}.</p><p class="text-xs text-muted-foreground">Activada {{ configuration.activated_at }} por {{ configuration.activated_by }}. La fecha queda bloqueada para conservar trazabilidad.</p></section>

        <div class="grid gap-5 lg:grid-cols-2">
            <section class="rounded-xl border bg-card p-5"><h2 class="font-semibold">Requisitos verificables</h2><div class="mt-3 divide-y"><Link v-for="item in readiness" :key="item.key" :href="item.url" class="flex items-center justify-between gap-3 py-3 text-sm hover:underline"><span class="flex items-center gap-2"><CheckCircle2 v-if="item.complete" class="size-4 text-emerald-600" /><Circle v-else class="size-4 text-amber-600" />{{ item.label }}</span><span>{{ item.complete ? 'Listo' : 'Pendiente' }}</span></Link></div></section>

            <form v-if="configuration.status !== 'active'" class="rounded-xl border bg-card p-5" @submit.prevent="form.put('/launch', { preserveScroll: true })"><h2 class="font-semibold">Fecha y declaraciones</h2><label class="mt-4 grid gap-1 text-sm">Fecha y hora de corte<Input v-model="form.cutoff_at" type="datetime-local" required /></label><div class="mt-4 grid gap-3"><label v-for="item in attestationItems" :key="item.key" class="flex items-start gap-2 text-sm"><input v-model="form.attestations[item.key]" type="checkbox" class="mt-1" />{{ item.label }}</label></div><label class="mt-4 grid gap-1 text-sm">Notas de conciliación<textarea v-model="form.notes" rows="4" class="rounded-md border bg-transparent p-2" maxlength="2000" /></label><InputError :message="form.errors.launch || form.errors.cutoff_at" class="mt-2" /><div class="mt-4 flex justify-end"><Button type="submit" :disabled="form.processing">Guardar preparación</Button></div></form>
        </div>

        <section v-if="configuration.status !== 'active'" class="max-w-2xl rounded-xl border border-amber-300 bg-amber-50 p-5 dark:bg-amber-950/20"><h2 class="font-semibold">Activar inicio oficial</h2><p class="mt-1 text-sm">Solo será posible cuando todos los requisitos estén listos. La acción es irreversible desde la interfaz.</p><form class="mt-4 grid gap-3" @submit.prevent="activation.post('/launch/activate', { preserveScroll: true })"><Input v-model="activation.current_password" type="password" required autocomplete="current-password" placeholder="Contraseña actual" /><Input v-model="activation.confirmation" required placeholder="Escribe INICIAR DONPATY" /><InputError :message="activation.errors.launch || activation.errors.current_password || activation.errors.confirmation" /><Button type="submit" variant="destructive" :disabled="activation.processing">Activar fecha de corte</Button></form></section>
    </main>
</template>
