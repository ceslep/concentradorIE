<!-- 
STUDENTDETAILSMODAL.SVELTE

DESCRIPCIÓN:
Modal de edición integral para la información del estudiante. Presenta una interfaz organizada por pestañas (Personal, Académico, Contacto, Familiar, Médico) con estética glassmorphism premium.

USO:
<StudentDetailsModal {estudiante} bind:showModal />

DEPENDENCIAS:
- Store: eNombres, eGenero, eFecnac, etc. (storeConcentrador.ts) - Stores individuales para cada campo de edición.
- Animaciones: fade, slide, scale, cubicOut.

PROPS/EMIT:
- Prop: `showModal` → boolean → Control de visibilidad.
- Prop: `estudiante` → EstudianteDetalle → Objeto con la información inicial del estudiante.
- Emit: `close` → Notifica el cierre del modal.

RELACIONES:
- Llamado por: NotasDetalleDialog.svelte.

NOTAS DE DESARROLLO:
- Sincroniza bidireccionalmente los datos del prop `estudiante` con los stores globales mediante reactividad (`$: if (estudiante) ...`).
- Implementa un sistema de clases CSS reutilizables (`INPUT_CLASSES`, `LABEL_CLASSES`) para mantener la coherencia visual.

ESTILOS:
- Usa 'premium-glass' para el contenedor principal.
- Implementa una paleta de colores vibrante por pestaña (Indigo para personal, Emerald para académico, etc.).
-->

<script lang="ts">
  import { createEventDispatcher } from "svelte";
  import { fade, slide, scale } from "svelte/transition";
  import { cubicOut } from "svelte/easing";
  import type { EstudianteDetalle } from "../../../types";
  import {
    eNombres,
    eInstitucion_externa,
    eGenero,
    eFecnac,
    eEdad,
    eNivel,
    eGrado,
    eNumero,
    eAnio,
    ePass,
    eActivo,
    eBanda,
    eHED,
    eIdacudiente,
    eAcudiente,
    eTelefono1,
    eTelefono2,
    eDireccion,
    eEmail_estudiante,
    eEmail_acudiente,
    eDesertor,
    eOtraInformacion,
    eEstado,
    eYear,
    eLugar,
    eSisben,
    eEstrato,
    eLugarNacimiento,
    eLugarExpedicion,
    eFechaExpedicion,
    eTdei,
    eVictimaConflicto,
    eLugarDesplazamiento,
    eFechaDesplazamiento,
    eEtnia,
    eTipoSangre,
    eEps,
    ePadre,
    ePadreid,
    eTelefonopadre,
    eOcupacionpadre,
    eMadre,
    eMadreid,
    eTelefonomadre,
    eOcupacionmadre,
    eParentesco,
    eDiscapacidad,
    eTelefono_acudiente,
    eEanterior,
    eSede,
    etnias,
  } from "$lib/storeConcentrador";

  export let showModal: boolean;
  export let estudiante: EstudianteDetalle;

  const dispatch = createEventDispatcher();

  let activeTab: "personal" | "academic" | "contact" | "family" | "medical" =
    "personal";
  let isSaving = false;

  function closeModal() {
    showModal = false;
    dispatch("close");
  }

  /**
   * Obtiene las iniciales del estudiante a partir de su nombre.
   * Se utiliza para el avatar del modal.
   */
  function getInitials(name: string): string {
    if (!name) return "E";
    const parts = name.trim().split(" ");
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }

  // Reactive statements to update form fields when student prop changes
  $: if (estudiante) {
    eNombres.set(estudiante.nombres);
    eInstitucion_externa.set(estudiante.institucion_externa);
    eGenero.set(estudiante.genero);
    eFecnac.set(estudiante.fecnac);
    eEdad.set(estudiante.edad);
    eNivel.set(estudiante.nivel);
    eGrado.set(estudiante.grado);
    eNumero.set(estudiante.numero);
    eAnio.set(estudiante.anio);
    ePass.set(estudiante.pass);
    eActivo.set(estudiante.activo);
    eBanda.set(estudiante.banda);
    eHED.set(estudiante.HED);
    eIdacudiente.set(estudiante.idacudiente);
    eAcudiente.set(estudiante.acudiente);
    eTelefono1.set(estudiante.telefono1);
    eTelefono2.set(estudiante.telefono2);
    eDireccion.set(estudiante.direccion);
    eEmail_estudiante.set(estudiante.email_estudiante);
    eEmail_acudiente.set(estudiante.email_acudiente);
    eDesertor.set(estudiante.desertor);
    eOtraInformacion.set(estudiante.otraInformacion);
    eEstado.set(estudiante.estado);
    eYear.set(estudiante.year);
    eLugar.set(estudiante.lugar);
    eSisben.set(estudiante.sisben);
    eEstrato.set(estudiante.estrato);
    eLugarNacimiento.set(estudiante.lugarNacimiento);
    eLugarExpedicion.set(estudiante.lugarExpedicion);
    eFechaExpedicion.set(estudiante.fechaExpedicion);
    eTdei.set(estudiante.tdei);
    eVictimaConflicto.set(estudiante.victimaConflicto);
    eLugarDesplazamiento.set(estudiante.lugarDesplazamiento);
    eFechaDesplazamiento.set(estudiante.fechaDesplazamiento);
    eEtnia.set(estudiante.etnia);
    eTipoSangre.set(estudiante.tipoSangre);
    eEps.set(estudiante.eps);
    ePadre.set(estudiante.padre);
    ePadreid.set(estudiante.padreid);
    eTelefonopadre.set(estudiante.telefonopadre);
    eOcupacionpadre.set(estudiante.ocupacionpadre);
    eMadre.set(estudiante.madre);
    eMadreid.set(estudiante.madreid);
    eTelefonomadre.set(estudiante.telefonomadre);
    eOcupacionmadre.set(estudiante.ocupacionmadre);
    eParentesco.set(estudiante.parentesco);
    eDiscapacidad.set(estudiante.discapacidad);
    eTelefono_acudiente.set(estudiante.telefono_acudiente);
    eEanterior.set(estudiante.eanterior);
    eSede.set(estudiante.sede);
  }

  /**
   * Procesa el envío del formulario.
   * Actualmente simula la persistencia con un retardo.
   */
  async function handleSubmit() {
    isSaving = true;
    // Simular operación de guardado
    await new Promise((resolve) => setTimeout(resolve, 1000));
    console.log("Saving student changes...");
    isSaving = false;
    closeModal();
  }

  // Enterprise-grade glassmorphism input system with refined aesthetics
  const INPUT_CLASSES =
    "peer block w-full rounded-xl border-2 border-white/30 dark:border-white/15 shadow-[0_8px_16px_rgba(0,0,0,0.08),0_2px_4px_rgba(0,0,0,0.04)] bg-gradient-to-br from-white/25 to-white/15 dark:from-gray-800/25 dark:to-gray-800/15 backdrop-blur-xl px-4 py-3.5 text-base font-medium text-gray-900 dark:text-white placeholder-transparent focus:border-indigo-400/80 focus:bg-gradient-to-br focus:from-white/35 focus:to-white/25 dark:focus:from-gray-800/35 dark:focus:to-gray-800/25 focus:ring-4 focus:ring-indigo-500/20 focus:shadow-[0_12px_24px_rgba(99,102,241,0.15),0_4px_8px_rgba(99,102,241,0.1)] transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] hover:shadow-[0_12px_20px_rgba(0,0,0,0.12),0_4px_6px_rgba(0,0,0,0.06)] hover:border-white/45 dark:hover:border-white/25 hover:scale-[1.01] active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100";

  const LABEL_CLASSES =
    "block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2 transition-colors duration-200";
