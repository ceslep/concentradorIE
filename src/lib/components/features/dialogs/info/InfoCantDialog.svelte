<script lang="ts">
  import { createEventDispatcher, onMount } from "svelte";
  import { fade, scale } from "svelte/transition";
  import { GET_INFOCANT_ENDPOINT } from "../../../../../constants";
  import { theme } from "../../../../themeStore";
  import Skeleton from "../../../shared/Skeleton.svelte";
  import type { InfoCantData } from "../../../../types";
  import { payload } from "../../../../storeConcentrador";
  import InfoCantCharts from "./InfoCantCharts.svelte";

  export let showDialog: boolean = false;

  let data: InfoCantData[] = [];
  let showCharts: boolean = false;
  let processedData: (InfoCantData & {
    isNivelSummary?: boolean;
    isSedeTotal?: boolean;
    isGrandTotal?: boolean;
    nivelDisplay?: string;
  })[] = [];
  let loading: boolean = false;
  let error: string | null = null;

  const dispatch = createEventDispatcher();

  async function fetchInfoCant() {
    loading = true;
    error = null;
    try {
      const response = await fetch(GET_INFOCANT_ENDPOINT, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ year: $payload.year }),
      });
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      data = await response.json();
      processData(data);
    } catch (e: any) {
      error = e.message;
    } finally {
      loading = false;
    }
  }

  function processData(rawData: InfoCantData[]) {
    const groupedBySede: { [key: string]: InfoCantData[] } = {};
    rawData.forEach((item) => {
      if (!groupedBySede[item.sede]) {
        groupedBySede[item.sede] = [];
      }
      groupedBySede[item.sede].push(item);
    });

    const tempProcessedData: (InfoCantData & {
      isNivelSummary?: boolean;
      isSedeTotal?: boolean;
      isGrandTotal?: boolean;
      nivelDisplay?: string;
    })[] = [];
    let grandTotalStudents = 0;

    for (const sede in groupedBySede) {
      const sedeData = groupedBySede[sede];
      const groupedByNivel: { [key: number]: InfoCantData[] } = {};

      sedeData.forEach((item) => {
        tempProcessedData.push(item);
        if (!groupedByNivel[item.nivel]) {
          groupedByNivel[item.nivel] = [];
        }
        groupedByNivel[item.nivel].push(item);
      });

      let sedeTotal = 0;

      for (const nivel in groupedByNivel) {
        const nivelData = groupedByNivel[nivel];
        const nivelTotal = nivelData.reduce(
          (sum, item) => sum + item.total_estudiantes,
          0,
        );
        sedeTotal += nivelTotal;

        tempProcessedData.push({
          sede: sede,
          nivel: parseInt(nivel),
          numero: 0,
          total_estudiantes: nivelTotal,
          isNivelSummary: true,
          nivelDisplay: `Total Nivel ${nivel}`,
        });
      }

      tempProcessedData.push({
        sede: sede,
        nivel: 0,
        numero: 0,
        total_estudiantes: sedeTotal,
        isSedeTotal: true,
        nivelDisplay: "Total Sede",
      });
      grandTotalStudents += sedeTotal;
    }

    tempProcessedData.push({
      sede: "TOTAL GENERAL",
      nivel: 0,
      numero: 0,
      total_estudiantes: grandTotalStudents,
      isGrandTotal: true,
      nivelDisplay: "",
    });

    processedData = tempProcessedData;
  }

  onMount(() => {
    if (showDialog) {
      fetchInfoCant();
    }
  });

  $: if (showDialog) {
    fetchInfoCant();
  }

  function closeDialog() {
    showDialog = false;
    dispatch("close");
  }
</script>

