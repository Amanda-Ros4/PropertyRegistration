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
  <tr
    data-slot="table-row"
    :class="
      cn(
        // Borda adaptável ao tema
        'border-b border-gray-200 dark:border-gray-800 transition-colors',
        
        // Efeito hover padrão para todas as linhas (Light / Dark)
        'hover:bg-gray-100 dark:hover:bg-gray-800/60',
        
        // Efeito Zebra: Cor fixa nas linhas pares (Light / Dark)
        'even:bg-gray-50 even:dark:bg-gray-800/30',
        
        // Garante que o hover continue funcionando por cima das linhas pares
        'even:hover:bg-gray-100 even:dark:hover:bg-gray-800/60',
        
        props.class,
      )
    "
  >
    <slot />
  </tr>
</template>