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
  <thead
    :class="
      cn(
        // Fixa o cabeçalho no topo
        'sticky top-0 z-10',
        
        // Cores exatas padronizadas
        'bg-gray-50 dark:bg-gray-950/50',
        
        // Bordas exatas padronizadas
        'border-b border-gray-100 dark:border-gray-800',
        
        // Mantém a regra que remove o hover/zebra das linhas do cabeçalho
        '[&_tr]:!bg-transparent [&_tr]:hover:!bg-transparent',
        
        props.class
      )
    "
  >
    <slot />
  </thead>
</template>