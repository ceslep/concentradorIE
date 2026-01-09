<!-- 
PAYLOADFORMSECTION.SVELTE

DESCRIPCIÓN:
Contenedor decorativo y funcional para el formulario de parámetros. Proporciona una interfaz colapsable con efectos visuales premium y estados de carga dedicados.

USO:
<PayloadFormSection show={showPayloadForm} loading={$loading} /> en App.svelte.

DEPENDENCIAS:
- Componente hijo: PayloadForm.svelte.
- Store: theme (themeStore.ts).

PROPS/EMIT:
- Prop: `show` → boolean → Controla la visibilidad (colapsado/expandido).
- Prop: `loading` → boolean → Muestra un overlay de carga sobre el formulario.

RELACIONES:
- Llamado por: App.svelte.
- Envuelve a: PayloadForm.svelte.

NOTAS DE DESARROLLO:
- Utiliza 'animate-pulse-slow' y 'animate-gradient-x' para fondos vivos y dinámicos.
- Implementa una máscara SVG/CSS para bordes animados de alta precisión.

ESTILOS:
- 'glass-effect-premium' para una apariencia de cristal traslúcido.
- Bordes rotativos decorativos (`rotateBorder`).
-->

<script lang="ts">
  import PayloadForm from "./PayloadForm.svelte";
  import { theme } from "../../../themeStore";

  export let show: boolean = true;
  export let loading: boolean = false;
</script>

{#if show}
  <section
    class="glass-effect-premium rounded-2xl p-4 sm:p-5 shadow-premium-2xl relative overflow-hidden border-2 cyber-border-section animate-fade-in"
    style="border-color: {$theme === 'dark'
      ? 'rgba(99, 102, 241, 0.3)'
      : 'rgba(99, 102, 241, 0.2)'};"
  >
    <!-- Multi-layer animated gradient overlays -->
    <div
      class="absolute inset-0 opacity-40 dark:opacity-30 pointer-events-none"
    >
      <div
        class="absolute top-0 right-0 w-48 h-48 sm:w-64 sm:h-64 bg-gradient-to-bl from-indigo-500/30 via-purple-500/20 to-transparent blur-3xl animate-pulse-slow"
      ></div>
      <div
        class="absolute bottom-0 left-0 w-48 h-48 sm:w-64 sm:h-64 bg-gradient-to-tr from-pink-500/30 via-purple-500/20 to-transparent blur-3xl animate-pulse-slow"
        style="animation-delay: 1.5s;"
      ></div>
      <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 sm:w-48 sm:h-48 bg-gradient-to-br from-indigo-500/20 via-transparent to-purple-500/20 blur-2xl animate-pulse-slow"
        style="animation-delay: 0.75s;"
      ></div>
    </div>

    <!-- Animated gradient line at top -->
    <div
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-indigo-500/80 to-transparent animate-gradient-x"
    ></div>

    <!-- Corner accents -->
    <div
      class="absolute top-0 left-0 w-16 h-16 opacity-20 dark:opacity-30 pointer-events-none"
    >
      <div
        class="absolute top-0 left-0 w-full h-full border-t-2 border-l-2 border-indigo-500/50 rounded-tl-2xl"
      ></div>
    </div>
    <div
      class="absolute bottom-0 right-0 w-16 h-16 opacity-20 dark:opacity-30 pointer-events-none"
    >
      <div
        class="absolute bottom-0 right-0 w-full h-full border-b-2 border-r-2 border-purple-500/50 rounded-br-2xl"
      ></div>
    </div>

    <!-- Loading overlay -->
    {#if loading}
      <div
        class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-md rounded-2xl z-20 flex items-center justify-center"
      >
        <div class="relative">
          <div
            class="w-12 h-12 border-4 border-indigo-200 dark:border-indigo-900/50 border-t-indigo-600 dark:border-t-indigo-400 rounded-full animate-spin shadow-lg shadow-indigo-500/20"
          ></div>
          <div
            class="absolute inset-0 w-12 h-12 border-4 border-transparent border-t-purple-500 dark:border-t-purple-400 rounded-full animate-spin opacity-60"
            style="animation-duration: 1.5s; animation-direction: reverse;"
          ></div>
        </div>
      </div>
    {/if}

    <div class="relative z-10">
      <PayloadForm disabled={loading} />
    </div>
  </section>
{/if}

<style>
  /* Advanced glassmorphism */
  .glass-effect-premium {
    background: linear-gradient(
      135deg,
      rgba(255, 255, 255, 0.85) 0%,
      rgba(255, 255, 255, 0.75) 100%
    );
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
  }

  :global(.dark) .glass-effect-premium {
    background: linear-gradient(
      135deg,
      rgba(17, 24, 39, 0.85) 0%,
      rgba(31, 41, 55, 0.75) 100%
    );
  }

  /* Premium shadow */
  .shadow-premium-2xl {
    box-shadow:
      0 25px 50px -12px rgba(99, 102, 241, 0.15),
      0 10px 20px -5px rgba(99, 102, 241, 0.1),
      0 0 0 1px rgba(99, 102, 241, 0.05);
  }

  :global(.dark) .shadow-premium-2xl {
    box-shadow:
      0 25px 50px -12px rgba(99, 102, 241, 0.25),
      0 10px 20px -5px rgba(99, 102, 241, 0.15),
      0 0 0 1px rgba(99, 102, 241, 0.1);
  }

  /* Cyber border animation */
  .cyber-border-section {
    position: relative;
  }

  .cyber-border-section::before {
    content: "";
    position: absolute;
    inset: -1px;
    border-radius: 1rem;
    padding: 2px;
    background: linear-gradient(
      135deg,
      rgba(99, 102, 241, 0.5),
      rgba(168, 85, 247, 0.4),
      rgba(236, 72, 153, 0.4),
      rgba(99, 102, 241, 0.5)
    );
    -webkit-mask:
      linear-gradient(#fff 0 0) content-box,
      linear-gradient(#fff 0 0);
    mask:
      linear-gradient(#fff 0 0) content-box,
      linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0.7;
    pointer-events: none;
    animation: rotateBorder 10s linear infinite;
  }

  @keyframes rotateBorder {
    0% {
      filter: hue-rotate(0deg);
    }
    100% {
      filter: hue-rotate(360deg);
    }
  }

  /* Fade in animation */
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .animate-fade-in {
    animation: fadeIn 0.6s ease-out;
  }

  /* Slower pulse for background gradients */
  @keyframes pulseSlow {
    0%,
    100% {
      opacity: 0.4;
    }
    50% {
      opacity: 0.6;
    }
  }

  .animate-pulse-slow {
    animation: pulseSlow 4s ease-in-out infinite;
  }

  :global(.dark) .animate-pulse-slow {
    animation: pulseSlow 4s ease-in-out infinite;
  }

  /* Gradient animation */
  @keyframes gradientX {
    0%,
    100% {
      opacity: 0.6;
      transform: scaleX(1);
    }
    50% {
      opacity: 1;
      transform: scaleX(1.1);
    }
  }

  .animate-gradient-x {
    animation: gradientX 3s ease-in-out infinite;
    transform-origin: center;
  }
</style>
