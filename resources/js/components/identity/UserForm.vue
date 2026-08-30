<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Person = { id: string; name: string; email: string | null };
type Role = { id: string; name: string; label: string; description: string | null };
type Initial = { person_id: string; name: string; email: string; roles: string[] };

const props = defineProps<{
    submitUrl: string;
    method: 'post' | 'put';
    people: Person[];
    roles: Role[];
    initial?: Initial;
    selectedPersonId?: string | null;
}>();

const form = useForm({
    person_id: props.initial?.person_id ?? props.selectedPersonId ?? '',
    name: props.initial?.name ?? '',
    email: props.initial?.email ?? '',
    password: '',
    password_confirmation: '',
    roles: props.initial?.roles ?? ([] as string[]),
});

const selectedPerson = computed(() => props.people.find((person) => person.id === form.person_id));
watch(selectedPerson, (person) => {
    if (!props.initial && person) {
        form.name = person.name;
        form.email = person.email ?? '';
    }
}, { immediate: true });

const submit = () => form.submit(props.method, props.submitUrl, { preserveScroll: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="grid gap-2 md:col-span-2">
                <Label for="person_id">Persona vinculada</Label>
                <select id="person_id" v-model="form.person_id" required class="h-9 rounded-md border bg-transparent px-3 text-sm">
                    <option value="" disabled>Selecciona una persona</option>
                    <option v-for="person in people" :key="person.id" :value="person.id">{{ person.name }}</option>
                </select>
                <InputError :message="form.errors.person_id" />
            </div>
            <div class="grid gap-2">
                <Label for="name">Nombre visible</Label>
                <Input id="name" v-model="form.name" required />
                <InputError :message="form.errors.name" />
            </div>
            <div class="grid gap-2">
                <Label for="email">Correo de acceso</Label>
                <Input id="email" v-model="form.email" type="email" required />
                <InputError :message="form.errors.email" />
            </div>
            <div class="grid gap-2">
                <Label for="password">{{ initial ? 'Nueva contraseña (opcional)' : 'Contraseña' }}</Label>
                <Input id="password" v-model="form.password" type="password" :required="!initial" autocomplete="new-password" />
                <InputError :message="form.errors.password" />
            </div>
            <div class="grid gap-2">
                <Label for="password_confirmation">Confirmar contraseña</Label>
                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" :required="!initial" autocomplete="new-password" />
            </div>
            <fieldset class="grid gap-3 md:col-span-2">
                <legend class="text-sm font-medium">Roles</legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    <label v-for="role in roles" :key="role.id" class="flex cursor-pointer gap-3 rounded-lg border p-3">
                        <input v-model="form.roles" type="checkbox" :value="role.id" class="mt-1 size-4 accent-amber-700" />
                        <span><span class="block text-sm font-medium">{{ role.label }}</span><span class="block text-xs text-muted-foreground">{{ role.description }}</span></span>
                    </label>
                </div>
                <InputError :message="form.errors.roles" />
            </fieldset>
        </div>
        <div class="flex justify-end gap-3 border-t pt-5">
            <Button variant="outline" type="button" as-child><a href="/users">Cancelar</a></Button>
            <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Guardando...' : 'Guardar acceso' }}</Button>
        </div>
    </form>
</template>