{#if showDialog}
  <button
    class="dialog-backdrop"
    on:click={closeDialog}
    on:keydown={(e) => {
      if (e.key === "Escape") closeDialog();
    }}
    transition:fade={{ duration: 200 }}
  >
    <div
      class="dialog-content glass-panel {$theme === 'dark'
        ? 'dark-glass'
        : 'light-glass'}"
      on:click|stopPropagation
      on:keydown|stopPropagation={() => {}}
      role="dialog"
      aria-modal="true"
      tabindex="-1"
      transition:scale={{ duration: 300, start: 0.95, opacity: 0 }}
    >
      <!-- Header -->
      <div class="dialog-header">
        <div class="header-content">
          <div class="icon-wrapper">
            <span class="material-symbols-rounded">analytics</span>
          </div>
          <div>
            <h2 class="dialog-title">Información de Cantidades</h2>
            <p class="dialog-subtitle">Resumen detallado por sede y nivel</p>
          </div>
        </div>
        <div class="header-actions">
          <button
            class="action-button"
            on:click={() => (showCharts = !showCharts)}
            title={showCharts ? "Ver Tabla" : "Ver Gráficos"}
          >
            <span class="material-symbols-rounded">
              {showCharts ? "table_chart" : "bar_chart"}
            </span>
          </button>
          <button class="close-button" on:click={closeDialog}>
            <span class="material-symbols-rounded">close</span>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="dialog-body custom-scrollbar">
        {#if loading}
          <div class="p-6">
            <Skeleton rows={6} columns={4} theme={$theme} />
          </div>
        {:else if error}
          <div class="error-container">
            <span class="material-symbols-rounded error-icon">error</span>
            <p class="error-text">Error: {error}</p>
          </div>
        {:else if processedData.length > 0}
          {#if showCharts}
            <InfoCantCharts {data} />
          {:else}
            <div class="table-container">
              <table class="modern-table">
                <thead>
                  <tr>
                    <th>Sede</th>
                    <th>Nivel</th>
                    <th class="text-center">Número</th>
                    <th class="text-right">Estudiantes</th>
                  </tr>
                </thead>
                <tbody>
                  {#each processedData as item, i}
                    <tr
                      class:grand-total={item.isGrandTotal}
                      class:sede-total={item.isSedeTotal}
                      class:nivel-summary={item.isNivelSummary}
                      class:regular-row={!item.isGrandTotal &&
                        !item.isSedeTotal &&
                        !item.isNivelSummary}
                    >
                      <td class="font-medium">
                        {#if item.isGrandTotal}
                          <span class="badge-grand">TOTAL GENERAL</span>
                        {:else if item.isSedeTotal}
                          <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 font-bold"
                          >
                            {item.sede}
                          </span>
                        {:else}
                          <span class="opacity-90">{item.sede}</span>
                        {/if}
                      </td>

                      <td>
                        {#if item.isSedeTotal}
                          <span class="badge-sede">Total Sede</span>
                        {:else if item.isNivelSummary}
                          <span class="badge-nivel">{item.nivelDisplay}</span>
                        {:else if !item.isGrandTotal}
                          <span class="font-mono text-sm opacity-80"
                            >Nivel {item.nivel}</span
                          >
                        {/if}
                      </td>

                      <td class="text-center">
                        {#if !item.isGrandTotal && !item.isSedeTotal && !item.isNivelSummary}
                          <span
                            class="font-mono bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-xs"
                          >
                            #{item.numero}
                          </span>
                        {/if}
                      </td>

                      <td class="text-right">
                        <span class="font-bold tabular-nums text-lg">
                          {item.total_estudiantes}
                        </span>
                      </td>
                    </tr>
                  {/each}
                </tbody>
              </table>
            </div>
          {/if}
        {:else}
          <div class="empty-state">
            <span class="material-symbols-rounded empty-icon">inbox</span>
            <p>No se encontraron datos disponibles.</p>
          </div>
        {/if}
      </div>

      <!-- Footer -->
      <div class="dialog-footer">
        <button class="btn-primary" on:click={closeDialog}> Entendido </button>
      </div>
    </div>
  </div>
{/if}

<style>
  @import url("https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap");

  /* Base Styles */
  :global(body) {
    --font-heading: "Outfit", sans-serif;
    --font-body: "Inter", sans-serif;
  }

  .dialog-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(8px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    padding: 1rem;
  }

  .dialog-content {
    width: 100%;
    max-width: 900px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    border-radius: 24px;
    overflow: hidden;
    box-shadow:
      0 20px 25px -5px rgba(0, 0, 0, 0.1),
      0 10px 10px -5px rgba(0, 0, 0, 0.04),
      0 0 0 1px rgba(255, 255, 255, 0.1);
  }

  /* Glassmorphism Themes */
  .glass-panel {
    transition: all 0.3s ease;
  }

  .light-glass {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.5);
  }

  .dark-glass {
    background: rgba(15, 23, 42, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.05);
  }

  /* Header */
  .dialog-header {
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }

  :global(.dark) .dialog-header {
    border-bottom-color: rgba(255, 255, 255, 0.05);
  }

  .header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 16px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  }

  .dialog-title {
    font-family: var(--font-heading);
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
  }

  :global(.dark) .dialog-title {
    color: #f8fafc;
  }

  .dialog-subtitle {
    font-family: var(--font-body);
    font-size: 0.875rem;
    color: #64748b;
    margin-top: 0.25rem;
  }

  :global(.dark) .dialog-subtitle {
    color: #94a3b8;
  }

  .close-button {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    transition: all 0.2s;
    background: transparent;
    border: none;
    cursor: pointer;
  }

  .close-button:hover {
    background: rgba(0, 0, 0, 0.05);
    color: #ef4444;
  }

  :global(.dark) .close-button:hover {
    background: rgba(255, 255, 255, 0.05);
  }

  .header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .action-button {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6366f1;
    transition: all 0.2s;
    background: rgba(99, 102, 241, 0.1);
    border: none;
    cursor: pointer;
  }

  .action-button:hover {
    background: rgba(99, 102, 241, 0.2);
    transform: translateY(-1px);
  }

  :global(.dark) .action-button {
    color: #818cf8;
    background: rgba(129, 140, 248, 0.1);
  }

  :global(.dark) .action-button:hover {
    background: rgba(129, 140, 248, 0.2);
  }

  /* Body */
  .dialog-body {
    flex: 1;
    overflow-y: auto;
    padding: 0;
  }

  /* Table Styles */
  .table-container {
    width: 100%;
  }

  .modern-table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--font-body);
  }

  .modern-table th {
    position: sticky;
    top: 0;
    background: rgba(248, 250, 252, 0.9);
    backdrop-filter: blur(4px);
    padding: 1rem 1.5rem;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    z-index: 10;
  }

  :global(.dark) .modern-table th {
    background: rgba(15, 23, 42, 0.9);
    color: #94a3b8;
    border-bottom-color: rgba(255, 255, 255, 0.05);
  }

  .modern-table td {
    padding: 1rem 1.5rem;
    color: #334155;
    border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    font-size: 0.95rem;
  }

  :global(.dark) .modern-table td {
    color: #cbd5e1;
    border-bottom-color: rgba(255, 255, 255, 0.03);
  }

  /* Row Variants */
  .regular-row:hover {
    background: rgba(0, 0, 0, 0.02);
  }

  :global(.dark) .regular-row:hover {
    background: rgba(255, 255, 255, 0.02);
  }

  .nivel-summary {
    background: linear-gradient(
      90deg,
      rgba(16, 185, 129, 0.05) 0%,
      transparent 100%
    );
  }

  .sede-total {
    background: linear-gradient(
      90deg,
      rgba(59, 130, 246, 0.08) 0%,
      transparent 100%
    );
  }

  .grand-total {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white !important;
  }

  .grand-total td {
    color: white !important;
    border-bottom: none;
    padding: 1.5rem;
  }

  /* Badges */
  .badge-grand {
    font-weight: 800;
    letter-spacing: 0.05em;
    font-size: 1.1rem;
  }

  .badge-sede {
    font-weight: 600;
    color: #3b82f6;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.05em;
  }

  :global(.dark) .badge-sede {
    color: #60a5fa;
  }

  .badge-nivel {
    font-weight: 600;
    color: #10b981;
    font-size: 0.9rem;
  }

  :global(.dark) .badge-nivel {
    color: #34d399;
  }

  /* Footer */
  .dialog-footer {
    padding: 1.5rem 2rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: flex-end;
  }

  :global(.dark) .dialog-footer {
    border-top-color: rgba(255, 255, 255, 0.05);
  }

  .btn-primary {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-family: var(--font-body);
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
  }

  .btn-primary:active {
    transform: translateY(0);
  }

  /* States */
  .error-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: #ef4444;
    text-align: center;
  }

  .error-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
  }

  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    color: #94a3b8;
  }

  .empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
  }

  /* Scrollbar */
  .custom-scrollbar::-webkit-scrollbar {
    width: 8px;
  }

  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.5);
    border-radius: 4px;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.8);
  }
</style>
