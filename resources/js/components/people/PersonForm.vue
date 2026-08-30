<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Option = { value: string; label: string };
type PersonFormData = {
    name: string;
    document_type: string;
    document_number: string;
    email: string;
    phone: string;
    notes: string;
    is_active: boolean;
    classifications: string[];
};

const props = defineProps<{
    submitUrl: string;
    method: 'post' | 'put';
    classificationOptions: Option[];
    initial?: Partial<PersonFormData>;
}>();

const form = useForm<PersonFormData>({
    name: props.initial?.name ?? '',
    document_type: props.initial?.document_type ?? '',
    document_number: props.initial?.document_number ?? '',
    email: props.initial?.email ?? '',
    phone: props.initial?.phone ?? '',
    notes: props.initial?.notes ?? '',
    is_active: props.initial?.is_active ?? true,
    classifications: props.initial?.classifications ?? [],
});

const submit = () => {
    form.submit(props.method, props.submitUrl, { preserveScroll: true });
};
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="grid gap-2 md:col-span-2">
                <Label for="name">Nombre completo</Label>
                <Input id="name" v-model="form.name" required maxlength="160" />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="document_type">Tipo de documento</Label>
                <Input id="document_type" v-model="form.document_type" placeholder="CC, CE, NIT..." />
                <InputError :message="form.errors.document_type" />
            </div>

            <div class="grid gap-2">
                <Label for="document_number">Número de documento</Label>
                <Input id="document_number" v-model="form.document_number" />
                <InputError :message="form.errors.document_number" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Correo</Label>
                <Input id="email" v-model="form.email" type="email" />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Teléfono</Label>
                <Input id="phone" v-model="form.phone" />
                <InputError :message="form.errors.phone" />
            </div>

            <fieldset class="grid gap-3 md:col-span-2">
                <legend class="text-sm font-medium">Clasificaciones</legend>
                <div class="flex flex-wrap gap-3">
                    <label
                        v-for="option in classificationOptions"
                        :key="option.value"
                        class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm"
                    >
                        <input
                            v-model="form.classifications"
                            type="checkbox"
                            :value="option.value"
                            class="size-4 accent-amber-700"
                        />
                        {{ option.label }}
                    </label>
                </div>
                <InputError :message="form.errors.classifications" />
            </fieldset>

            <div class="grid gap-2 md:col-span-2">
                <Label for="notes">Notas</Label>
                <textarea
                    id="notes"
                    v-model="form.notes"
                    rows="4"
                    class="rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                />
                <InputError :message="form.errors.notes" />
            </div>

            <label class="flex items-center gap-2 text-sm md:col-span-2">
                <input v-model="form.is_active" type="checkbox" class="size-4 accent-amber-700" />
                Persona activa
            </label>
        </div>

        <div class="flex justify-end gap-3 border-t pt-5">
            <Button type="button" variant="outline" as-child>
                <a href="/people">Cancelar</a>
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar persona' }}
            </Button>
        </div>
    </form>
</template>
