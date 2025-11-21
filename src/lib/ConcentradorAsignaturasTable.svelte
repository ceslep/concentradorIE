<script lang="ts">
  import type { EstudianteRow, ConcentradorParsed } from './types'
  import { theme } from './themeStore'
  import { parsed, loading, payload, showPeriodos, selectedPeriodos, currentOrden, concentradorType, selectedAsignatura } from './storeConcentrador'

  export let handleValoracionClick: (est: EstudianteRow, asignaturaAbrev: string, periodo: string, valoracion: string) => Promise<void>
  export let onHeaderClick: () => void // New prop
  let handleShowInasistencias: (estudianteId: string, nombres: string, asignatura: string, periodo: string) => void

  let search = ''

  $: currentTheme = $theme
  $: dark = currentTheme === 'dark'

  $: bgSurface = dark ? 'bg-gray-800' : 'bg-gray-100'
  $: bgHeader = dark ? 'bg-gray-700' : 'bg-gray-200'
  $: textPrimary = dark ? 'text-white' : 'text-gray-900'
  $: textSecondary = dark ? 'text-gray-300' : 'text-gray-900'
  $: borderDivide = dark ? 'divide-gray-700' : 'divide-gray-300'
  $: hoverBg = dark ? 'hover:bg-gray-700' : 'hover:bg-gray-200'
  $: inputClasses = `rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 mt-1 ${
    dark ? 'bg-gray-600 border-gray-500 text-white' : 'bg-gray-100 border-gray-300 text-gray-900'
  }`

  $: filteredEstudiantes = (() => {
    if (!$parsed || $concentradorType !== 'asignaturas') return [] as EstudianteRow[]
    const p = $parsed as ConcentradorParsed
    return !search.trim()
      ? p.estudiantes
      : p.estudiantes.filter(est => est.nombres.toLowerCase().includes(search.toLowerCase()))
  })()

  function valorPeriodo(est: EstudianteRow, asignatura: string, periodo: string): string {
    const a = est.asignaturas?.find(a => a.asignatura === asignatura)
    if (!a) return ''
    const p = a.periodos?.find(p => p.periodo === periodo)
    const val = p?.valoracion
    return val != null ? val.toFixed(1) : ''
  }

  function getPeriodBorderColor(periodo: string): string {
    switch (periodo) {
      case 'UNO': return 'border-blue-500'
      case 'DOS': return 'border-green-500'
      case 'TRES': return 'border-yellow-500'
      case 'CUATRO': return 'border-purple-500'
      case 'DEF': return 'border-indigo-500'
      default: return 'border-gray-500'
    }
  }

  function getShortPeriodName(periodo: string): string {
    switch (periodo) {
      case 'UNO': return '1'
      case 'DOS': return '2'
      case 'TRES': return '3'
      case 'CUATRO': return '4'
      case 'DEF': return 'D'
      default: return periodo
    }
  }

  function getItemName(abreviatura: string): string {
    if (!$parsed || $concentradorType !== 'asignaturas') return abreviatura
    const p = $parsed as ConcentradorParsed
    if (!p.asignaturas) return abreviatura
    return p.asignaturas.find(a => a.abreviatura === abreviatura)?.nombre ?? abreviatura
  }

  function colorNota(valor: string): string {
    if (!valor) return dark ? 'bg-gray-700 text-gray-400' : 'bg-gray-200 text-gray-700'
    const v = parseFloat(valor)
    if (isNaN(v)) return dark ? 'bg-gray-700 text-gray-400' : 'bg-gray-200 text-gray-700'
    if (v >= 4) return 'bg-emerald-500 text-white'
    if (v >= 3) return 'bg-amber-500 text-white'
    return 'bg-rose-500 text-white'
  }
</script>

