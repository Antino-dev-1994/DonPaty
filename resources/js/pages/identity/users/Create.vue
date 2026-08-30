<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import UserForm from '@/components/identity/UserForm.vue';
type Person = { id: string; name: string; email: string | null }; type Role = { id: string; name: string; label: string; description: string | null };
defineProps<{ people: Person[]; roles: Role[]; selectedPersonId?: string | null }>();
</script>
<template><Head title="Nuevo usuario" /><main class="flex flex-1 flex-col gap-5 p-4 md:p-6"><header><h1 class="text-2xl font-semibold">Nuevo usuario</h1><p class="text-sm text-muted-foreground">Vincula una persona y asigna únicamente los roles necesarios.</p></header><section class="max-w-3xl rounded-xl border bg-card p-5"><UserForm submit-url="/users" method="post" :people="people" :roles="roles" :selected-person-id="selectedPersonId" /></section></main></template>
