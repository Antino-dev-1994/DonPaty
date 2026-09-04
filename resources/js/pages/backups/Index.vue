<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

type Backup = { id: string; status: string; driver: string; size: number | null; sha256: string | null; verification_status: string | null; error_message: string | null; creator: string; created_at: string; verified_at: string | null; download_url: string | null; restore_command: string | null };
type PageLink = { url: string | null; label: string; active: boolean };
defineProps<{ backups: { data: Backup[]; links: PageLink[] } }>();
const create = () => router.post('/backups', {}, { preserveScroll: true });
const verify = (backup: Backup) => router.post(`/backups/${backup.id}/verify`, {}, { preserveScroll: true });
const size = (bytes: number | null) => bytes === null ? '—' : `${(bytes / 1024 / 1024).toFixed(2)} MB`;
</script>

<template>
    <Head title="Respaldos" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"><div><h1 class="text-2xl font-semibold">Respaldos</h1><p class="text-sm text-muted-foreground">Base de datos y adjuntos privados con manifiesto de integridad.</p></div><Button @click="create">Crear respaldo ahora</Button></header>
        <section class="space-y-3">
            <article v-for="backup in backups.data" :key="backup.id" class="rounded-xl border bg-card p-4">
                <div class="flex flex-col justify-between gap-3 lg:flex-row"><div><div class="flex flex-wrap items-center gap-2"><strong>{{ backup.created_at }}</strong><span class="rounded-full bg-muted px-2 py-0.5 text-xs">{{ backup.status }}</span><span class="rounded-full bg-muted px-2 py-0.5 text-xs">Integridad: {{ backup.verification_status || 'sin verificar' }}</span></div><p class="mt-1 text-sm text-muted-foreground">{{ backup.driver }} · {{ size(backup.size) }} · {{ backup.creator }}</p><p v-if="backup.sha256" class="mt-1 break-all font-mono text-xs text-muted-foreground">SHA-256 {{ backup.sha256 }}</p><p v-if="backup.error_message" class="mt-2 text-sm text-destructive">{{ backup.error_message }}</p></div><div class="flex shrink-0 flex-wrap gap-2"><Button v-if="backup.status === 'completed'" variant="outline" @click="verify(backup)">Verificar</Button><Button v-if="backup.download_url" variant="outline" as-child><a :href="backup.download_url">Descargar</a></Button></div></div>
                <div v-if="backup.restore_command" class="mt-4 rounded-lg border border-amber-300 bg-amber-50 p-3 dark:bg-amber-950/20"><p class="text-sm font-medium">Restauración controlada desde el servidor</p><code class="mt-1 block overflow-auto text-xs">{{ backup.restore_command }}</code><p class="mt-1 text-xs text-muted-foreground">El comando vuelve a verificar el respaldo, crea una copia de seguridad previa y activa mantenimiento.</p></div>
            </article>
            <p v-if="backups.data.length === 0" class="rounded-xl border p-10 text-center text-sm text-muted-foreground">Todavía no hay respaldos.</p>
        </section>
        <nav v-if="backups.links.length > 3" class="flex justify-center gap-1"><template v-for="link in backups.links" :key="link.label"><Link v-if="link.url" :href="link.url" class="rounded border px-3 py-1 text-sm" :class="link.active ? 'bg-primary text-primary-foreground' : ''" v-html="link.label" /><span v-else class="rounded border px-3 py-1 text-sm opacity-40" v-html="link.label" /></template></nav>
    </main>
</template>
