<script lang="ts">
  import { createEventDispatcher } from "svelte";
  import { fade, slide } from "svelte/transition";
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

  function closeModal() {
    showModal = false;
    dispatch("close");
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

  // Form submission handler
  function handleSubmit() {
    // Implement your save logic here
    console.log("Saving student changes...");
    closeModal();
  }

  // Define la cadena de clases de entrada que reemplaza a .input-field-md
  const INPUT_CLASSES =
    "block w-full rounded-lg border-gray-300/50 shadow-inner focus:border-indigo-500/80 focus:ring-indigo-500/80 dark:bg-gray-800/60 dark:text-gray-200 dark:border-gray-600/50 transition-colors duration-200 py-2.5 px-4";
</script>

{#if showModal}
  <div
    transition:fade={{ duration: 150 }}
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 font-sans bg-black/50 backdrop-blur-xl"
  >
    <div
      transition:slide={{ duration: 300, axis: "y" }}
      class="relative w-full max-w-5xl max-h-[95vh] flex flex-col rounded-3xl shadow-2xl backdrop-blur-md bg-white/70 dark:bg-gray-900/70 text-gray-900 dark:text-gray-100 overflow-hidden border border-white/20 dark:border-gray-700/50 glass-effect"
    >
      <div
        class="flex-none flex items-center justify-between px-8 py-5 border-b border-white/30 dark:border-gray-700/50 bg-white/60 dark:bg-gray-900/60"
      >
        <h3
          class="flex items-center gap-4 text-2xl font-extrabold text-gray-800 dark:text-white"
        >
          <span
            class="material-symbols-rounded text-indigo-500 text-3xl p-1 bg-indigo-500/10 rounded-lg"
            >person_edit</span
          >
          Detalles del Estudiante:
          <span class="text-indigo-600 dark:text-indigo-400"
            >{estudiante.nombres}</span
          >
        </h3>
        <button
          on:click={closeModal}
          class="p-2 rounded-full text-gray-600 hover:text-white hover:bg-indigo-500/80 dark:text-gray-300 dark:hover:text-white dark:hover:bg-indigo-600 transition-colors duration-200 shadow-md"
          title="Cerrar"
        >
          <span class="material-symbols-rounded text-2xl">close</span>
        </button>
      </div>

      <div
        class="flex-none border-b border-white/30 dark:border-gray-700/50 bg-white/40 dark:bg-gray-800/40 px-8"
      >
        <nav class="-mb-px flex space-x-6 sm:space-x-8" aria-label="Tabs">
          <button
            on:click={() => (activeTab = "personal")}
            class="whitespace-nowrap py-3 px-3 border-b-4 rounded-t-lg font-bold text-sm transition-all duration-300 transform hover:scale-105 {activeTab ===
            'personal'
              ? 'border-indigo-500 text-indigo-700 dark:text-indigo-300 bg-indigo-500/10'
              : 'border-transparent text-gray-600 hover:text-indigo-500 dark:text-gray-400 dark:hover:text-indigo-300 hover:border-indigo-300/50'}"
          >
            <span class="material-symbols-rounded text-xl align-middle mr-1"
              >person</span
            >Personal
          </button>
          <button
            on:click={() => (activeTab = "academic")}
            class="whitespace-nowrap py-3 px-3 border-b-4 rounded-t-lg font-bold text-sm transition-all duration-300 transform hover:scale-105 {activeTab ===
            'academic'
              ? 'border-indigo-500 text-indigo-700 dark:text-indigo-300 bg-indigo-500/10'
              : 'border-transparent text-gray-600 hover:text-indigo-500 dark:text-gray-400 dark:hover:text-indigo-300 hover:border-indigo-300/50'}"
          >
            <span class="material-symbols-rounded text-xl align-middle mr-1"
              >school</span
            >Académico
          </button>
          <button
            on:click={() => (activeTab = "contact")}
            class="whitespace-nowrap py-3 px-3 border-b-4 rounded-t-lg font-bold text-sm transition-all duration-300 transform hover:scale-105 {activeTab ===
            'contact'
              ? 'border-indigo-500 text-indigo-700 dark:text-indigo-300 bg-indigo-500/10'
              : 'border-transparent text-gray-600 hover:text-indigo-500 dark:text-gray-400 dark:hover:text-indigo-300 hover:border-indigo-300/50'}"
          >
            <span class="material-symbols-rounded text-xl align-middle mr-1"
              >contact_mail</span
            >Contacto
          </button>
          <button
            on:click={() => (activeTab = "family")}
            class="whitespace-nowrap py-3 px-3 border-b-4 rounded-t-lg font-bold text-sm transition-all duration-300 transform hover:scale-105 {activeTab ===
            'family'
              ? 'border-indigo-500 text-indigo-700 dark:text-indigo-300 bg-indigo-500/10'
              : 'border-transparent text-gray-600 hover:text-indigo-500 dark:text-gray-400 dark:hover:text-indigo-300 hover:border-indigo-300/50'}"
          >
            <span class="material-symbols-rounded text-xl align-middle mr-1"
              >family_restroom</span
            >Familiar
          </button>
          <button
            on:click={() => (activeTab = "medical")}
            class="whitespace-nowrap py-3 px-3 border-b-4 rounded-t-lg font-bold text-sm transition-all duration-300 transform hover:scale-105 {activeTab ===
            'medical'
              ? 'border-indigo-500 text-indigo-700 dark:text-indigo-300 bg-indigo-500/10'
              : 'border-transparent text-gray-600 hover:text-indigo-500 dark:text-gray-400 dark:hover:text-indigo-300 hover:border-indigo-300/50'}"
          >
            <span class="material-symbols-rounded text-xl align-middle mr-1"
              >health_and_safety</span
            >Médico
          </button>
        </nav>
      </div>

      <div class="flex-grow overflow-y-auto custom-scrollbar px-8 py-6">
        <form on:submit|preventDefault={handleSubmit} class="space-y-8">
          {#if activeTab === "personal"}
            <div transition:fade class="space-y-6">
              <fieldset
                class="border border-white/30 dark:border-gray-700/50 rounded-2xl p-6 shadow-xl bg-white/80 dark:bg-gray-900/80 glass-panel"
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
                        >location_alt</span
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

      <div
        class="flex-none flex justify-end p-6 border-t border-white/30 dark:border-gray-700/50 bg-white/60 dark:bg-gray-900/60"
      >
        <button
          type="submit"
          class="inline-flex items-center px-6 py-3 rounded-lg text-white font-bold text-base shadow-xl transition-all duration-300 transform hover:scale-[1.05] active:scale-[0.98] focus:outline-none focus:ring-4 focus:ring-indigo-500/50 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
          on:click={handleSubmit}
        >
          <span class="material-symbols-rounded text-lg mr-2">save</span>
          Guardar Cambios
        </button>
        <button
          type="button"
          on:click={closeModal}
          class="ml-4 inline-flex items-center px-6 py-3 rounded-lg text-gray-700 dark:text-gray-200 font-bold text-base shadow-lg transition-all duration-300 transform hover:scale-[1.05] active:scale-[0.98] focus:outline-none focus:ring-4 focus:ring-gray-400/50 bg-gray-200/50 dark:bg-gray-700/50 hover:bg-gray-300/50 dark:hover:bg-gray-600/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
        >
          <span class="material-symbols-rounded text-lg mr-2">cancel</span>
          Cerrar
        </button>
      </div>
    </div>
  </div>
{/if}

<style>
  /* Material Symbols Font (if not already globally imported) */
  @import url("https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200");

  /*
    Se mantuvo la clase .glass-effect ya que no usa @apply y su definición de box-shadow
    es más compleja que una simple utilidad de Tailwind.
  */
  .glass-effect {
    box-shadow:
      0 8px 32px 0 rgba(31, 38, 135, 0.25),
      0 0 0 1px rgba(255, 255, 255, 0.18) inset;
  }

  /* Custom Scrollbar for a cleaner look */
  .custom-scrollbar::-webkit-scrollbar {
    width: 8px;
    height: 8px;
  }
  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(140, 150, 200, 0.4); /* Púrpura/índigo sutil */
    border-radius: 4px;
  }
  .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(100, 110, 160, 0.6);
  }
</style>
