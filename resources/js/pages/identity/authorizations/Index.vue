<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
type Authorization = { id: string; operation_type: string; reason: string; status: string; status_label: string; requester: string; approver: string | null; decision_notes: string | null; expires_at: string | null; created_at: string; can_decide: boolean };
defineProps<{ authorizations: { data: Authorization[] } }>();
const decide = (authorization: Authorization, decision: 'approved' | 'rejected') => {
    const notes = window.prompt(decision === 'approved' ? 'Notas de aprobación (opcional)' : 'Motivo del rechazo');
    if (notes === null) return;
    router.post(`/authorizations/${authorization.id}/decision`, { decision, notes }, { preserveScroll: true });
};
</script>
<template><Head title="Autorizaciones" /><main class="flex flex-1 flex-col gap-5 p-4 md:p-6"><header><h1 class="text-2xl font-semibold">Autorizaciones</h1><p class="text-sm text-muted-foreground">Excepciones de un solo uso con solicitante, autorizador, motivo y vigencia.</p></header><section class="space-y-3"><article v-for="item in authorizations.data" :key="item.id" class="rounded-xl border bg-card p-5"><div class="flex flex-col justify-between gap-3 sm:flex-row"><div><div class="flex flex-wrap items-center gap-2"><h2 class="font-semibold">{{ item.operation_type }}</h2><span class="rounded-full bg-muted px-2 py-0.5 text-xs">{{ item.status_label }}</span></div><p class="mt-2 text-sm">{{ item.reason }}</p><p class="mt-2 text-xs text-muted-foreground">Solicita {{ item.requester }} · {{ item.created_at }}<span v-if="item.expires_at"> · vence {{ item.expires_at }}</span></p><p v-if="item.approver" class="text-xs text-muted-foreground">Decide {{ item.approver }}<span v-if="item.decision_notes">: {{ item.decision_notes }}</span></p></div><div v-if="item.can_decide" class="flex shrink-0 gap-2"><Button size="sm" variant="outline" @click="decide(item, 'rejected')">Rechazar</Button><Button size="sm" @click="decide(item, 'approved')">Aprobar</Button></div></div></article><p v-if="authorizations.data.length === 0" class="rounded-xl border p-10 text-center text-sm text-muted-foreground">No hay solicitudes de autorización.</p></section></main></template>
