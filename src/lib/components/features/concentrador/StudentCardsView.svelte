<script lang="ts">
  import { writable, get } from "svelte/store";
  import type {
    EstudianteRow,
    AsignaturaNota,
    PeriodoValoracion,
    EstudianteDetalle,
    ConcentradorParsed,
  } from "$lib/types";
  import {
    parsed,
    loading,
    error,
    payload,
    loadConcentradorData,
    selectedPeriodos,
  } from "$lib/storeConcentrador";
  import { fetchStudentDetails } from "$lib/api";
  import { onMount } from "svelte";
  import { fade, fly } from "svelte/transition";

  export let selectedStudent: EstudianteRow | null = null;
  let selectedStudentDetails: EstudianteDetalle | null = null;
  let loadingStudentDetails = false;
  let studentDetailsError: string | null = null;

  // Fetch initial concentrador data on mount
  onMount(() => {
    // Only load if parsed data isn't already available
    if (!get(parsed)) {
      loadConcentradorData();
    }
  });

  // Reactive statement to fetch student details when selectedStudent changes
  $: if (selectedStudent) {
    fetchDetailsForSelectedStudent(selectedStudent.id, get(payload).year);
  } else {
    selectedStudentDetails = null;
    studentDetailsError = null;
  }

  async function fetchDetailsForSelectedStudent(
    studentId: string,
    year: string
  ) {
    loadingStudentDetails = true;
    studentDetailsError = null;
    try {
      selectedStudentDetails = await fetchStudentDetails(studentId, year);
    } catch (e: any) {
      studentDetailsError = e.message;
      console.error("Error fetching student details:", e);
    } finally {
      loadingStudentDetails = false;
    }
  }

  function handleStudentClick(student: EstudianteRow) {
    selectedStudent = student;
  }

  // Helper to format grades for display
  function getGradeForPeriod(
    asignatura: AsignaturaNota,
    periodoName: string
  ): number | string {
    const period = asignatura.periodos.find((p) => p.periodo === periodoName);
    return period ? period.valoracion.toFixed(2) : "-";
  }

  function getGradeStyles(grade: number | string): string {
    if (typeof grade === "string")
      return "bg-gray-100 text-gray-500 border-gray-200";
    const num = Number(grade);
    if (num >= 4.0)
      return "bg-emerald-100 text-emerald-700 border-emerald-200 shadow-emerald-500/10";
    if (num >= 3.0)
      return "bg-blue-100 text-blue-700 border-blue-200 shadow-blue-500/10";
    return "bg-rose-100 text-rose-700 border-rose-200 shadow-rose-500/10";
  }

  function calculateDefinitiva(asignatura: AsignaturaNota): number {
    if (!asignatura.periodos || asignatura.periodos.length === 0) return 0;
    const sum = asignatura.periodos.reduce((acc, p) => acc + p.valoracion, 0);
    return sum / asignatura.periodos.length;
  }
</script>

<div
  class="h-[calc(100vh-60px)] w-full bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 p-4 md:p-6 flex flex-col md:flex-row gap-6 overflow-hidden font-sans text-slate-800"
