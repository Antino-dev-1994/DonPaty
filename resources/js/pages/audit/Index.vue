<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Authorization = { id: string; operation_type: string; status: string; reason: string; requester: string | null; approver: string | null; url: string };
type Event = { id: string; actor: string; action: string; resource_type: string; resource_id: string; ip_address: string | null; correlation_id: string | null; before_data: unknown; after_data: unknown; authorization: Authorization | null; created_at: string };
type Filters = { search: string; actor_user_id: number | null; action: string; resource_id: string; date_from: string | null; date_to: string | null; authorization: string };
type PageLink = { url: string | null; label: string; active: boolean };

const props = defineProps<{ events: { data: Event[]; links: PageLink[] }; filters: Filters; users: { id: number; name: string }[] }>();
const form = reactive({ ...props.filters });
const applyFilters = () => router.get('/audit', form, { preserveState: true, replace: true });
const clearFilters = () => router.get('/audit');
</script>

<template>
    <Head title="Auditoría" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header><h1 class="text-2xl font-semibold">Auditoría</h1><p class="text-sm text-muted-foreground">Cambios y accesos sensibles con actor, documento, fecha y autorización relacionada.</p></header>

        <form class="grid gap-3 rounded-xl border bg-card p-4 md:grid-cols-4" @submit.prevent="applyFilters">
            <Input v-model="form.search" placeholder="Búsqueda general" />
            <select v-model="form.actor_user_id" class="h-9 rounded-md border bg-transparent px-3 text-sm"><option :value="null">Todos los usuarios</option><option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option></select>
            <Input v-model="form.action" placeholder="Acción, ej. attachment" />
            <Input v-model="form.resource_id" placeholder="ID de documento" />
            <Input v-model="form.date_from" type="date" aria-label="Fecha inicial" />
            <Input v-model="form.date_to" type="date" aria-label="Fecha final" />
            <select v-model="form.authorization" class="h-9 rounded-md border bg-transparent px-3 text-sm"><option value="any">Con o sin autorización</option><option value="with">Solo con autorización</option><option value="without">Sin autorización</option></select>
            <div class="flex gap-2"><Button type="submit" variant="outline">Filtrar</Button><Button type="button" variant="ghost" @click="clearFilters">Limpiar</Button></div>
        </form>

        <section class="space-y-2">
            <details v-for="event in events.data" :key="event.id" class="rounded-lg border bg-card p-4">
                <summary class="cursor-pointer list-none"><div class="flex flex-col justify-between gap-1 sm:flex-row"><div><span class="font-medium">{{ event.action }}</span><span class="ml-2 text-sm text-muted-foreground">{{ event.resource_type }} #{{ event.resource_id }}</span></div><span class="text-xs text-muted-foreground">{{ event.created_at }}</span></div><p class="mt-1 text-xs text-muted-foreground">{{ event.actor }} · {{ event.ip_address || 'sin IP' }} · {{ event.correlation_id }}</p></summary>
                <div v-if="event.authorization" class="mt-4 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm dark:bg-amber-950/20"><div class="flex justify-between gap-3"><strong>Excepción autorizada: {{ event.authorization.operation_type }}</strong><Link :href="event.authorization.url" class="underline">Ver autorizaciones</Link></div><p>{{ event.authorization.reason }}</p><p class="text-xs text-muted-foreground">{{ event.authorization.status }} · solicita {{ event.authorization.requester || 'sistema' }}<span v-if="event.authorization.approver"> · decide {{ event.authorization.approver }}</span></p></div>
                <div class="mt-4 grid gap-3 border-t pt-3 md:grid-cols-2"><div><h3 class="mb-1 text-xs font-medium">Antes</h3><pre class="overflow-auto rounded bg-muted p-3 text-xs">{{ JSON.stringify(event.before_data, null, 2) }}</pre></div><div><h3 class="mb-1 text-xs font-medium">Después</h3><pre class="overflow-auto rounded bg-muted p-3 text-xs">{{ JSON.stringify(event.after_data, null, 2) }}</pre></div></div>
            </details>
            <p v-if="events.data.length === 0" class="rounded-xl border p-10 text-center text-sm text-muted-foreground">No hay eventos para mostrar.</p>
        </section>

        <nav v-if="events.links.length > 3" class="flex flex-wrap justify-center gap-1"><template v-for="link in events.links" :key="link.label"><Link v-if="link.url" :href="link.url" preserve-scroll class="rounded border px-3 py-1 text-sm" :class="link.active ? 'bg-primary text-primary-foreground' : ''" v-html="link.label" /><span v-else class="rounded border px-3 py-1 text-sm opacity-40" v-html="link.label" /></template></nav>
    </main>
</template>