</script>

{#if showModal}
  <!-- Enhanced backdrop with stronger blur -->
  <div
    transition:fade={{ duration: 200 }}
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 font-sans bg-gradient-to-br from-indigo-900/40 via-purple-900/30 to-pink-900/40 backdrop-blur-3xl"
    on:click={closeModal}
    on:keydown={(e) => e.key === "Escape" && closeModal()}
    role="presentation"
  >
    <!-- Premium modal container with enhanced glassmorphism -->
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <div
      transition:scale={{
        duration: 350,
        start: 0.92,
        opacity: 0,
        easing: cubicOut,
      }}
      class="relative w-full max-w-6xl max-h-[95vh] flex flex-col rounded-3xl shadow-[0_25px_80px_rgba(99,102,241,0.35),0_15px_40px_rgba(139,92,246,0.25)] backdrop-blur-2xl bg-white/20 dark:bg-gray-900/20 text-gray-900 dark:text-gray-100 overflow-hidden border-2 border-white/30 dark:border-white/15 premium-glass"
      on:click|stopPropagation
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-title"
      aria-describedby="modal-description"
      tabindex="-1"
    >
      <!-- Premium glassmorphism header -->
      <div
        class="flex-none relative overflow-hidden border-b border-white/20 dark:border-white/10"
      >
        <!-- Animated gradient background layer -->
        <div
          class="absolute inset-0 bg-gradient-to-r from-indigo-500/30 via-purple-500/30 to-pink-500/30 dark:from-indigo-500/40 dark:via-purple-500/40 dark:to-pink-500/40 animated-gradient"
        ></div>

        <!-- Glassmorphism content layer -->
        <div
          class="relative flex items-center justify-between px-8 py-6 bg-white/10 dark:bg-gray-900/10 backdrop-blur-xl"
        >
          <div class="flex items-center gap-5">
            <!-- Student initials badge with gradient and enhanced shadow -->
            <div
              class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-700 shadow-[0_12px_24px_rgba(99,102,241,0.4),0_4px_8px_rgba(99,102,241,0.2)] ring-4 ring-indigo-500/15 transform hover:scale-105 hover:shadow-[0_16px_32px_rgba(99,102,241,0.5)] transition-all duration-400 cursor-default"
              in:scale={{
                duration: 450,
                delay: 120,
                start: 0.4,
                easing: cubicOut,
              }}
              role="img"
              aria-label="Iniciales del estudiante: {getInitials(
                estudiante.nombres,
              )}"
            >
              <span
                class="text-2xl font-black text-white tracking-wide drop-shadow-lg"
              >
                {getInitials(estudiante.nombres)}
              </span>
            </div>

            <!-- Title section with improved typography hierarchy -->
            <div class="flex flex-col gap-1">
              <div class="flex items-center gap-3">
                <span
                  class="material-symbols-rounded text-indigo-600 dark:text-indigo-400 text-3xl drop-shadow-sm"
                  aria-hidden="true">person_edit</span
                >
                <h3
                  id="modal-title"
                  class="text-lg font-semibold text-gray-700 dark:text-gray-400 tracking-wide"
                >
                  Detalles del Estudiante
                </h3>
              </div>
              <p
                id="modal-description"
                class="text-2xl md:text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 dark:from-indigo-400 dark:via-purple-400 dark:to-pink-400 leading-tight drop-shadow-sm"
              >
                {estudiante.nombres}
              </p>
            </div>
          </div>

          <!-- Enhanced close button with improved accessibility -->
          <button
            type="button"
            on:click={closeModal}
            class="group relative p-3.5 rounded-2xl text-gray-600 hover:text-white dark:text-gray-300 dark:hover:text-white transition-all duration-300 overflow-hidden hover:shadow-[0_8px_24px_rgba(99,102,241,0.35)] focus:outline-none focus:ring-4 focus:ring-indigo-500/30 flex-shrink-0"
            title="Cerrar (Esc)"
            aria-label="Cerrar ventana modal"
          >
            <div
              class="absolute inset-0 bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
              aria-hidden="true"
            ></div>
            <span
              class="material-symbols-rounded text-2xl relative z-10 transform group-hover:rotate-90 transition-transform duration-300"
              aria-hidden="true">close</span
            >
          </button>
        </div>
      </div>

      <!-- Glassmorphism tab navigation -->
      <div
        class="flex-none relative border-b border-white/20 dark:border-white/10 bg-white/10 dark:bg-gray-900/10 backdrop-blur-xl"
      >
        <div
          class="flex px-8 overflow-x-auto scrollbar-hide"
          aria-label="Tabs"
          role="tablist"
        >
          <!-- Tab: Personal -->
          <button
            role="tab"
            type="button"
            aria-selected={activeTab === "personal"}
            aria-controls="personal-panel"
            on:click={() => (activeTab = "personal")}
            class="group relative min-w-fit flex items-center gap-3 py-4 px-6 font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 rounded-t-xl {activeTab ===
            'personal'
              ? 'text-indigo-600 dark:text-indigo-400 bg-white/5 dark:bg-white/5'
              : 'text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-white/5'}"
          >
            <span
              class="material-symbols-rounded text-[1.375rem] transition-transform duration-300 {activeTab ===
              'personal'
                ? 'scale-110 drop-shadow-sm'
                : 'group-hover:scale-110'}"
              aria-hidden="true">person</span
            >
            <span class="whitespace-nowrap tracking-wide">Personal</span>
            {#if activeTab === "personal"}
              <div
                in:scale={{ duration: 250, start: 0.9, opacity: 0 }}
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-indigo-500 via-indigo-600 to-purple-600 shadow-[0_-2px_6px_rgba(99,102,241,0.5)]"
              ></div>
            {/if}
          </button>

          <!-- Tab: Academic -->
          <button
            role="tab"
            type="button"
            aria-selected={activeTab === "academic"}
            aria-controls="academic-panel"
            on:click={() => (activeTab = "academic")}
            class="group relative min-w-fit flex items-center gap-3 py-4 px-6 font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 rounded-t-xl {activeTab ===
            'academic'
              ? 'text-emerald-600 dark:text-emerald-400 bg-white/5 dark:bg-white/5'
              : 'text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-white/5'}"
          >
            <span
              class="material-symbols-rounded text-[1.375rem] transition-transform duration-300 {activeTab ===
              'academic'
                ? 'scale-110 drop-shadow-sm'
                : 'group-hover:scale-110'}"
              aria-hidden="true">school</span
            >
            <span class="whitespace-nowrap tracking-wide">Académico</span>
            {#if activeTab === "academic"}
              <div
                in:scale={{ duration: 250, start: 0.9, opacity: 0 }}
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-500 via-green-600 to-teal-600 shadow-[0_-2px_6px_rgba(16,185,129,0.5)]"
              ></div>
            {/if}
          </button>

          <!-- Tab: Contact -->
          <button
            role="tab"
            type="button"
            aria-selected={activeTab === "contact"}
            aria-controls="contact-panel"
            on:click={() => (activeTab = "contact")}
            class="group relative min-w-fit flex items-center gap-3 py-4 px-6 font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 rounded-t-xl {activeTab ===
            'contact'
              ? 'text-blue-600 dark:text-blue-400 bg-white/5 dark:bg-white/5'
              : 'text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-white/5'}"
          >
            <span
              class="material-symbols-rounded text-[1.375rem] transition-transform duration-300 {activeTab ===
              'contact'
                ? 'scale-110 drop-shadow-sm'
                : 'group-hover:scale-110'}"
              aria-hidden="true">contact_mail</span
            >
            <span class="whitespace-nowrap tracking-wide">Contacto</span>
            {#if activeTab === "contact"}
              <div
                in:scale={{ duration: 250, start: 0.9, opacity: 0 }}
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-blue-500 via-cyan-600 to-sky-600 shadow-[0_-2px_6px_rgba(59,130,246,0.5)]"
              ></div>
            {/if}
          </button>

          <!-- Tab: Family -->
          <button
            role="tab"
            type="button"
            aria-selected={activeTab === "family"}
            aria-controls="family-panel"
            on:click={() => (activeTab = "family")}
            class="group relative min-w-fit flex items-center gap-3 py-4 px-6 font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 rounded-t-xl {activeTab ===
            'family'
              ? 'text-pink-600 dark:text-pink-400 bg-white/5 dark:bg-white/5'
              : 'text-gray-500 dark:text-gray-400 hover:text-pink-600 dark:hover:text-pink-400 hover:bg-white/5'}"
          >
            <span
              class="material-symbols-rounded text-[1.375rem] transition-transform duration-300 {activeTab ===
              'family'
                ? 'scale-110 drop-shadow-sm'
                : 'group-hover:scale-110'}"
              aria-hidden="true">family_restroom</span
            >
            <span class="whitespace-nowrap tracking-wide">Familiar</span>
            {#if activeTab === "family"}
              <div
                in:scale={{ duration: 250, start: 0.9, opacity: 0 }}
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-pink-500 via-rose-600 to-red-600 shadow-[0_-2px_6px_rgba(236,72,153,0.5)]"
              ></div>
            {/if}
          </button>

          <!-- Tab: Medical -->
          <button
            role="tab"
            type="button"
            aria-selected={activeTab === "medical"}
            aria-controls="medical-panel"
            on:click={() => (activeTab = "medical")}
            class="group relative min-w-fit flex items-center gap-3 py-4 px-6 font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 rounded-t-xl {activeTab ===
            'medical'
              ? 'text-orange-600 dark:text-orange-400 bg-white/5 dark:bg-white/5'
              : 'text-gray-500 dark:text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-white/5'}"
          >
            <span
              class="material-symbols-rounded text-[1.375rem] transition-transform duration-300 {activeTab ===
              'medical'
                ? 'scale-110 drop-shadow-sm'
                : 'group-hover:scale-110'}"
              aria-hidden="true">health_and_safety</span
            >
            <span class="whitespace-nowrap tracking-wide">Médico</span>
            {#if activeTab === "medical"}
              <div
                in:scale={{ duration: 250, start: 0.9, opacity: 0 }}
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-orange-500 via-amber-600 to-yellow-600 shadow-[0_-2px_6px_rgba(249,115,22,0.5)]"
              ></div>
            {/if}
          </button>
        </div>
      </div>

      <div class="flex-grow overflow-y-auto custom-scrollbar px-8 py-6">
        <form on:submit|preventDefault={handleSubmit} class="space-y-8">
          {#if activeTab === "personal"}
            <div transition:fade class="space-y-6">
              <fieldset
                class="border border-white/20 dark:border-white/10 rounded-2xl p-6 shadow-[0_8px_32px_rgba(139,92,246,0.2)] bg-white/10 dark:bg-gray-900/10 backdrop-blur-xl glass-panel"
              >
                <legend
                  class="text-2xl font-extrabold text-gray-900 dark:text-white px-3 mb-4 border-b-2 border-indigo-400/50 inline-block pb-1"
                >
                  Información Personal
                </legend>
                <div
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4"
                >
                  <div>
                    <label
                      for="eNombres"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-blue-500"
                        >person</span
                      >Nombres</label
                    >
                    <input
                      type="text"
                      id="eNombres"
                      bind:value={$eNombres}
                      placeholder="Nombres completos"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eGenero"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-purple-500"
                        >wc</span
                      >Género</label
                    >
                    <select
                      id="eGenero"
                      bind:value={$eGenero}
                      class={INPUT_CLASSES}
                    >
                      <option value="M">Masculino</option>
                      <option value="F">Femenino</option>
                    </select>
                  </div>
                  <div>
                    <label
                      for="eFecnac"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-teal-500"
                        >calendar_month</span
                      >Fecha de Nacimiento</label
                    >
                    <input
                      type="date"
                      id="eFecnac"
                      bind:value={$eFecnac}
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eEdad"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-orange-500"
                        >numbers</span
                      >Edad</label
                    >
                    <input
                      type="number"
                      id="eEdad"
                      bind:value={$eEdad}
                      placeholder="Edad"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eLugarNacimiento"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-green-500"
                        >location_on</span
                      >Lugar de Nacimiento</label
                    >
                    <input
                      type="text"
                      id="eLugarNacimiento"
                      bind:value={$eLugarNacimiento}
                      placeholder="Ciudad, País"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eTdei"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-indigo-500"
                        >badge</span
                      >Tipo Documento Identidad</label
                    >
                    <input
                      type="text"
                      id="eTdei"
                      bind:value={$eTdei}
                      placeholder="Tipo de documento"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eFechaExpedicion"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-teal-500"
                        >today</span
                      >Fecha Expedición Documento</label
                    >
                    <input
                      type="date"
                      id="eFechaExpedicion"
                      bind:value={$eFechaExpedicion}
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eLugarExpedicion"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-green-500"
                        >location_city</span
                      >Lugar Expedición Documento</label
                    >
                    <input
                      type="text"
                      id="eLugarExpedicion"
                      bind:value={$eLugarExpedicion}
                      placeholder="Ciudad, País"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eEstado"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-purple-500"
                        >bookmark</span
                      >Estado</label
                    >
                    <select
                      id="eEstado"
                      bind:value={$eEstado}
                      class={INPUT_CLASSES}
                    >
                      <option value="Activo">Activo</option>
                      <option value="Inactivo">Inactivo</option>
                    </select>
                  </div>
                  <div>
                    <label
                      for="eActivo"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-green-500"
                        >toggle_on</span
                      >Activo (S/N)</label
                    >
                    <select
                      id="eActivo"
                      bind:value={$eActivo}
                      class={INPUT_CLASSES}
                    >
                      <option value="S">S</option>
                      <option value="N">N</option>
                    </select>
                  </div>
                  <div>
                    <label
                      for="eDesertor"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-red-500"
                        >do_not_disturb_on</span
                      >Desertor (S/N)</label
                    >
                    <select
                      id="eDesertor"
                      bind:value={$eDesertor}
                      class={INPUT_CLASSES}
                    >
                      <option value="S">S</option>
                      <option value="N">N</option>
                    </select>
                  </div>
                  <div>
                    <label
                      for="eOtraInformacion"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-blue-500"
                        >info</span
                      >Otra Información</label
                    >
                    <input
                      type="text"
                      id="eOtraInformacion"
                      bind:value={$eOtraInformacion}
                      placeholder="Información adicional"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eLugar"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-green-500"
                        >home</span
                      >Lugar de Residencia (Barrio/Vereda)</label
                    >
                    <input
                      type="text"
                      id="eLugar"
                      bind:value={$eLugar}
                      placeholder="Barrio o Vereda"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eSisben"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-indigo-500"
                        >description</span
                      >Sisben</label
                    >
                    <input
                      type="text"
                      id="eSisben"
                      bind:value={$eSisben}
                      placeholder="Puntaje o clasificación Sisben"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eEstrato"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-orange-500"
                        >stairs</span
                      >Estrato</label
                    >
                    <input
                      type="number"
                      id="eEstrato"
                      bind:value={$eEstrato}
                      placeholder="Estrato socioeconómico"
                      class={INPUT_CLASSES}
                    />
                  </div>
                </div>
              </fieldset>

              <fieldset
                class="border border-white/30 dark:border-gray-700/50 rounded-2xl p-6 shadow-xl bg-white/80 dark:bg-gray-900/80 glass-panel"
              >
                <legend
                  class="text-2xl font-extrabold text-gray-900 dark:text-white px-3 mb-4 border-b-2 border-red-400/50 inline-block pb-1"
                >
                  Víctima de Conflicto
                </legend>
                <div
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4"
                >
                  <div>
                    <label
                      for="eVictimaConflicto"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-red-500"
                        >gavel</span
                      >¿Es víctima de conflicto?</label
                    >
                    <select
                      id="eVictimaConflicto"
                      bind:value={$eVictimaConflicto}
                      class={INPUT_CLASSES}
                    >
                      <option value="indefinido">Seleccione</option>
                      <option value="si">Sí</option>
                      <option value="no">No</option>
                    </select>
                  </div>
                  <div>
                    <label
                      for="eLugarDesplazamiento"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-orange-500"
                        >near_me</span
                      >Lugar de Desplazamiento</label
                    >
                    <input
                      type="text"
                      id="eLugarDesplazamiento"
                      bind:value={$eLugarDesplazamiento}
                      placeholder="Lugar de desplazamiento"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eFechaDesplazamiento"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-teal-500"
                        >event</span
                      >Fecha de Desplazamiento</label
                    >
                    <input
                      type="date"
                      id="eFechaDesplazamiento"
                      bind:value={$eFechaDesplazamiento}
                      class={INPUT_CLASSES}
                    />
                  </div>
                </div>
              </fieldset>
            </div>
          {/if}

          {#if activeTab === "academic"}
            <div transition:fade class="space-y-4">
              <fieldset
                class="border border-white/30 dark:border-gray-700/50 rounded-2xl p-6 shadow-xl bg-white/80 dark:bg-gray-900/80 glass-panel"
              >
                <legend
                  class="text-2xl font-extrabold text-gray-900 dark:text-white px-3 mb-4 border-b-2 border-indigo-400/50 inline-block pb-1"
                >
                  Información Académica
                </legend>
                <div
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4"
                >
                  <div>
                    <label
                      for="eNivel"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-blue-500"
                        >school</span
                      >Nivel</label
                    >
                    <input
                      type="text"
                      id="eNivel"
                      bind:value={$eNivel}
                      placeholder="Nivel educativo"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eGrado"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-green-500"
                        >grade</span
                      >Grado</label
                    >
                    <input
                      type="text"
                      id="eGrado"
                      bind:value={$eGrado}
                      placeholder="Grado actual"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eNumero"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-purple-500"
                        >format_list_numbered</span
                      >Número de Lista</label
                    >
                    <input
                      type="number"
                      id="eNumero"
                      bind:value={$eNumero}
                      placeholder="Número en lista"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eAnio"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-teal-500"
                        >calendar_today</span
                      >Año</label
                    >
                    <input
                      type="text"
                      id="eAnio"
                      bind:value={$eAnio}
                      placeholder="Año de curso"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="ePass"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-red-500"
                        >lock</span
                      >Contraseña</label
                    >
                    <input
                      type="password"
                      id="ePass"
                      bind:value={$ePass}
                      placeholder="Contraseña del estudiante"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eBanda"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-orange-500"
                        >music_note</span
                      >Banda</label
                    >
                    <input
                      type="text"
                      id="eBanda"
                      bind:value={$eBanda}
                      placeholder="Banda"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eEanterior"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-indigo-500"
                        >history_edu</span
                      >Escuela Anterior</label
                    >
                    <input
                      type="text"
                      id="eEanterior"
                      bind:value={$eEanterior}
                      placeholder="Escuela de procedencia"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eSede"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-green-500"
                        >location_on</span
                      >Sede</label
                    >
                    <input
                      type="text"
                      id="eSede"
                      bind:value={$eSede}
                      placeholder="Sede"
                      class={INPUT_CLASSES}
                    />
                  </div>
                </div>
              </fieldset>
            </div>
          {/if}

          {#if activeTab === "contact"}
            <div transition:fade class="space-y-4">
              <fieldset
                class="border border-white/30 dark:border-gray-700/50 rounded-2xl p-6 shadow-xl bg-white/80 dark:bg-gray-900/80 glass-panel"
              >
                <legend
                  class="text-2xl font-extrabold text-gray-900 dark:text-white px-3 mb-4 border-b-2 border-indigo-400/50 inline-block pb-1"
                >
                  Información de Contacto
                </legend>
                <div
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4"
                >
                  <div>
                    <label
                      for="eTelefono1"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-blue-500"
                        >phone</span
                      >Teléfono 1</label
                    >
                    <input
                      type="text"
                      id="eTelefono1"
                      bind:value={$eTelefono1}
                      placeholder="Número de teléfono principal"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eTelefono2"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-blue-400"
                        >phone_iphone</span
                      >Teléfono 2</label
                    >
                    <input
                      type="text"
                      id="eTelefono2"
                      bind:value={$eTelefono2}
                      placeholder="Número de teléfono secundario"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eDireccion"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-green-500"
                        >location_on</span
                      >Dirección</label
                    >
                    <input
                      type="text"
                      id="eDireccion"
                      bind:value={$eDireccion}
                      placeholder="Dirección de residencia"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eEmail_estudiante"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-red-500"
                        >email</span
                      >Email Estudiante</label
                    >
                    <input
                      type="email"
                      id="eEmail_estudiante"
                      bind:value={$eEmail_estudiante}
                      placeholder="Email del estudiante"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eEmail_acudiente"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-red-400"
                        >contact_mail</span
                      >Email Acudiente</label
                    >
                    <input
                      type="email"
                      id="eEmail_acudiente"
                      bind:value={$eEmail_acudiente}
                      placeholder="Email del acudiente"
                      class={INPUT_CLASSES}
                    />
                  </div>
                </div>
              </fieldset>
            </div>
          {/if}

          {#if activeTab === "family"}
            <div transition:fade class="space-y-4">
              <fieldset
                class="border border-white/30 dark:border-gray-700/50 rounded-2xl p-6 shadow-xl bg-white/80 dark:bg-gray-900/80 glass-panel"
              >
                <legend
                  class="text-2xl font-extrabold text-gray-900 dark:text-white px-3 mb-4 border-b-2 border-indigo-400/50 inline-block pb-1"
                >
                  Información de Acudiente y Padres
                </legend>
                <div
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4"
                >
                  <div>
                    <label
                      for="eAcudiente"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-blue-500"
                        >supervisor_account</span
                      >Acudiente</label
                    >
                    <input
                      type="text"
                      id="eAcudiente"
                      bind:value={$eAcudiente}
                      placeholder="Nombre del acudiente"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eIdacudiente"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-indigo-500"
                        >id_card</span
                      >ID Acudiente</label
                    >
                    <input
                      type="text"
                      id="eIdacudiente"
                      bind:value={$eIdacudiente}
                      placeholder="Identificación del acudiente"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eTelefono_acudiente"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-teal-500"
                        >contact_phone</span
                      >Teléfono Acudiente</label
                    >
                    <input
                      type="text"
                      id="eTelefono_acudiente"
                      bind:value={$eTelefono_acudiente}
                      placeholder="Teléfono del acudiente"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eParentesco"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-purple-500"
                        >groups</span
                      >Parentesco</label
                    >
                    <input
                      type="text"
                      id="eParentesco"
                      bind:value={$eParentesco}
                      placeholder="Parentesco con el estudiante"
                      class={INPUT_CLASSES}
                    />
                  </div>
                </div>
                <div
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6 border-t border-gray-200/50 dark:border-gray-700/50 pt-6"
                >
                  <div>
                    <label
                      for="ePadre"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-blue-600"
                        >male</span
                      >Padre</label
                    >
                    <input
                      type="text"
                      id="ePadre"
                      bind:value={$ePadre}
                      placeholder="Nombre del padre"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="ePadreid"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-indigo-600"
                        >badge</span
                      >ID Padre</label
                    >
                    <input
                      type="text"
                      id="ePadreid"
                      bind:value={$ePadreid}
                      placeholder="Identificación del padre"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eTelefonopadre"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-teal-600"
                        >call</span
                      >Teléfono Padre</label
                    >
                    <input
                      type="text"
                      id="eTelefonopadre"
                      bind:value={$eTelefonopadre}
                      placeholder="Teléfono del padre"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eOcupacionpadre"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-orange-600"
                        >work</span
                      >Ocupación Padre</label
                    >
                    <input
                      type="text"
                      id="eOcupacionpadre"
                      bind:value={$eOcupacionpadre}
                      placeholder="Ocupación del padre"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eMadre"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-pink-500"
                        >female</span
                      >Madre</label
                    >
                    <input
                      type="text"
                      id="eMadre"
                      bind:value={$eMadre}
                      placeholder="Nombre de la madre"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eMadreid"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-indigo-500"
                        >badge</span
                      >ID Madre</label
                    >
                    <input
                      type="text"
                      id="eMadreid"
                      bind:value={$eMadreid}
                      placeholder="Identificación de la madre"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eTelefonomadre"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-teal-500"
                        >call</span
                      >Teléfono Madre</label
                    >
                    <input
                      type="text"
                      id="eTelefonomadre"
                      bind:value={$eTelefonomadre}
                      placeholder="Teléfono de la madre"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eOcupacionmadre"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-orange-500"
                        >work</span
                      >Ocupación Madre</label
                    >
                    <input
                      type="text"
                      id="eOcupacionmadre"
                      bind:value={$eOcupacionmadre}
                      placeholder="Ocupación de la madre"
                      class={INPUT_CLASSES}
                    />
                  </div>
                </div>
              </fieldset>
            </div>
          {/if}
          {#if activeTab === "medical"}
            <div transition:fade class="space-y-4">
              <fieldset
                class="border border-white/30 dark:border-gray-700/50 rounded-2xl p-6 shadow-xl bg-white/80 dark:bg-gray-900/80 glass-panel"
              >
                <legend
                  class="text-2xl font-extrabold text-gray-900 dark:text-white px-3 mb-4 border-b-2 border-indigo-400/50 inline-block pb-1"
                >
                  Información Médica
                </legend>
                <div
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4"
                >
                  <div>
                    <label
                      for="eTipoSangre"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-red-500"
                        >bloodtype</span
                      >Tipo de Sangre</label
                    >
                    <input
                      type="text"
                      id="eTipoSangre"
                      bind:value={$eTipoSangre}
                      placeholder="Ej: O+"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eEps"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-blue-500"
                        >local_hospital</span
                      >EPS</label
                    >
                    <input
                      type="text"
                      id="eEps"
                      bind:value={$eEps}
                      placeholder="Nombre de la EPS"
                      class={INPUT_CLASSES}
                    />
                  </div>
                  <div>
                    <label
                      for="eDiscapacidad"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-purple-500"
                        >accessible</span
                      >Discapacidad</label
                    >
                    <select
                      id="ediscapacidad"
                      bind:value={$eDiscapacidad}
                      class={INPUT_CLASSES}
                    >
                      <option value="" disabled>Seleccione una opción</option>
                      <option value="NO APLICA">NO APLICA</option>
                      <option value="DISCAPACIDAD FÍSICA"
                        >DISCAPACIDAD FÍSICA</option
                      >
                      <option value="DISCAPACIDAD AUDITIVA"
                        >DISCAPACIDAD AUDITIVA</option
                      >
                      <option value="USUARIO DE LENGUA DE SEÑAS COLOMBIANA">
                        USUARIO DE LENGUA DE SEÑAS COLOMBIANA
                      </option>
                      <option
                        value="DISCAPACIDAD AUDITIVA USUARIO DEL CASTELLANO"
                      >
                        DISCAPACIDAD AUDITIVA USUARIO DEL CASTELLANO
                      </option>
                      <option
                        value="DISCAPACIDAD VISUAL BAJA VISIÓN IRREVERSIBLE"
                      >
                        DISCAPACIDAD VISUAL BAJA VISIÓN IRREVERSIBLE
                      </option>
                      <option value="DISCAPACIDAD VISUAL CEGUERA SORDOCEGUERA">
                        DISCAPACIDAD VISUAL CEGUERA SORDOCEGUERA
                      </option>
                      <option value="DISCAPACIDAD INTELECTUAL"
                        >DISCAPACIDAD INTELECTUAL</option
                      >
                      <option value="TRASTORNO DEL ESPECTRO AUTISTA">
                        TRASTORNO DEL ESPECTRO AUTISTA
                      </option>
                      <option value="DISCAPACIDAD PSICOSOCIAL (MENTAL)">
                        DISCAPACIDAD PSICOSOCIAL (MENTAL)
                      </option>
                      <option value="DISCAPACIDAD MULTIPLE"
                        >DISCAPACIDAD MULTIPLE</option
                      >
                      <option value="DISCAPACIDAD SISTEMICA">SISTEMICA</option>
                      <option value="MULTIPLE">MULTIPLE</option>
                    </select>
                  </div>
                  <div>
                    <label
                      for="eHED"
                      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                      ><span
                        class="material-symbols-rounded text-base text-teal-500"
                        >medical_information</span
                      >HED</label
                    >
                    <input
                      type="text"
                      id="eHED"
                      bind:value={$eHED}
                      placeholder="Ingrese HED"
                      class={INPUT_CLASSES}
                    />
                  </div>
                </div>
              </fieldset>
              <fieldset
                class="border border-white/30 dark:border-gray-700/50 rounded-2xl p-6 shadow-xl bg-white/80 dark:bg-gray-900/80 glass-panel"
              >
                <legend
                  class="text-2xl font-extrabold text-gray-900 dark:text-white px-3 mb-4 border-b-2 border-purple-400/50 inline-block pb-1"
                >
                  Etnia o Identificación Social
                </legend>
                <div class="mt-4">
                  <label
                    for="eetnia"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-2"
                    ><span
                      class="material-symbols-rounded text-base text-purple-500"
                      >diversity_3</span
                    >Seleccione la etnia o distinción social</label
                  >
                  <select
                    id="eetnia"
                    bind:value={$eEtnia}
                    class={INPUT_CLASSES}
                  >
                    <option value="" disabled>Seleccione una opción</option>
                    {#each $etnias as group}
                      <optgroup label={group.label}>
                        {#each group.options as option}
                          <option value={option}>{option}</option>
                        {/each}
                      </optgroup>
                    {/each}
                  </select>
                </div>
              </fieldset>
            </div>
          {/if}
        </form>
      </div>

      <!-- Premium footer with enhanced buttons -->
      <div
        class="flex-none flex justify-end gap-4 p-8 border-t-2 border-gray-100 dark:border-gray-800 bg-gradient-to-t from-white/90 to-white/70 dark:from-gray-900/90 dark:to-gray-900/70 backdrop-blur-sm"
      >
        <!-- Cancel Button -->
        <button
          type="button"
          on:click={closeModal}
          disabled={isSaving}
          class="px-6 py-3 rounded-2xl text-gray-600 dark:text-gray-300 font-semibold hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-300 border border-transparent hover:border-gray-200 dark:hover:border-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-500/20 disabled:opacity-50"
        >
          Cancelar
        </button>

        <!-- Save Button with animated gradient -->
        <button
          type="submit"
          on:click={handleSubmit}
          disabled={isSaving}
          class="group relative px-8 py-3 rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white font-bold shadow-[0_8px_20px_rgba(99,102,241,0.35)] hover:shadow-[0_12px_28px_rgba(99,102,241,0.5)] transform hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 overflow-hidden focus:outline-none focus:ring-4 focus:ring-indigo-500/40 disabled:opacity-75 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
        >
          <!-- Shimmer effect overlay -->
          <div
            class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000 ease-in-out"
          ></div>

          <div class="relative z-10 flex items-center justify-center gap-3">
            {#if isSaving}
              <svg
                class="w-5 h-5 animate-spin"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              <span>Guardando...</span>
            {:else}
              <span
                class="material-symbols-rounded text-xl transition-transform duration-300 group-hover:scale-110"
                aria-hidden="true">save</span
              >
              <span>Guardar Cambios</span>
            {/if}
          </div>
        </button>
      </div>
    </div>
  </div>
{/if}

<style>
  /* Material Symbols Font */
  @import url("https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200");

  /* Enterprise-grade Premium Glass Effect with multi-layer depth */
  .premium-glass {
    box-shadow:
      0 25px 70px -20px rgba(0, 0, 0, 0.35),
      0 12px 24px -10px rgba(0, 0, 0, 0.25),
      0 4px 12px rgba(0, 0, 0, 0.15),
      0 0 0 1px rgba(255, 255, 255, 0.12) inset,
      0 1px 0 0 rgba(255, 255, 255, 0.15) inset,
      0 0 30px 0 rgba(139, 92, 246, 0.12);
  }

  /* Glass panel refinement for fieldsets */
  .glass-panel {
    position: relative;
    overflow: hidden;
  }

  .glass-panel::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(
      90deg,
      transparent,
      rgba(255, 255, 255, 0.6),
      transparent
    );
    opacity: 0.5;
  }

  /* Enhanced Custom Scrollbar */
  .custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(139, 92, 246, 0.3) transparent;
  }

  .custom-scrollbar::-webkit-scrollbar {
    width: 10px;
    height: 10px;
  }

  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
    border-radius: 10px;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(
      180deg,
      rgba(139, 92, 246, 0.4),
      rgba(168, 85, 247, 0.4)
    );
    border-radius: 10px;
    border: 2px solid transparent;
    background-clip: padding-box;
    transition: background 0.3s ease;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(
      180deg,
      rgba(139, 92, 246, 0.6),
      rgba(168, 85, 247, 0.6)
    );
  }

  .custom-scrollbar::-webkit-scrollbar-thumb:active {
    background: linear-gradient(
      180deg,
      rgba(139, 92, 246, 0.8),
      rgba(168, 85, 247, 0.8)
    );
  }

  /* Hide scrollbar for tab navigation */
  .scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }

  .scrollbar-hide::-webkit-scrollbar {
    display: none;
  }

  /* Accessibility: Respect reduced motion preferences */
  @media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
      animation-duration: 0.01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.01ms !important;
    }
  }

  /* Enhanced focus-visible for keyboard navigation (WCAG AAA compliance) */
  *:focus-visible {
    outline: 3px solid rgba(99, 102, 241, 0.9);
    outline-offset: 3px;
    border-radius: 0.5rem;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
  }

  /* Ensure inputs have proper focus rings */
  input:focus-visible,
  select:focus-visible {
    outline: none;
  }

  /* Smooth font rendering */
  * {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
</style>
