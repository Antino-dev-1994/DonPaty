<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Paperclip, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type AttachmentItem = {
    id: string;
    name: string;
    mime_type: string;
    size: number;
    sha256: string;
    uploaded_by: string;
    created_at: string;
    download_url: string;
    can_delete: boolean;
};

type AttachmentCollection = {
    resource_type: string;
    resource_id: string;
    can_upload: boolean;
    items: AttachmentItem[];
};

const props = defineProps<{ attachments: AttachmentCollection }>();
const form = useForm<{ file: File | null }>({ file: null });

const upload = () => {
    form.post(`/attachments/${props.attachments.resource_type}/${props.attachments.resource_id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const remove = (attachment: AttachmentItem) => {
    if (window.confirm(`¿Eliminar el adjunto “${attachment.name}”?`)) {
        router.delete(`/attachments/${attachment.id}`, { preserveScroll: true });
    }
};

const fileSize = (bytes: number) => {
    const units = ['B', 'KB', 'MB'];
    let value = bytes;
    let unit = 0;
    while (value >= 1024 && unit < units.length - 1) {
        value /= 1024;
        unit++;
    }

    return `${value.toFixed(unit === 0 ? 0 : 1)} ${units[unit]}`;
};
</script>

<template>
    <section class="rounded-xl border bg-card p-4">
        <div class="flex items-center gap-2">
            <Paperclip class="size-4" />
            <h2 class="font-semibold">Adjuntos privados</h2>
        </div>
        <p class="mt-1 text-xs text-muted-foreground">PDF, JPG o PNG. Máximo 10 MB por archivo.</p>

        <form v-if="attachments.can_upload" class="mt-3 flex flex-col gap-2 sm:flex-row" @submit.prevent="upload">
            <Input
                type="file"
                accept="application/pdf,image/jpeg,image/png"
                required
                @change="form.file = ($event.target as HTMLInputElement).files?.[0] ?? null"
            />
            <Button type="submit" variant="outline" :disabled="form.processing || !form.file">
                Adjuntar
            </Button>
        </form>
        <p v-if="form.errors.file" class="mt-2 text-sm text-destructive">{{ form.errors.file }}</p>

        <div v-if="attachments.items.length" class="mt-4 divide-y">
            <div v-for="item in attachments.items" :key="item.id" class="flex items-center justify-between gap-3 py-3">
                <div class="min-w-0 text-sm">
                    <a :href="item.download_url" class="block truncate font-medium underline-offset-4 hover:underline">
                        {{ item.name }}
                    </a>
                    <p class="text-xs text-muted-foreground">
                        {{ fileSize(item.size) }} · {{ item.uploaded_by }} · {{ item.created_at }}
                    </p>
                </div>
                <Button v-if="item.can_delete" type="button" size="icon" variant="ghost" @click="remove(item)">
                    <Trash2 class="size-4" />
                    <span class="sr-only">Eliminar {{ item.name }}</span>
                </Button>
            </div>
        </div>
        <p v-else class="mt-4 text-sm text-muted-foreground">Este documento no tiene adjuntos.</p>
    </section>
</template>