>
  <!-- Student List Panel -->
  <div
    class="flex-1 min-w-[300px] md:max-w-md flex flex-col bg-white/70 backdrop-blur-xl border border-white/40 rounded-2xl shadow-xl overflow-hidden transition-all duration-300 hover:shadow-2xl hover:bg-white/80"
  >
    <div
      class="p-5 border-b border-slate-100/50 bg-white/50 backdrop-blur-sm sticky top-0 z-10"
    >
      <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
        <span class="p-2 rounded-lg bg-indigo-100 text-indigo-600">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
            /></svg
          >
        </span>
        Estudiantes
      </h2>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
      {#if $loading}
        <div class="flex flex-col items-center justify-center h-40 space-y-3">
          <div
            class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"
          ></div>
          <p class="text-slate-500 text-sm font-medium">
            Cargando estudiantes...
          </p>
        </div>
      {:else if $error}
        <div
          class="p-4 m-2 bg-rose-50 border border-rose-100 text-rose-600 rounded-xl text-sm"
        >
          <p class="font-semibold">Error</p>
          <p>{$error}</p>
        </div>
      {:else if $parsed && ($parsed as ConcentradorParsed).estudiantes.length > 0}
        <ul class="space-y-1">
          {#each ($parsed as ConcentradorParsed).estudiantes as student (student.id)}
            <li>
              <button
                class="w-full text-left px-4 py-3 rounded-xl transition-all duration-200 group relative overflow-hidden
                {selectedStudent?.id === student.id
                  ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 scale-[1.02]'
                  : 'hover:bg-white hover:shadow-md text-slate-600 hover:text-indigo-600'}"
                on:click={() => handleStudentClick(student)}
              >
                <div class="relative z-10 flex items-center justify-between">
                  <span class="font-medium truncate">{student.nombres}</span>
                  {#if selectedStudent?.id === student.id}
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5 opacity-80"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                      ><path
                        fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L6 12.586l7.293-7.293a1 1 0 011.414 1.414l-8 8z"
                        clip-rule="evenodd"
                      /></svg
                    >
                  {/if}
                </div>
              </button>
            </li>
          {/each}
        </ul>
      {:else}
        <div class="p-8 text-center text-slate-400">
          <p>No se encontraron estudiantes.</p>
        </div>
      {/if}
    </div>
  </div>

  <!-- Student Details Panel -->
  <div
    class="flex-[2.5] bg-white/70 backdrop-blur-xl border border-white/40 rounded-2xl shadow-xl overflow-hidden flex flex-col transition-all duration-300 relative"
  >
    {#if !selectedStudent}
      <div
        class="flex-1 flex flex-col items-center justify-center text-slate-400 p-8 text-center"
      >
        <div
          class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-300"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-12 w-12"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            ><path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1.5"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
            /></svg
          >
        </div>
        <p class="text-lg font-medium">Selecciona un estudiante</p>
        <p class="text-sm opacity-70">
          Elige un estudiante de la lista para ver sus detalles académicos.
        </p>
      </div>
    {:else}
      <div
        class="h-full overflow-y-auto custom-scrollbar p-6 md:p-8"
        in:fade={{ duration: 300 }}
      >
        <!-- Header -->
        <div
          class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 pb-6 border-b border-slate-200/60"
        >
          <div>
            <h2
              class="text-3xl font-bold text-slate-800 bg-clip-text text-transparent bg-gradient-to-r from-slate-800 to-indigo-600"
            >
              {selectedStudent.nombres}
            </h2>
            <p class="text-slate-500 mt-1 flex items-center gap-2">
              <span class="inline-block w-2 h-2 rounded-full bg-emerald-400"
              ></span>
              Estudiante Activo
            </p>
          </div>
          <div class="flex gap-2">
            <button
              class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-colors shadow-sm"
            >
              Exportar
            </button>
          </div>
        </div>

        {#if loadingStudentDetails}
          <div class="space-y-4 animate-pulse">
            <div class="h-32 bg-slate-100 rounded-xl"></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="h-24 bg-slate-100 rounded-xl"></div>
              <div class="h-24 bg-slate-100 rounded-xl"></div>
              <div class="h-24 bg-slate-100 rounded-xl"></div>
            </div>
          </div>
        {:else if studentDetailsError}
          <div
            class="p-6 bg-rose-50 border border-rose-100 rounded-xl text-rose-700 flex items-center gap-4"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-8 w-8 text-rose-500"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              /></svg
            >
            <div>
              <h3 class="font-bold">Error al cargar detalles</h3>
              <p class="text-sm opacity-90">{studentDetailsError}</p>
            </div>
          </div>
        {:else if selectedStudentDetails}
          <!-- Info Cards -->
          <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8"
          >
            <div
              class="bg-white/40 backdrop-blur-md p-4 rounded-xl border border-white/60 shadow-lg shadow-indigo-500/5 hover:shadow-indigo-500/10 hover:bg-white/50 hover:scale-[1.02] transition-all duration-300"
            >
              <p
                class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-1"
              >
                Nivel Académico
              </p>
              <p class="text-lg font-semibold text-slate-700">
                {selectedStudentDetails.nivel}
              </p>
            </div>
            <div
              class="bg-white/40 backdrop-blur-md p-4 rounded-xl border border-white/60 shadow-lg shadow-indigo-500/5 hover:shadow-indigo-500/10 hover:bg-white/50 hover:scale-[1.02] transition-all duration-300"
            >
              <p
                class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-1"
              >
                Grado
              </p>
              <p class="text-lg font-semibold text-slate-700">
                {selectedStudentDetails.grado}
              </p>
            </div>
            <div
              class="bg-white/40 backdrop-blur-md p-4 rounded-xl border border-white/60 shadow-lg shadow-indigo-500/5 hover:shadow-indigo-500/10 hover:bg-white/50 hover:scale-[1.02] transition-all duration-300"
            >
              <p
                class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-1"
              >
                Acudiente
              </p>
              <p
                class="text-lg font-semibold text-slate-700 truncate"
                title={selectedStudentDetails.acudiente}
              >
                {selectedStudentDetails.acudiente}
              </p>
            </div>
            <div
              class="bg-white/40 backdrop-blur-md p-4 rounded-xl border border-white/60 shadow-lg shadow-indigo-500/5 hover:shadow-indigo-500/10 hover:bg-white/50 hover:scale-[1.02] transition-all duration-300"
            >
              <p
                class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-1"
              >
                Contacto
              </p>
              <p class="text-lg font-semibold text-slate-700">
                {selectedStudentDetails.telefono1 || "N/A"}
              </p>
            </div>
            <div
              class="bg-white/40 backdrop-blur-md p-4 rounded-xl border border-white/60 shadow-lg shadow-indigo-500/5 hover:shadow-indigo-500/10 hover:bg-white/50 hover:scale-[1.02] transition-all duration-300 sm:col-span-2 lg:col-span-2"
            >
              <p
                class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-1"
              >
                Correo Electrónico
              </p>
              <p class="text-lg font-semibold text-slate-700">
                {selectedStudentDetails.email_estudiante || "N/A"}
              </p>
            </div>
          </div>
        {/if}

        <div class="mt-8">
          <h4
            class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2"
          >
            <span class="p-1.5 bg-indigo-100 text-indigo-600 rounded-lg">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                ><path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                /></svg
              >
            </span>
            Notas por Asignatura
          </h4>

          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            {#if selectedStudent.asignaturas && selectedStudent.asignaturas.length > 0}
              {#each selectedStudent.asignaturas as asignatura, i (asignatura.asignatura)}
                <div
                  class="bg-white/40 backdrop-blur-md rounded-2xl p-5 shadow-sm border border-white/50 hover:shadow-xl hover:shadow-indigo-500/10 hover:-translate-y-1 hover:bg-white/60 transition-all duration-300 group relative overflow-hidden"
                  in:fly={{ y: 20, duration: 300, delay: i * 50 }}
                >
                  <div
                    class="absolute inset-0 bg-gradient-to-br from-white/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                  ></div>
                  <h5
                    class="font-bold text-slate-700 mb-4 pb-3 border-b border-slate-100 group-hover:text-indigo-600 transition-colors line-clamp-2 min-h-[3.5rem]"
                  >
                    {asignatura.asignatura}
                  </h5>
                  <div class="flex flex-wrap gap-2">
                    {#each $selectedPeriodos as periodo}
                      {@const grade = getGradeForPeriod(asignatura, periodo)}
                      <div
                        class="flex flex-col items-center {getGradeStyles(
                          grade
                        )} backdrop-blur-sm rounded-xl p-2 flex-1 min-w-[60px] border shadow-sm transition-transform hover:scale-105"
                      >
                        <span
                          class="text-[10px] font-bold opacity-70 uppercase tracking-wider mb-1"
                          >{periodo}</span
                        >
                        <span class="font-bold text-lg">
                          {grade}
                        </span>
                      </div>
                    {/each}
                    <div
                      class="flex flex-col items-center {getGradeStyles(
                        calculateDefinitiva(asignatura)
                      )} backdrop-blur-sm rounded-xl p-2 flex-1 min-w-[60px] border-2 shadow-md transition-transform hover:scale-110 relative overflow-hidden"
                    >
                      <div class="absolute inset-0 bg-white/20"></div>
                      <span
                        class="text-[10px] font-extrabold opacity-80 uppercase tracking-wider mb-1 z-10"
                        >DEF</span
                      >
                      <span class="font-bold text-lg z-10">
                        {calculateDefinitiva(asignatura).toFixed(2)}
                      </span>
                    </div>
                  </div>
                </div>
              {/each}
            {:else}
              <div
                class="col-span-full p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-300 text-slate-400"
              >
                <p>No hay asignaturas registradas para este estudiante.</p>
              </div>
            {/if}
          </div>
        </div>
      </div>
    {/if}
  </div>
</div>

<style>
  /* Custom Scrollbar for Webkit */
  .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }
  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.3);
    border-radius: 20px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.5);
  }
</style>
