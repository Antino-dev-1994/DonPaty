<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { Button } from '@/components/ui/button';

type User = { id: number; name: string; email: string; person: string | null; status: string; status_label: string; last_login_at: string | null; roles: string[] };
defineProps<{ users: User[] }>();
</script>

<template>
    <Head title="Usuarios" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header class="flex items-center justify-between gap-4"><div><h1 class="text-2xl font-semibold">Usuarios</h1><p class="text-sm text-muted-foreground">Accesos, roles, bloqueos y sesiones activas.</p></div><Button as-child><Link href="/users/create"><Plus /> Nuevo usuario</Link></Button></header>
        <section class="overflow-x-auto rounded-xl border bg-card">
            <table class="w-full text-sm"><thead class="border-b bg-muted/40 text-left"><tr><th class="px-4 py-3">Usuario</th><th class="px-4 py-3">Roles</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Último acceso</th><th></th></tr></thead>
                <tbody class="divide-y"><tr v-for="user in users" :key="user.id"><td class="px-4 py-3"><p class="font-medium">{{ user.name }}</p><p class="text-xs text-muted-foreground">{{ user.email }}</p></td><td class="px-4 py-3">{{ user.roles.join(', ') }}</td><td class="px-4 py-3" :class="user.status === 'active' ? 'text-emerald-700' : 'text-destructive'">{{ user.status_label }}</td><td class="px-4 py-3 text-muted-foreground">{{ user.last_login_at || 'Nunca' }}</td><td class="px-4 py-3 text-right"><Link :href="`/users/${user.id}/edit`" class="font-medium text-amber-700 hover:underline">Administrar</Link></td></tr></tbody>
            </table>
        </section>
    </main>
</template>
