<script setup lang="ts">
import { nextTick, onMounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

type Step = { title: string; description: string; target?: string };
const props = defineProps<{ storageKey: string; title: string; steps: Step[] }>();
const open = ref(false);
const currentStep = ref(0);
let highlighted: Element | null = null;

const clearHighlight = () => { highlighted?.classList.remove('ring-2', 'ring-primary', 'ring-offset-4'); highlighted = null; };
const focusStep = async () => {
    clearHighlight();
    const selector = props.steps[currentStep.value]?.target;
    if (!selector) return;
    await nextTick();
    highlighted = document.querySelector(selector);
    highlighted?.classList.add('ring-2', 'ring-primary', 'ring-offset-4');
    highlighted?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};
const finish = () => { localStorage.setItem(props.storageKey, 'completed'); clearHighlight(); open.value = false; };
const next = () => currentStep.value === props.steps.length - 1 ? finish() : currentStep.value++;
const previous = () => currentStep.value--;

onMounted(() => { open.value = localStorage.getItem(props.storageKey) !== 'completed'; });
watch([open, currentStep], ([isOpen]) => { if (isOpen) void focusStep(); else clearHighlight(); });
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent :show-close-button="false">
            <DialogHeader><DialogTitle>{{ title }}</DialogTitle><DialogDescription>Paso {{ currentStep + 1 }} de {{ steps.length }}</DialogDescription></DialogHeader>
            <section class="space-y-2"><h2 class="font-semibold">{{ steps[currentStep].title }}</h2><p class="text-sm text-muted-foreground">{{ steps[currentStep].description }}</p></section>
            <DialogFooter class="flex-row justify-between sm:justify-between"><Button variant="ghost" @click="finish">Omitir guía</Button><div class="flex gap-2"><Button v-if="currentStep > 0" variant="outline" @click="previous">Anterior</Button><Button @click="next">{{ currentStep === steps.length - 1 ? 'Entendido' : 'Siguiente' }}</Button></div></DialogFooter>
        </DialogContent>
    </Dialog>
</template>