{#if $parsed && !$loading && $concentradorType === 'asignaturas'}
  <div class="rounded-lg shadow-lg overflow-hidden flex-grow {bgSurface}">
    <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
      <table class="min-w-full text-sm text-left {textSecondary}">
        <thead class="text-xs uppercase tracking-wider sticky top-0 z-10 {bgHeader} {dark ? 'text-gray-400' : 'text-gray-600'}">
          <tr>
            <th
              scope="col"
              class="p-2 text-left sticky left-0 z-10 w-max {bgHeader}"
              rowspan="{$showPeriodos ? 2 : 1}"
            >
              <div class="flex flex-col">
                <span class="flex items-center gap-1">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                  </svg>
                  Estudiante
                </span>
                <input
                  type="text"
                  aria-label="Buscar estudiante por nombre"
                  placeholder="Buscar estudiante..."
                  bind:value={search}
                  class={inputClasses}
                />
              </div>
            </th>
            {#each $currentOrden as itemAbrev}
              {#if itemAbrev}
                <th scope="col" class="p-0 whitespace-nowrap text-center">
                  <button
                    class="block w-full h-full p-2 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200
                           {$theme === 'dark' ? 'text-gray-400 hover:bg-gray-600' : 'text-gray-600 hover:bg-gray-200'}"
                    on:click={() => { selectedAsignatura.set(itemAbrev); onHeaderClick(); }}
                    title="Seleccionar {getItemName(itemAbrev)}"
                  >
                    {getItemName(itemAbrev)}
                  </button>
                </th>
              {/if}
            {/each}
          </tr>

          {#if $showPeriodos}
            <tr class="border-t {bgHeader} {dark ? 'border-gray-600' : 'border-gray-300'}">
              {#each $currentOrden as itemAbrev}
                {#if itemAbrev}
                  <th class="p-0">
                    <div
                      class="grid gap-1 justify-items-center"
                      style="grid-template-columns: repeat({$selectedPeriodos.length + 1}, minmax(32px, 1fr));"
                    >
                      {#each $selectedPeriodos.filter((p: string) => p !== 'DEF') as per}
                        <span
                          class="px-1 rounded-full border-2 {getPeriodBorderColor(per)}"
                          title="{getItemName(itemAbrev)} - Período {getShortPeriodName(per)}"
                        >
                          {getShortPeriodName(per)}
                        </span>
                      {/each}
                      <span
                        class="rounded-md px-1 text-xs font-bold border-2 {getPeriodBorderColor('DEF')}"
                        title="{getItemName(itemAbrev)} - Definitiva"
                      >
                        DEF
                      </span>
                    </div>
                  </th>
                {/if}
              {/each}
            </tr>
          {/if}
        </thead>

        <tbody class="divide-y {borderDivide}">
          {#each filteredEstudiantes as est (est.nombres)}
            <tr class="transition duration-150 {hoverBg}">
              <td class="p-4 font-medium sticky left-0 z-10 whitespace-nowrap {textPrimary} {bgSurface} {hoverBg}">
                {est.nombres}
              </td>
              {#each $currentOrden as itemAbrev}
                {#if itemAbrev}
                  <td class="p-2 text-center align-middle">
                    {#if !$showPeriodos}
                      {#if $payload && $payload.periodo}
                        <button
                          type="button"
                          class="font-bold text-lg {colorNota(valorPeriodo(est, itemAbrev, $payload.periodo))} px-2 py-1 rounded-md border-2 {getPeriodBorderColor($payload.periodo)} cursor-pointer"
                          on:click={() => handleValoracionClick(est, itemAbrev, $payload.periodo, valorPeriodo(est, itemAbrev, $payload.periodo))}
                          title="{est.nombres} – {getItemName(itemAbrev)} – Período {getShortPeriodName($payload.periodo)}: {valorPeriodo(est, itemAbrev, $payload.periodo) || 'Sin nota'}"
                        >
                          {valorPeriodo(est, itemAbrev, $payload.periodo) || '-'}
                        </button>
                      {/if}
                    {:else}
                      <div
                        class="grid gap-1 justify-items-center"
                        style="grid-template-columns: repeat({$selectedPeriodos.length + 1}, minmax(32px, 1fr));"
                      >
                        {#each $selectedPeriodos.filter((p: string) => p !== 'DEF') as per}
                          <button
                            type="button"
                            class="rounded-md px-1 py-1 text-xs font-bold {colorNota(valorPeriodo(est, itemAbrev, per))} border-2 {getPeriodBorderColor(per)} cursor-pointer"
                            on:click={() => handleValoracionClick(est, itemAbrev, per, valorPeriodo(est, itemAbrev, per))}
                            title="{est.nombres} – {getItemName(itemAbrev)} – Período {getShortPeriodName(per)}: {valorPeriodo(est, itemAbrev, per) || 'Sin nota'}"
                          >
                            {valorPeriodo(est, itemAbrev, per) || '-'}
                          </button>
                        {/each}
                        <button
                          type="button"
                          class="rounded-md px-1 py-1 text-xs font-bold {colorNota(valorPeriodo(est, itemAbrev, 'DEF'))} border-2 {getPeriodBorderColor('DEF')} cursor-pointer"
                          on:click={() => handleValoracionClick(est, itemAbrev, 'DEF', valorPeriodo(est, itemAbrev, 'DEF'))}
                          title="{est.nombres} – {getItemName(itemAbrev)} – Definitiva: {valorPeriodo(est, itemAbrev, 'DEF') || 'Sin nota'}"
                        >
                          {valorPeriodo(est, itemAbrev, 'DEF') || '-'}
                        </button>
                      </div>
                    {/if}
                  </td>
                {/if}
              {/each}
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  </div>
{/if}