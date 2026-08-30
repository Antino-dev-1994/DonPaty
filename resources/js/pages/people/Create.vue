<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PersonForm from '@/components/people/PersonForm.vue';

defineProps<{ classificationOptions: { value: string; label: string }[] }>();
</script>

<template>
    <Head title="Nueva persona" />
    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header><h1 class="text-2xl font-semibold">Nueva persona</h1><p class="text-sm text-muted-foreground">Registra primero a la persona; el acceso al sistema es opcional.</p></header>
        <section class="max-w-3xl rounded-xl border bg-card p-5"><PersonForm submit-url="/people" method="post" :classification-options="classificationOptions" /></section>
    </main>
</template>
