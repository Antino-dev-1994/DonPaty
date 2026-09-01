<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CircleDollarSign,
    ClipboardList,
    House,
    PackageOpen,
    Wheat,
} from '@lucide/vue';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel principal',
                href: dashboard(),
            },
        ],
    },
});

const modules = [
    {
        title: 'Inventario',
        description: 'Materias primas, productos y paquetes.',
        icon: PackageOpen,
    },
    {
        title: 'Producción',
        description: 'Recetas, masa, rendimiento y costos.',
        icon: Wheat,
    },
    {
        title: 'Pedidos y ventas',
        description: 'Demanda pendiente, cartera y caja.',
        icon: ClipboardList,
    },
    {
        title: 'Finanzas',
        description: 'Ingresos, gastos y resultado diario.',
        icon: CircleDollarSign,
    },
    {
        title: 'Hogar',
        description: 'Presupuestos, solicitudes y deudas.',
        icon: House,
    },
];

defineProps<{ costPeriodAlert: { message: string; canManage: boolean } | null }>();
</script>

<template>
    <Head title="Panel principal" />

    <main class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <section v-if="costPeriodAlert" class="flex flex-col justify-between gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950 sm:flex-row sm:items-center dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100">
            <p>{{ costPeriodAlert.message }}</p>
            <Link v-if="costPeriodAlert.canManage" href="/cost-periods/create" class="shrink-0 font-semibold underline">Abrir periodo</Link>
        </section>
        <section
            class="overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 via-orange-50 to-white p-6 dark:border-amber-900/50 dark:from-amber-950/40 dark:via-orange-950/20 dark:to-background"
        >
            <p
                class="mb-2 text-xs font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-300"
            >
                DonPaty
            </p>
            <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
                Administración de la panadería y el hogar
            </h1>
            <p class="mt-3 max-w-2xl text-sm text-muted-foreground md:text-base">
                La base técnica está activa. Los módulos se habilitarán por
                entregas verificables, conservando inventario, dinero y costos
                en movimientos auditables.
            </p>
        </section>

        <section>
            <div class="mb-3 flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold">Módulos planificados</h2>
                    <p class="text-sm text-muted-foreground">
                        Se activarán según el checklist de implementación.
                    </p>
                </div>
                <span
                    class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-200"
                >
                    Entrega 1 completada
                </span>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <article
                    v-for="module in modules"
                    :key="module.title"
                    class="rounded-xl border bg-card p-4 shadow-xs"
                >
                    <component
                        :is="module.icon"
                        class="mb-4 size-5 text-amber-700 dark:text-amber-300"
                    />
                    <h3 class="text-sm font-semibold">{{ module.title }}</h3>
                    <p class="mt-1 text-xs leading-5 text-muted-foreground">
                        {{ module.description }}
                    </p>
                </article>
            </div>
        </section>
    </main>
</template>
