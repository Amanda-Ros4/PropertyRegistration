<script setup>
import { cn } from "@/lib/utils";

const props = defineProps({
  class: {
    type: [Boolean, null, String, Object, Array],
    required: false,
    skipCheck: true,
  },
});
</script>
<template>
  <thead :class="cn(
    // Fixa o cabeçalho no topo
    'sticky top-0 z-10',

    // COR FIXA: Escurece a linha no modo Light (gray-100) e mantém no Dark (gray-900)
    'bg-gray-100 dark:bg-gray-900',

    // Bordas que combinam com o tema
    'border-b border-gray-200 dark:border-gray-800',

    // FORÇA A REMOÇÃO DO HOVER E ZEBRA: O sinal '!' (important) garante que as 
    // classes do TableRow não consigam sobrescrever esta regra no cabeçalho
    '[&_tr]:!bg-transparent [&_tr]:hover:!bg-transparent',

    props.class
  )
    ">
    <slot />
  </thead>
</template>
