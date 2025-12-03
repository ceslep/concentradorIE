<script lang="ts">
  import { writable, get } from "svelte/store";
  import type {
    EstudianteRow,
    AsignaturaNota,
    PeriodoValoracion,
    EstudianteDetalle,
    ConcentradorParsed,
    ConcentradorAreasParsed,
    Asignatura,
    Area,
    NotaDetalle,
  } from "$lib/types";
  import {
    parsed,
    loading,
    error,
    payload,
    loadConcentradorData,
    selectedPeriodos,
    concentradorType,
  } from "$lib/storeConcentrador";
  import {
    fetchStudentDetails,
    fetchNotasDetallado,
    fetchNotasDetalladoAreas,
  } from "$lib/api";
  import { onMount, createEventDispatcher } from "svelte";
  import { fade, fly, scale } from "svelte/transition";
  import NotasDetalleDialog from "../dialogs/notas/NotasDetalleDialog.svelte";

  export let selectedStudent: EstudianteRow | null = null;
  let selectedStudentDetails: EstudianteDetalle | null = null;
  let loadingStudentDetails = false;
  let studentDetailsError: string | null = null;

  // Notas Dialog State
  let showNotasDialog = false;
  let notasDetalle: NotaDetalle[] = [];
  let loadingNotas = false;
  let notasError: string | null = null;

  // Context for Dialog
  let dialogStudentName = "";
  let dialogAsignatura = "";
  let dialogPeriodo = "";
  let dialogEstudianteId = "";
  let dialogYear = "";

  // Convivencia Dialog State
  let showConvivenciaDialog = false;

  // UI States
  let isSidebarCollapsed = false;
  let activeTab = "asignaturas";
  let hoveredStudent: string | null = null;
  let rippleElements: Array<{ x: number; y: number; id: number }> = [];
  let nextRippleId = 0;

  const dispatch = createEventDispatcher();

  // Add ripple effect
  function createRipple(event: MouseEvent) {
    const button = event.currentTarget as HTMLElement;
    const rect = button.getBoundingClientRect();

    rippleElements = [
      ...rippleElements,
      {
        x: event.clientX - rect.left,
        y: event.clientY - rect.top,
        id: nextRippleId++,
      },
    ];

    setTimeout(() => {
      rippleElements = rippleElements.slice(1);
    }, 600);
  }

  async function handleOpenNotasDetalle(
    student: EstudianteRow,
    asignatura: AsignaturaNota,
  ) {
    const p = get(parsed);
    const currentConcentradorType = get(concentradorType);
    let itemNombre = asignatura.asignatura;

    if (p) {
      if (currentConcentradorType === "asignaturas") {
        const parsedAsignaturas = p as ConcentradorParsed;
        if (parsedAsignaturas.asignaturas) {
          const foundItem = parsedAsignaturas.asignaturas.find(
            (item: Asignatura) => item.abreviatura === asignatura.asignatura,
          );
          if (foundItem) {
            itemNombre = foundItem.nombre;
          }
        }
      } else if (currentConcentradorType === "areas") {
        const parsedAreas = p as ConcentradorAreasParsed;
        if (parsedAreas.areas) {
          const foundItem = parsedAreas.areas.find(
            (item: Area) => item.abreviatura === asignatura.asignatura,
          );
          if (foundItem) {
            itemNombre = foundItem.nombre;
          }
        }
      }
    }

    // Prepare Dialog State
    dialogStudentName = student.nombres;
    dialogAsignatura = itemNombre;
    dialogEstudianteId = student.id;
    dialogYear = get(payload).year;
    dialogPeriodo = "";

    showNotasDialog = true;
    loadingNotas = true;
    notasError = null;
    notasDetalle = [];

    try {
      const currentPayload = get(payload);
      const detailPayload = {
        estudiante: student.id,
        nombres: student.nombres,
        asignatura: asignatura.asignatura,
        asignat: asignatura.asignatura,
        valoracion: "",
        periodo: currentPayload.periodo,
        year: currentPayload.year,
        asignacion: currentPayload.Asignacion,
        nivel: currentPayload.nivel,
        numero: currentPayload.numero,
      };

      if (currentConcentradorType === "areas") {
        notasDetalle = await fetchNotasDetalladoAreas(detailPayload);
      } else {
        notasDetalle = await fetchNotasDetallado(detailPayload);
      }
    } catch (err: any) {
      console.error("Error fetching notes details:", err);
      notasError = err.message || "Error al cargar detalles de notas";
    } finally {
      loadingNotas = false;
    }
  }

  function handleShowInasistencias(
    estId: string,
    nom: string,
    asig: string,
    per: string,
  ) {
    dispatch("openInasistencias", {
      estudianteId: estId,
      nombres: nom,
      asignatura: asig,
      periodo: per,
    });
  }

  onMount(() => {
    if (!get(parsed)) {
      loadConcentradorData();
    }
  });

  $: if (selectedStudent) {
    fetchDetailsForSelectedStudent(selectedStudent.id, get(payload).year);
  } else {
    selectedStudentDetails = null;
    studentDetailsError = null;
  }

  async function fetchDetailsForSelectedStudent(
    studentId: string,
    year: string,
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

  function getGradeForPeriod(
    asignatura: AsignaturaNota,
    periodoName: string,
  ): number | string {
    const period = asignatura.periodos.find((p) => p.periodo === periodoName);
    return period ? period.valoracion.toFixed(2) : "-";
  }

  function getPeriodCardClasses(periodoName: string): string {
    switch (periodoName.toUpperCase()) {
      case "UNO":
        return "bg-gradient-to-br from-purple-500/10 to-purple-600/5 border-purple-200/50 shadow-lg shadow-purple-500/5";
      case "DOS":
        return "bg-gradient-to-br from-teal-500/10 to-teal-600/5 border-teal-200/50 shadow-lg shadow-teal-500/5";
      case "TRES":
        return "bg-gradient-to-br from-orange-500/10 to-amber-600/5 border-orange-200/50 shadow-lg shadow-orange-500/5";
      case "CUATRO":
        return "bg-gradient-to-br from-rose-500/10 to-pink-600/5 border-rose-200/50 shadow-lg shadow-rose-500/5";
      default:
        return "bg-gradient-to-br from-slate-500/10 to-slate-600/5 border-slate-200/50";
    }
  }

  function getGradeTextClass(grade: number | string): string {
    if (typeof grade === "string") return "text-slate-400";
    const num = Number(grade);
    if (num >= 4.0)
      return "text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-green-400";
    if (num >= 3.0)
      return "text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-cyan-400";
    return "text-transparent bg-clip-text bg-gradient-to-r from-rose-500 to-pink-400";
  }

  function getDefinitiveCardStyles(grade: number | string): string {
    let classes = "border-2 shadow-xl relative overflow-hidden group";
    if (typeof grade === "string") {
      return `${classes} bg-gradient-to-br from-gray-100 to-gray-200 text-gray-600 border-gray-300/30`;
    }
    const num = Number(grade);
    if (num >= 4.0) {
      return `${classes} bg-gradient-to-br from-emerald-500/20 to-green-400/10 text-emerald-700 border-emerald-400/30`;
    } else if (num >= 3.0) {
      return `${classes} bg-gradient-to-br from-blue-500/20 to-cyan-400/10 text-blue-700 border-blue-400/30`;
    } else {
      return `${classes} bg-gradient-to-br from-rose-500/20 to-pink-400/10 text-rose-700 border-rose-400/30`;
    }
  }

  function getPeriodoNameClasses(periodoName: string): string {
    switch (periodoName.toUpperCase()) {
      case "UNO":
        return "text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-violet-400";
      case "DOS":
        return "text-transparent bg-clip-text bg-gradient-to-r from-teal-600 to-emerald-400";
      case "TRES":
        return "text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-400";
      case "CUATRO":
        return "text-transparent bg-clip-text bg-gradient-to-r from-rose-600 to-pink-400";
      default:
        return "text-slate-500";
    }
  }

  function calculateDefinitiva(asignatura: AsignaturaNota): number {
    if (!asignatura.periodos || asignatura.periodos.length === 0) return 0;
    const sum = asignatura.periodos.reduce((acc, p) => acc + p.valoracion, 0);
    return sum / asignatura.periodos.length;
  }

  function getGradeColor(grade: number): string {
    if (grade >= 4.0) return "from-emerald-500 to-green-400";
    if (grade >= 3.0) return "from-blue-500 to-cyan-400";
    return "from-rose-500 to-pink-400";
  }

  // Helper function to get progress bar color
  function getProgressBarColor(definitive: number): string {
    if (definitive >= 4.0)
      return "bg-gradient-to-r from-emerald-500 to-green-400";
    if (definitive >= 3.0) return "bg-gradient-to-r from-blue-500 to-cyan-400";
    return "bg-gradient-to-r from-rose-500 to-pink-400";
  }

  // Helper function to get status text and class
  function getStatusInfo(definitive: number): { text: string; class: string } {
    if (definitive >= 4.0)
      return { text: "Excelente", class: "bg-emerald-500/20 text-emerald-300" };
    if (definitive >= 3.0)
      return { text: "Bueno", class: "bg-blue-500/20 text-blue-300" };
    return { text: "Requiere atención", class: "bg-rose-500/20 text-rose-300" };
  }
</script>

<div
  class="h-screen w-full bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 p-6 flex gap-6 overflow-hidden font-sans relative"
>
  <!-- Background Effects -->
  <div class="absolute inset-0 overflow-hidden pointer-events-none">
    <div
      class="absolute -top-40 -right-40 w-80 h-80 bg-purple-500/10 rounded-full blur-3xl"
    ></div>
    <div
      class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl"
    ></div>
    <div
      class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-sky-500/5 rounded-full blur-3xl"
    ></div>
  </div>

  <!-- Student List Panel - Glass Morphism -->
  <div
    class="flex-shrink-0 w-96 flex flex-col bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl shadow-2xl overflow-hidden transition-all duration-500 hover:shadow-purple-500/10 relative {isSidebarCollapsed
      ? 'collapsed'
      : ''}"
    style="--tw-backdrop-blur: blur(24px)"
  >
    <!-- Animated Background -->
    <div
      class="absolute inset-0 bg-gradient-to-br from-white/5 via-transparent to-purple-500/5"
    ></div>

    <div
      class="relative p-6 border-b border-white/10 bg-gradient-to-r from-white/10 to-transparent"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div
            class="p-2 rounded-2xl bg-gradient-to-br from-purple-500/20 to-indigo-500/20 backdrop-blur-sm"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 text-white"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
              />
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-bold text-white">Estudiantes</h2>
            <p class="text-sm text-white/60">Selecciona para ver detalles</p>
          </div>
        </div>
        <button
          on:click={() => (isSidebarCollapsed = !isSidebarCollapsed)}
          class="p-2 rounded-xl bg-white/5 hover:bg-white/10 transition-all duration-300 group"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 text-white/70 group-hover:text-white transition-colors"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d={isSidebarCollapsed
                ? "M13 5l7 7-7 7M5 5l7 7-7 7"
                : "M11 19l-7-7 7-7m8 14l-7-7 7-7"}
            />
          </svg>
        </button>
      </div>
    </div>

    <div class="relative flex-1 overflow-hidden">
      <div
        class="absolute inset-0 bg-gradient-to-b from-transparent to-black/5"
      ></div>
      <div class="h-full overflow-y-auto custom-scrollbar p-4 space-y-2">
        {#if $loading}
          <div class="space-y-3 p-4">
            {#each Array(5) as _, i}
              <div
                class="h-16 bg-white/5 rounded-2xl animate-pulse"
                style="animation-delay: {i * 100}ms"
              ></div>
            {/each}
          </div>
        {:else if $error}
          <div
            class="m-4 p-4 bg-gradient-to-r from-rose-500/20 to-pink-500/10 backdrop-blur-sm border border-rose-500/20 rounded-2xl"
            in:fly={{ y: 20 }}
          >
            <div class="flex items-center gap-3">
              <div class="p-2 bg-rose-500/20 rounded-xl">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-5 w-5 text-rose-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <div>
                <p class="font-semibold text-white">Error de carga</p>
                <p class="text-sm text-white/70">{$error}</p>
              </div>
            </div>
          </div>
        {:else if $parsed && ($parsed as ConcentradorParsed).estudiantes.length > 0}
          <div class="space-y-2">
            {#each ($parsed as ConcentradorParsed).estudiantes as student, i (student.id)}
              <div
                class="relative overflow-hidden group"
                on:mouseenter={() => (hoveredStudent = student.id)}
                on:mouseleave={() => (hoveredStudent = null)}
                in:fly={{ y: 20, delay: i * 50 }}
              >
                <!-- Hover Effect Background -->
                <div
                  class="absolute inset-0 bg-gradient-to-r from-purple-500/0 via-purple-500/5 to-purple-500/0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 {hoveredStudent ===
                  student.id
                    ? 'animate-slide-in'
                    : ''}"
                  style="transform: translateX(-100%); transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);"
                ></div>

                <button
                  class="w-full text-left p-4 rounded-2xl transition-all duration-300 relative overflow-hidden group/btn {selectedStudent?.id ===
                  student.id
                    ? 'selected'
                    : ''}"
                  style="
                    background: {selectedStudent?.id === student.id
                    ? 'linear-gradient(135deg, rgba(139, 92, 246, 0.15), rgba(99, 102, 241, 0.1))'
                    : 'rgba(255, 255, 255, 0.03)'};
                    border: 1px solid {selectedStudent?.id === student.id
                    ? 'rgba(139, 92, 246, 0.3)'
                    : 'rgba(255, 255, 255, 0.05)'};
                    transform: {selectedStudent?.id === student.id
                    ? 'translateX(8px)'
                    : 'none'};
                  "
                  on:click={(e) => {
                    createRipple(e);
                    handleStudentClick(student);
                  }}
                >
                  <!-- Ripple Effect -->
                  {#each rippleElements as ripple}
                    {#if ripple.id === i}
                      <div
                        class="absolute w-2 h-2 bg-white/30 rounded-full animate-ripple"
                        style="left: {ripple.x}px; top: {ripple.y}px;"
                      ></div>
                    {/if}
                  {/each}

                  <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold transition-all duration-300 group-hover/btn:scale-110"
                        style="
                          background: {selectedStudent?.id === student.id
                          ? 'linear-gradient(135deg, #8b5cf6, #6366f1)'
                          : 'linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05))'};
                          box-shadow: {selectedStudent?.id === student.id
                          ? '0 10px 30px -5px rgba(139, 92, 246, 0.4)'
                          : '0 4px 20px -2px rgba(0, 0, 0, 0.2)'};
                        "
                      >
                        {student.nombres.split(" ")[0][0]}
                      </div>
                      <div>
                        <p
                          class="font-semibold text-white group-hover/btn:text-purple-200 transition-colors"
                        >
                          {student.nombres}
                        </p>
                        <p class="text-xs text-white/50 mt-0.5">
                          ID: {student.id}
                        </p>
                      </div>
                    </div>
                    {#if selectedStudent?.id === student.id}
                      <div
                        class="p-1.5 rounded-full bg-gradient-to-r from-purple-500 to-indigo-500 animate-pulse-glow"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-4 w-4 text-white"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </div>
                    {/if}
                  </div>
                </button>
              </div>
            {/each}
          </div>
        {:else}
          <div class="p-8 text-center text-white/40">
            <div class="w-16 h-16 mx-auto mb-4 opacity-20">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1.5"
                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                />
              </svg>
            </div>
            <p>No hay estudiantes registrados</p>
          </div>
        {/if}
      </div>
    </div>
  </div>

  <!-- Main Content Panel -->
  <div class="flex-1 flex flex-col min-w-0">
    {#if !selectedStudent}
      <!-- Empty State - Enhanced -->
      <div
        class="flex-1 flex flex-col items-center justify-center relative overflow-hidden"
        in:fade={{ duration: 500 }}
      >
        <div
          class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent"
        ></div>
        <div class="relative z-10 text-center px-8 max-w-2xl">
          <div class="w-48 h-48 mx-auto mb-8 relative">
            <!-- Animated orb -->
            <div
              class="absolute inset-0 bg-gradient-to-r from-purple-500/20 to-indigo-500/20 rounded-full blur-2xl animate-pulse"
            ></div>
            <div
              class="relative w-full h-full flex items-center justify-center"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-32 w-32 text-white/20"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
              </svg>
            </div>
          </div>
          <h3
            class="text-3xl font-bold text-white mb-4 bg-clip-text text-transparent bg-gradient-to-r from-white to-purple-200"
          >
            Portal Académico
          </h3>
          <p class="text-white/60 text-lg mb-6">
            Selecciona un estudiante para visualizar su desempeño académico
            detallado
          </p>
          <div class="flex items-center justify-center gap-2 text-white/40">
            <div class="w-2 h-2 rounded-full bg-purple-500 animate-ping"></div>
            <p class="text-sm">Listo para explorar</p>
          </div>
        </div>
      </div>
    {:else}
      <!-- Student Details - Modern UI -->
      <div class="flex-1 flex flex-col min-h-0" in:scale={{ duration: 400 }}>
        <!-- Header - Glass Morphism -->
        <div
          class="bg-white/5 backdrop-blur-xl border-b border-white/10 rounded-t-3xl p-6 mb-6"
          style="--tw-backdrop-blur: blur(24px)"
        >
          <div class="flex items-start justify-between gap-6">
            <div class="flex-1">
              <div class="flex items-center gap-4 mb-4">
                <div
                  class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-bold text-white bg-gradient-to-br from-purple-500 to-indigo-500 shadow-lg shadow-purple-500/30 animate-float"
                >
                  {selectedStudent.nombres.split(" ")[0][0]}
                </div>
                <div>
                  <h1 class="text-3xl font-bold text-white mb-1">
                    {selectedStudent.nombres}
                  </h1>
                  <div class="flex items-center gap-3">
                    <span
                      class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gradient-to-r from-emerald-500/20 to-green-500/10 border border-emerald-500/30 text-emerald-300 text-sm"
                    >
                      <span
                        class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"
                      ></span>
                      Activo
                    </span>
                    <span class="text-white/40 text-sm">
                      ID: {selectedStudent.id}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <button
                class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-white/10 to-white/5 hover:from-white/20 hover:to-white/10 border border-white/10 text-white text-sm font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg hover:shadow-white/5"
              >
                <span class="flex items-center gap-2">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                  </svg>
                  Exportar PDF
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
          {#if loadingStudentDetails}
            <!-- Skeleton Loader -->
            <div class="space-y-6 animate-pulse">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {#each Array(4) as _}
                  <div class="h-24 bg-white/5 rounded-2xl"></div>
                {/each}
              </div>
              <div class="h-64 bg-white/5 rounded-2xl"></div>
            </div>
          {:else if studentDetailsError}
            <div
              class="p-6 rounded-2xl bg-gradient-to-r from-rose-500/20 to-pink-500/10 backdrop-blur-sm border border-rose-500/20"
              in:fly={{ y: 20 }}
            >
              <div class="flex items-center gap-4">
                <div class="p-3 bg-rose-500/20 rounded-xl">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 text-rose-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <div>
                  <h3 class="font-bold text-white">Error en los detalles</h3>
                  <p class="text-white/70">{studentDetailsError}</p>
                </div>
              </div>
            </div>
          {:else if selectedStudentDetails}
            <!-- Info Cards Grid -->
            <div
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8"
            >
              {#each [{ label: "Nivel Académico", value: selectedStudentDetails.nivel, icon: "M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4", color: "from-blue-500 to-cyan-400" }, { label: "Grado", value: selectedStudentDetails.grado, icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2", color: "from-purple-500 to-violet-400" }, { label: "Acudiente", value: selectedStudentDetails.acudiente, icon: "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z", color: "from-emerald-500 to-green-400" }, { label: "Contacto", value: selectedStudentDetails.telefono1 || "N/A", icon: "M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z", color: "from-amber-500 to-yellow-400" }] as item, i}
                <div
                  class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10 hover:border-white/20 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl hover:shadow-purple-500/10 group relative overflow-hidden"
                  in:fly={{ y: 20, delay: i * 100 }}
                >
                  <div
                    class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"
                  ></div>
                  <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3">
                      <div
                        class={`p-2 rounded-xl bg-gradient-to-br ${item.color}/20`}
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          class="h-5 w-5 text-white"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d={item.icon}
                          />
                        </svg>
                      </div>
                    </div>
                    <p class="text-sm font-medium text-white/60 mb-1">
                      {item.label}
                    </p>
                    <p class="text-lg font-bold text-white truncate">
                      {item.value}
                    </p>
                  </div>
                </div>
              {/each}
            </div>
          {/if}

          <!-- Academic Performance Section -->
          <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <div
                  class="p-2 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="1.5"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                    />
                  </svg>
                </div>
                <div>
                  <h3 class="text-2xl font-bold text-white">
                    Rendimiento Académico
                  </h3>
                  <p class="text-white/60 text-sm">
                    Notas por período y definitivas
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-2 bg-white/5 rounded-xl p-1">
                <button
                  class="px-4 py-2 rounded-lg transition-all duration-300 {activeTab ===
                  'asignaturas'
                    ? 'active'
                    : ''}"
                  on:click={() => (activeTab = "asignaturas")}
                >
                  <span
                    class="text-sm font-medium {activeTab === 'asignaturas'
                      ? 'text-white'
                      : 'text-white/60'}"
                  >
                    Asignaturas
                  </span>
                </button>
                <button
                  class="px-4 py-2 rounded-lg transition-all duration-300 {activeTab ===
                  'areas'
                    ? 'active'
                    : ''}"
                  on:click={() => (activeTab = "areas")}
                >
                  <span
                    class="text-sm font-medium {activeTab === 'areas'
                      ? 'text-white'
                      : 'text-white/60'}"
                  >
                    Áreas
                  </span>
                </button>
              </div>
            </div>

            <!-- Subjects Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
              {#if selectedStudent.asignaturas && selectedStudent.asignaturas.length > 0}
                {#each selectedStudent.asignaturas as asignatura, i (asignatura.asignatura)}
                  <!-- Calculate values once for each asignatura -->
                  {@const definitive = calculateDefinitiva(asignatura)}
                  {@const statusInfo = getStatusInfo(definitive)}
                  {@const progressColor = getProgressBarColor(definitive)}
                  <div
                    class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10 hover:border-white/20 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl hover:shadow-purple-500/10 group relative overflow-hidden"
                    in:fly={{ y: 30, duration: 400, delay: i * 80 }}
                  >
                    <!-- Animated background effect -->
                    <div
                      class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700"
                    ></div>

                    <!-- Subject Header -->
                    <div class="relative z-10">
                      <div class="flex items-start justify-between mb-5">
                        <h4
                          class="text-lg font-bold text-white group-hover:text-purple-200 transition-colors line-clamp-2"
                        >
                          {asignatura.asignatura}
                        </h4>
                        <button
                          class="p-2 rounded-xl bg-white/5 hover:bg-white/10 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-purple-500/20"
                          on:click={() =>
                            handleOpenNotasDetalle(
                              selectedStudent!,
                              asignatura,
                            )}
                          aria-label="Ver detalles"
                        >
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 text-white/70 group-hover:text-white"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                          >
                            <path
                              d="M12 4.5C7.5 4.5 3.73 7.61 3 12c.73 4.39 4.5 7.5 9 7.5s8.27-3.11 9-7.5c-.73-4.39-4.5-7.5-9-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"
                            />
                          </svg>
                        </button>
                      </div>

                      <!-- Period Grades -->
                      <div class="flex flex-wrap gap-3 mb-4">
                        {#each $selectedPeriodos as periodo, j}
                          {@const grade = getGradeForPeriod(
                            asignatura,
                            periodo,
                          )}
                          {@const cardClasses = getPeriodCardClasses(periodo)}
                          {@const textClass = getGradeTextClass(grade)}
                          {@const periodoClasses =
                            getPeriodoNameClasses(periodo)}
                          <div
                            class="flex-1 min-w-[70px]"
                            in:scale={{ delay: j * 100 }}
                          >
                            <div
                              class="flex flex-col items-center {cardClasses} rounded-xl p-3 border backdrop-blur-sm transition-all duration-300 hover:scale-105 hover:shadow-lg"
                            >
                              <span
                                class="text-xs font-bold uppercase tracking-wider mb-1 {periodoClasses}"
                              >
                                {periodo}
                              </span>
                              <span class="text-xl font-bold {textClass}">
                                {grade}
                              </span>
                            </div>
                          </div>
                        {/each}
                      </div>

                      <!-- Definitive Grade -->
                      <div
                        class="{getDefinitiveCardStyles(
                          definitive,
                        )} rounded-xl p-4 text-center transition-all duration-500 hover:scale-105"
                      >
                        <div
                          class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"
                        ></div>
                        <div class="relative z-10">
                          <div
                            class="flex items-center justify-center gap-2 mb-1"
                          >
                            <span
                              class="text-xs font-extrabold uppercase tracking-wider opacity-80"
                            >
                              Definitiva
                            </span>
                            {#if definitive >= 3.0}
                              <span
                                class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"
                              ></span>
                            {/if}
                          </div>
                          <div class="flex items-center justify-center gap-2">
                            <span class="text-2xl font-bold">
                              {definitive.toFixed(2)}
                            </span>
                            <span
                              class="text-xs px-2 py-1 rounded-full {statusInfo.class}"
                            >
                              {statusInfo.text}
                            </span>
                          </div>

                          <!-- Progress Bar -->
                          <div
                            class="mt-3 h-1.5 bg-white/10 rounded-full overflow-hidden"
                          >
                            <div
                              class="h-full rounded-full transition-all duration-1000 ease-out {progressColor}"
                              style="width: {Math.min(
                                (definitive / 5) * 100,
                                100,
                              )}%"
                            ></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                {/each}
              {:else}
                <div
                  class="col-span-full p-12 text-center bg-white/5 rounded-2xl border border-dashed border-white/10"
                >
                  <div class="w-20 h-20 mx-auto mb-4 opacity-20">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                      />
                    </svg>
                  </div>
                  <p class="text-white/40">No hay asignaturas registradas</p>
                </div>
              {/if}
            </div>
          </div>
        </div>
      </div>
    {/if}
  </div>
</div>

<!-- Dialog Component -->
<NotasDetalleDialog
  bind:showDialog={showNotasDialog}
  {notasDetalle}
  loading={loadingNotas}
  error={notasError}
  year={dialogYear}
  periodo={dialogPeriodo}
  estudianteId={dialogEstudianteId}
  asignatura={dialogAsignatura}
  studentName={dialogStudentName}
  onShowInasistencias={handleShowInasistencias}
  bind:showConvivenciaDialog
/>

<style>
  /* Custom Scrollbar */
  .custom-scrollbar::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }
  .custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(
      to bottom,
      rgba(139, 92, 246, 0.4),
      rgba(99, 102, 241, 0.4)
    );
    border-radius: 10px;
    border: 2px solid transparent;
    background-clip: padding-box;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(
      to bottom,
      rgba(139, 92, 246, 0.6),
      rgba(99, 102, 241, 0.6)
    );
  }

  /* Animations */
  @keyframes float {
    0%,
    100% {
      transform: translateY(0px);
    }
    50% {
      transform: translateY(-10px);
    }
  }

  @keyframes pulse-glow {
    0%,
    100% {
      box-shadow:
        0 0 20px rgba(139, 92, 246, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }
    50% {
      box-shadow:
        0 0 30px rgba(139, 92, 246, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }
  }

  @keyframes ripple {
    0% {
      transform: scale(0);
      opacity: 1;
    }
    100% {
      transform: scale(40);
      opacity: 0;
    }
  }

  @keyframes slide-in {
    from {
      transform: translateX(-100%);
    }
    to {
      transform: translateX(100%);
    }
  }

  .animate-float {
    animation: float 3s ease-in-out infinite;
  }

  .animate-pulse-glow {
    animation: pulse-glow 2s ease-in-out infinite;
  }

  .animate-ripple {
    animation: ripple 0.6s linear;
  }

  .animate-slide-in {
    animation: slide-in 0.6s ease-out;
  }

  /* Tab active state */
  button.active {
    background: linear-gradient(
      135deg,
      rgba(139, 92, 246, 0.2),
      rgba(99, 102, 241, 0.1)
    );
    box-shadow: 0 4px 20px -2px rgba(139, 92, 246, 0.3);
  }

  /* Button selected state */
  button.selected {
    box-shadow: 0 8px 32px rgba(139, 92, 246, 0.25);
  }

  /* Smooth transitions */
  * {
    scroll-behavior: smooth;
  }

  /* Glass morphism enhancement */
  .backdrop-blur-xl {
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
  }

  /* Gradient text */
  .gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  /* Line clamp */
  .line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
  }

  /* Card hover effects */
  .hover-lift {
    transition:
      transform 0.3s ease,
      box-shadow 0.3s ease;
  }

  .hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  }
</style>
