<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PersonForm from '@/components/people/PersonForm.vue';

type Person = {
    id: string; name: string; document_type: string; document_number: string; email: string; phone: string;
    notes: string; is_active: boolean; classifications: string[];
    user: { id: number; status: string; roles: string[] } | null;
};

defineProps<{ person: Person; classificationOptions: { value: string; label: string }[] }>();
</script>

<template>
    <Head :title="`Editar ${person.name}`" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div><h1 class="text-2xl font-semibold">{{ person.name }}</h1><p class="text-sm text-muted-foreground">Información personal y clasificaciones vigentes.</p></div>
            <Link v-if="person.user" :href="`/users/${person.user.id}/edit`" class="text-sm font-medium text-amber-700 hover:underline">Administrar acceso</Link>
            <Link v-else href="/users/create" :data="{ person_id: person.id }" class="text-sm font-medium text-amber-700 hover:underline">Crear acceso</Link>
        </header>
        <section class="max-w-3xl rounded-xl border bg-card p-5"><PersonForm :submit-url="`/people/${person.id}`" method="put" :classification-options="classificationOptions" :initial="person" /></section>
    </main>
</template>
