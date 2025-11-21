<script lang="ts">
  import type { ConcentradorAreasParsed, EstudianteRowArea } from './types'
  import { theme } from './themeStore'
  import { parsed, loading, concentradorType, showPeriodos, selectedPeriodos, currentOrden } from './storeConcentrador'

  export let handleValoracionClick: (est: EstudianteRowArea, itemAbrev: string, periodo: string, valoracion: string) => Promise<void>
  let handleShowInasistencias: (estudianteId: string, nombres: string, asignatura: string, periodo: string) => void

  let search = ''

  // Tema reactivo local para evitar múltiples lecturas del store
  $: currentTheme = $theme
  $: dark = currentTheme === 'dark'

  // Clases reutilizables
  $: bgSurface = dark ? 'bg-gray-800' : 'bg-gray-100'
  $: bgHeader = dark ? 'bg-gray-700' : 'bg-gray-200'
  $: textPrimary = dark ? 'text-white' : 'text-gray-900'
  $: textSecondary = dark ? 'text-gray-300' : 'text-gray-900'
  $: textMuted = dark ? 'text-gray-400' : 'text-gray-700'
  $: borderDivide = dark ? 'divide-gray-700' : 'divide-gray-300'
  $: hoverBg = dark ? 'hover:bg-gray-700' : 'hover:bg-gray-200'
  $: inputClasses = `rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200 mt-1 ${
    dark ? 'bg-gray-600 border-gray-500 text-white' : 'bg-gray-100 border-gray-300 text-gray-900'
  }`

  $: filteredEstudiantes = (() => {
    if (!$parsed || $concentradorType !== 'areas') return [] as EstudianteRowArea[]
    const p = $parsed as ConcentradorAreasParsed
    const list = p.estudiantes
    if (!search.trim()) return list
    const q = search.toLowerCase()
    return list.filter(est => est.nombres.toLowerCase().includes(q))
  })()

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
    if (!$parsed || $concentradorType !== 'areas') return abreviatura
    const p = $parsed as ConcentradorAreasParsed
    if (!p.areas) return abreviatura
    const item = p.areas.find(a => a.abreviatura === abreviatura)
    return item?.nombre ?? abreviatura
  }

  function valorPeriodo(est: EstudianteRowArea, areaAbrev: string, periodo: string): string {
    const a = est.areas?.find(a => a.area === areaAbrev)
    if (!a) return ''
    const p = a.periodos?.find(p => p.periodo === periodo)
    const val = p?.valoracion
    return val != null ? val.toFixed(1) : ''
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

{#if $parsed && !$loading && $concentradorType === 'areas'}
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
                <th scope="col" class="p-2 whitespace-nowrap text-center">
                  <span class="block text-xs font-medium">{itemAbrev.abreviatura}</span>
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
                          title="{getItemName(itemAbrev.abreviatura)} - {per}"
                        >
                          {getShortPeriodName(per)}
                        </span>
                      {/each}
                      <span
                        class="rounded-md px-1 text-xs font-bold border-2 {getPeriodBorderColor('DEF')}"
                        title="{getItemName(itemAbrev.abreviatura)} - Definitiva"
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
              <td
                class="p-4 font-medium sticky left-0 z-10 whitespace-nowrap w-max {textPrimary} {bgSurface} {hoverBg}"
              >
                {est.nombres}
              </td>
              {#each $currentOrden as itemAbrev}
                {#if itemAbrev}
                  <td class="p-2 text-center align-middle">
                    {#if !$showPeriodos}
                      <button
                        type="button"
                        class="font-bold text-lg {colorNota(valorPeriodo(est, itemAbrev.abreviatura, 'DEF'))} px-2 py-1 rounded-md border-2 {getPeriodBorderColor('DEF')} cursor-pointer"
                        on:click={() => handleValoracionClick(est, itemAbrev.abreviatura, 'DEF', valorPeriodo(est, itemAbrev.abreviatura, 'DEF'))}
                        title="{est.nombres} – {getItemName(itemAbrev.abreviatura)} – Definitiva: {valorPeriodo(est, itemAbrev.abreviatura, 'DEF') || 'Sin nota'}"
                      >
                        {valorPeriodo(est, itemAbrev.abreviatura, 'DEF') || '-'}
                      </button>
                    {:else}
                      <div
                        class="grid gap-1 justify-items-center"
                        style="grid-template-columns: repeat({$selectedPeriodos.length + 1}, minmax(32px, 1fr));"
                      >
                        {#each $selectedPeriodos.filter((p: string) => p !== 'DEF') as per}
                          <button
                            type="button"
                            class="rounded-md px-1 py-1 text-xs font-bold {colorNota(valorPeriodo(est, itemAbrev.abreviatura, per))} border-2 {getPeriodBorderColor(per)} cursor-pointer"
                            on:click={() => handleValoracionClick(est, itemAbrev.abreviatura, per, valorPeriodo(est, itemAbrev.abreviatura, per))}
                            title="{est.nombres} – {getItemName(itemAbrev.abreviatura)} – Período {getShortPeriodName(per)}: {valorPeriodo(est, itemAbrev.abreviatura, per) || 'Sin nota'}"
                          >
                            {valorPeriodo(est, itemAbrev.abreviatura, per) || '-'}
                          </button>
                        {/each}
                        <button
                          type="button"
                          class="rounded-md px-1 py-1 text-xs font-bold {colorNota(valorPeriodo(est, itemAbrev.abreviatura, 'DEF'))} border-2 {getPeriodBorderColor('DEF')} cursor-pointer"
                          on:click={() => handleValoracionClick(est, itemAbrev.abreviatura, 'DEF', valorPeriodo(est, itemAbrev.abreviatura, 'DEF'))}
                          title="{est.nombres} – {getItemName(itemAbrev.abreviatura)} – Definitiva: {valorPeriodo(est, itemAbrev.abreviatura, 'DEF') || 'Sin nota'}"
                        >
                          {valorPeriodo(est, itemAbrev.abreviatura, 'DEF') || '-'}
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