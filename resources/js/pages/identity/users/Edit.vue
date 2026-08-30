<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import UserForm from '@/components/identity/UserForm.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
type Person = { id: string; name: string; email: string | null }; type Role = { id: string; name: string; label: string; description: string | null };
type ManagedUser = { id: number; person_id: string; name: string; email: string; status: string; blocked_reason: string | null; roles: string[] };
type Session = { id: string; ip_address: string | null; user_agent: string | null; last_activity: string; is_current: boolean };
const props = defineProps<{ managedUser: ManagedUser; people: Person[]; roles: Role[]; sessions: Session[]; canBlock: boolean }>();
const reason = ref('');
const block = () => router.post(`/users/${props.managedUser.id}/block`, { reason: reason.value }, { preserveScroll: true });
const activate = () => router.post(`/users/${props.managedUser.id}/activate`, {}, { preserveScroll: true });
const revoke = (session: string) => router.delete(`/users/${props.managedUser.id}/sessions/${session}`, { preserveScroll: true });
</script>
<template><Head :title="`Acceso de ${managedUser.name}`" /><main class="flex flex-1 flex-col gap-6 p-4 md:p-6"><header><h1 class="text-2xl font-semibold">Acceso de {{ managedUser.name }}</h1><p class="text-sm text-muted-foreground">Credenciales, roles y dispositivos conectados.</p></header><section class="max-w-3xl rounded-xl border bg-card p-5"><UserForm :submit-url="`/users/${managedUser.id}`" method="put" :people="people" :roles="roles" :initial="managedUser" /></section>
<section class="max-w-3xl rounded-xl border bg-card p-5"><h2 class="font-semibold">Sesiones</h2><p class="mb-4 text-sm text-muted-foreground">Revoca un dispositivo sin cambiar la contraseña.</p><div class="divide-y"><div v-for="session in sessions" :key="session.id" class="flex items-center justify-between gap-3 py-3"><div class="min-w-0"><p class="truncate text-sm">{{ session.user_agent || 'Dispositivo desconocido' }}</p><p class="text-xs text-muted-foreground">{{ session.ip_address || 'Sin IP' }} · {{ session.last_activity }} <span v-if="session.is_current">· Esta sesión</span></p></div><Button size="sm" variant="outline" @click="revoke(session.id)">Revocar</Button></div><p v-if="sessions.length === 0" class="py-4 text-sm text-muted-foreground">No hay sesiones activas.</p></div></section>
<section v-if="canBlock" class="max-w-3xl rounded-xl border border-destructive/30 bg-card p-5"><h2 class="font-semibold">Estado del usuario</h2><template v-if="managedUser.status === 'active'"><p class="my-2 text-sm text-muted-foreground">Bloquear impide nuevos accesos y revoca todas las sesiones.</p><div class="flex gap-2"><Input v-model="reason" placeholder="Motivo obligatorio" /><Button variant="destructive" :disabled="reason.length < 5" @click="block">Bloquear</Button></div></template><template v-else><p class="my-2 text-sm text-muted-foreground">Motivo: {{ managedUser.blocked_reason }}</p><Button @click="activate">Reactivar usuario</Button></template></section></main></template>
