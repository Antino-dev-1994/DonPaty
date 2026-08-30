<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search, UserRound } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type Person = {
    id: string;
    name: string;
    document: string;
    email: string | null;
    phone: string | null;
    is_active: boolean;
    has_user: boolean;
    classifications: { value: string; label: string }[];
};

type PaginationLink = { url: string | null; label: string; active: boolean };

const props = defineProps<{
    people: { data: Person[]; links: PaginationLink[]; from: number | null; to: number | null; total: number };
    filters: { search: string };
    canManage: boolean;
}>();

const search = ref(props.filters.search);
const applySearch = () => router.get('/people', { search: search.value }, { preserveState: true, replace: true });
</script>

<template>
    <Head title="Personas" />

    <main class="flex flex-1 flex-col gap-5 p-4 md:p-6">
        <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-semibold">Personas</h1>
                <p class="text-sm text-muted-foreground">Habitantes, empleados, clientes y propietarios en un solo registro.</p>
            </div>
            <Button v-if="canManage" as-child>
                <Link href="/people/create"><Plus /> Nueva persona</Link>
            </Button>
        </header>

        <form class="flex max-w-xl gap-2" @submit.prevent="applySearch">
            <Input v-model="search" placeholder="Buscar por nombre, documento, correo o teléfono" />
            <Button type="submit" variant="outline"><Search /> Buscar</Button>
        </form>

        <section class="overflow-hidden rounded-xl border bg-card">
            <div v-if="people.data.length === 0" class="grid place-items-center gap-2 p-12 text-center">
                <UserRound class="size-8 text-muted-foreground" />
                <p class="font-medium">No se encontraron personas</p>
                <p class="text-sm text-muted-foreground">Crea la primera o cambia el término de búsqueda.</p>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/40 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Persona</th>
                            <th class="px-4 py-3 font-medium">Clasificación</th>
                            <th class="px-4 py-3 font-medium">Contacto</th>
                            <th class="px-4 py-3 font-medium">Estado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="person in people.data" :key="person.id">
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ person.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ person.document || 'Sin documento' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="item in person.classifications" :key="item.value" class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-900 dark:bg-amber-900/40 dark:text-amber-100">{{ item.label }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                <p>{{ person.email || 'Sin correo' }}</p>
                                <p class="text-xs">{{ person.phone || 'Sin teléfono' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="person.is_active ? 'text-emerald-700' : 'text-muted-foreground'">{{ person.is_active ? 'Activa' : 'Inactiva' }}</span>
                                <p v-if="person.has_user" class="text-xs text-muted-foreground">Con acceso al sistema</p>
                            </td>
                            <td class="px-4 py-3 text-right"><Link :href="`/people/${person.id}/edit`" class="font-medium text-amber-700 hover:underline">Editar</Link></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <nav v-if="people.links.length > 3" class="flex flex-wrap gap-1">
            <Link v-for="link in people.links" :key="link.label" :href="link.url || '#'" :class="['rounded-md border px-3 py-1.5 text-sm', link.active && 'bg-primary text-primary-foreground', !link.url && 'pointer-events-none opacity-50']" v-html="link.label" />
        </nav>
    </main>
</template>
