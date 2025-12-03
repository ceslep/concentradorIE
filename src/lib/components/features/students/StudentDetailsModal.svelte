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

  // Get initials from student name for avatar badge
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

  // Form submission handler with loading state
  async function handleSubmit() {
    isSaving = true;
    // Simulate save operation
    await new Promise((resolve) => setTimeout(resolve, 1000));
    console.log("Saving student changes...");
    isSaving = false;
    closeModal();
  }

  // Glassmorphism input classes with premium styling
  const INPUT_CLASSES =
    "peer block w-full rounded-xl border border-white/30 dark:border-white/20 shadow-lg bg-white/20 dark:bg-gray-800/20 backdrop-blur-xl px-4 py-3.5 text-gray-900 dark:text-white placeholder-transparent focus:border-indigo-400 focus:bg-white/30 dark:focus:bg-gray-800/30 focus:ring-4 focus:ring-indigo-500/30 transition-all duration-300 ease-out hover:shadow-xl hover:bg-white/30 dark:hover:bg-gray-800/30 hover:border-white/40 dark:hover:border-white/30";

  const LABEL_CLASSES =
    "absolute left-4 -top-2.5 bg-white dark:bg-gray-900 px-2 text-sm font-medium text-gray-600 dark:text-gray-400 transition-all duration-200 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-3.5 peer-placeholder-shown:left-4 peer-focus:-top-2.5 peer-focus:left-4 peer-focus:text-sm peer-focus:text-indigo-600 dark:peer-focus:text-indigo-400 peer-focus:font-semibold";
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
    <!-- Premium modal container -->
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <div
      transition:scale={{
        duration: 300,
        start: 0.95,
        opacity: 0,
        easing: cubicOut,
      }}
      class="relative w-full max-w-6xl max-h-[95vh] flex flex-col rounded-3xl shadow-[0_0_80px_rgba(139,92,246,0.3)] backdrop-blur-2xl bg-white/20 dark:bg-gray-900/20 text-gray-900 dark:text-gray-100 overflow-hidden border border-white/30 dark:border-white/10 premium-glass"
      on:click|stopPropagation
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-title"
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
          <div class="flex items-center gap-4">
            <!-- Student initials badge with gradient -->
            <div
              class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg ring-4 ring-indigo-500/20 transform hover:scale-105 transition-transform duration-300"
              in:scale={{ duration: 400, delay: 100, start: 0.5 }}
            >
              <span class="text-2xl font-black text-white tracking-wide">
                {getInitials(estudiante.nombres)}
              </span>
            </div>

            <!-- Title section with improved typography -->
            <div class="flex flex-col">
              <div class="flex items-center gap-3">
                <span
                  class="material-symbols-rounded text-indigo-600 dark:text-indigo-400 text-3xl"
                  >person_edit</span
                >
                <h3
                  id="modal-title"
                  class="text-lg font-semibold text-gray-700 dark:text-gray-400 tracking-wide"
                >
                  Detalles del Estudiante
                </h3>
              </div>
              <p
                class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 dark:from-indigo-400 dark:via-purple-400 dark:to-pink-400 mt-1"
              >
                {estudiante.nombres}
              </p>
            </div>
          </div>

          <!-- Enhanced close button with ripple effect -->
          <button
            on:click={closeModal}
            class="group relative p-3 rounded-2xl text-gray-600 hover:text-white dark:text-gray-300 dark:hover:text-white transition-all duration-300 overflow-hidden hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/30"
            title="Cerrar (Esc)"
            aria-label="Cerrar modal"
          >
            <div
              class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
            ></div>
            <span
              class="material-symbols-rounded text-2xl relative z-10 transform group-hover:rotate-90 transition-transform duration-300"
              >close</span
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
          <!-- Personal Tab -->
          <button
            role="tab"
            aria-selected={activeTab === "personal"}
            aria-controls="personal-panel"
            on:click={() => (activeTab = "personal")}
            class="group relative min-w-fit flex items-center gap-2.5 py-4 px-5 font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 rounded-t-xl {activeTab ===
            'personal'
              ? 'text-indigo-700 dark:text-indigo-300'
              : 'text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400'}"
          >
            <span
              class="material-symbols-rounded text-xl transition-transform duration-300 {activeTab ===
              'personal'
                ? 'scale-110'
                : 'group-hover:scale-105'}">person</span
            >
            <span class="whitespace-nowrap">Personal</span>
            <!-- Active indicator -->
            {#if activeTab === "personal"}
              <div
                in:scale={{ duration: 200, start: 0.8 }}
                class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-t-full shadow-lg shadow-indigo-500/50"
              ></div>
            {/if}
          </button>

          <!-- Academic Tab -->
          <button
            role="tab"
            aria-selected={activeTab === "academic"}
            aria-controls="academic-panel"
            on:click={() => (activeTab = "academic")}
            class="group relative min-w-fit flex items-center gap-2.5 py-4 px-5 font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 rounded-t-xl {activeTab ===
            'academic'
              ? 'text-indigo-700 dark:text-indigo-300'
              : 'text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400'}"
          >
            <span
              class="material-symbols-rounded text-xl transition-transform duration-300 {activeTab ===
              'academic'
                ? 'scale-110'
                : 'group-hover:scale-105'}">school</span
            >
            <span class="whitespace-nowrap">Académico</span>
            {#if activeTab === "academic"}
              <div
                in:scale={{ duration: 200, start: 0.8 }}
                class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-green-500 to-emerald-500 rounded-t-full shadow-lg shadow-green-500/50"
              ></div>
            {/if}
          </button>

          <!-- Contact Tab -->
          <button
            role="tab"
            aria-selected={activeTab === "contact"}
            aria-controls="contact-panel"
            on:click={() => (activeTab = "contact")}
            class="group relative min-w-fit flex items-center gap-2.5 py-4 px-5 font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 rounded-t-xl {activeTab ===
            'contact'
              ? 'text-indigo-700 dark:text-indigo-300'
              : 'text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400'}"
          >
            <span
              class="material-symbols-rounded text-xl transition-transform duration-300 {activeTab ===
              'contact'
                ? 'scale-110'
                : 'group-hover:scale-105'}">contact_mail</span
            >
            <span class="whitespace-nowrap">Contacto</span>
            {#if activeTab === "contact"}
              <div
                in:scale={{ duration: 200, start: 0.8 }}
                class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-t-full shadow-lg shadow-blue-500/50"
              ></div>
            {/if}
          </button>

          <!-- Family Tab -->
          <button
            role="tab"
            aria-selected={activeTab === "family"}
            aria-controls="family-panel"
            on:click={() => (activeTab = "family")}
            class="group relative min-w-fit flex items-center gap-2.5 py-4 px-5 font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 rounded-t-xl {activeTab ===
            'family'
              ? 'text-indigo-700 dark:text-indigo-300'
              : 'text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400'}"
          >
            <span
              class="material-symbols-rounded text-xl transition-transform duration-300 {activeTab ===
              'family'
                ? 'scale-110'
                : 'group-hover:scale-105'}">family_restroom</span
            >
            <span class="whitespace-nowrap">Familiar</span>
            {#if activeTab === "family"}
              <div
                in:scale={{ duration: 200, start: 0.8 }}
                class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-pink-500 to-rose-500 rounded-t-full shadow-lg shadow-pink-500/50"
              ></div>
            {/if}
          </button>

          <!-- Medical Tab -->
          <button
            role="tab"
            aria-selected={activeTab === "medical"}
            aria-controls="medical-panel"
            on:click={() => (activeTab = "medical")}
            class="group relative min-w-fit flex items-center gap-2.5 py-4 px-5 font-semibold text-sm transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 rounded-t-xl {activeTab ===
            'medical'
              ? 'text-indigo-700 dark:text-indigo-300'
              : 'text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400'}"
          >
            <span
              class="material-symbols-rounded text-xl transition-transform duration-300 {activeTab ===
              'medical'
                ? 'scale-110'
                : 'group-hover:scale-105'}">health_and_safety</span
            >
            <span class="whitespace-nowrap">Médico</span>
            {#if activeTab === "medical"}
              <div
                in:scale={{ duration: 200, start: 0.8 }}
                class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-orange-500 rounded-t-full shadow-lg shadow-red-500/50"
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
        <!-- Cancel button -->
        <button
          type="button"
          on:click={closeModal}
          disabled={isSaving}
          class="group relative inline-flex items-center gap-2.5 px-8 py-3.5 rounded-xl font-semibold text-base shadow-lg transition-all duration-300 overflow-hidden focus:outline-none focus:ring-4 focus:ring-gray-400/30 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98]"
        >
          <div
            class="absolute inset-0 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 transition-opacity duration-300"
          ></div>
          <div
            class="absolute inset-0 bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
          ></div>
          <span
            class="material-symbols-rounded text-xl relative z-10 text-gray-700 dark:text-gray-200 transform group-hover:rotate-2 transition-transform duration-300"
            >cancel</span
          >
          <span class="relative z-10 text-gray-700 dark:text-gray-200"
            >Cerrar</span
          >
        </button>

        <!-- Save button with loading state -->
        <button
          type="submit"
          on:click={handleSubmit}
          disabled={isSaving}
          class="group relative inline-flex items-center gap-2.5 px-8 py-3.5 rounded-xl font-semibold text-base shadow-lg shadow-indigo-500/30 transition-all duration-300 overflow-hidden focus:outline-none focus:ring-4 focus:ring-indigo-500/40 disabled:opacity-70 disabled:cursor-not-allowed hover:shadow-2xl hover:shadow-indigo-500/40 transform hover:scale-[1.02] active:scale-[0.98]"
        >
          <div
            class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600"
          ></div>
          <div
            class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
          ></div>

          <!-- Loading spinner -->
          {#if isSaving}
            <svg
              class="relative z-10 w-5 h-5 text-white animate-spin"
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
          {:else}
            <span
              class="material-symbols-rounded text-xl relative z-10 text-white transform group-hover:scale-110 transition-transform duration-300"
              >save</span
            >
          {/if}

          <span class="relative z-10 text-white">
            {isSaving ? "Guardando..." : "Guardar Cambios"}
          </span>
        </button>
      </div>
    </div>
  </div>
{/if}

<style>
  /* Material Symbols Font */
  @import url("https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200");

  /* Premium Glass Effect with enhanced depth */
  .premium-glass {
    box-shadow:
      0 20px 60px -15px rgba(0, 0, 0, 0.3),
      0 8px 16px -8px rgba(0, 0, 0, 0.2),
      0 0 0 1px rgba(255, 255, 255, 0.1) inset,
      0 0 20px 0 rgba(139, 92, 246, 0.1);
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

  /* Enhanced focus visible for keyboard navigation */
  *:focus-visible {
    outline: 2px solid rgba(139, 92, 246, 0.8);
    outline-offset: 2px;
    border-radius: 0.375rem;
  }

  /* Smooth font rendering */
  * {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
</style>
