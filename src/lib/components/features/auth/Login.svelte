<script lang="ts">
  import { createEventDispatcher } from "svelte";
  import { theme } from "../../../themeStore";
  import { LOGIN_ENDPOINT } from "../../../../constants";
  import { Card, Button, Label, Input, Alert, Spinner } from "flowbite-svelte";

  const dispatch = createEventDispatcher();

  let identificacion = "";
  let clave_acceso = "";
  let loading = false;
  let error = "";

  let showPassword = false;

  async function handleLogin() {
    loading = true;
    error = "";

    try {
      const response = await fetch(LOGIN_ENDPOINT, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ identificacion, clave_acceso }),
      });

      const data = await response.json();

      if (data.success) {
        dispatch("loginSuccess", { user: data.user });
      } else {
        error = data.message || "Error al iniciar sesión";
      }
    } catch (e) {
      error = "Error de conexión";
      console.error(e);
    } finally {
      loading = false;
    }
  }
</script>

<div
  class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden transition-colors duration-300"
  style="background: {$theme === 'dark'
    ? 'radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.08) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.08) 0%, transparent 50%), #0f172a'
    : 'radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.05) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(240, 147, 251, 0.05) 0%, transparent 50%), #ffffff'};"
>
  <!-- Decorative Background Elements -->
  <div
    class="fixed inset-0 pointer-events-none opacity-30"
    style="background-image: radial-gradient({$theme === 'dark'
      ? 'rgba(148, 163, 184, 0.15)'
      : 'rgba(203, 213, 225, 0.3)'} 1px, transparent 1px); background-size: 40px 40px;"
  ></div>

  <!-- Unified Login Card with Logo -->
  <div
    class="w-full max-w-5xl relative z-10 animate-fade-in-up flex items-center justify-center px-2"
  >
    <Card
      size="xl"
      class="glass-card-custom p-4 sm:p-6 lg:p-8 w-full"
    >
              <div
              class="flex flex-col lg:flex-row items-center lg:items-stretch justify-center gap-4 sm:gap-6 lg:gap-12 w-full h-full"
              >
              <!-- Institution Logo -->
              <div class="flex flex-col items-center justify-center flex-shrink-0 h-full">
                <div
                  class="relative logo-sheen w-32 h-32 sm:w-40 sm:h-40 md:w-52 md:h-52 lg:w-72 lg:h-72 overflow-hidden"
                >
                  <img
                    src="/src/assets/uescudo.png"
                    alt="Escudo Institución Educativa"
                    class="w-full h-full object-contain drop-shadow-2xl"
                  />
                </div>
              </div>
      
              <!-- Login Form -->
              <div class="flex-1 w-full lg:max-w-md h-full flex flex-col justify-center">          <div class="text-center lg:text-left mb-4 sm:mb-6 lg:mb-8">
            <h1
              class="text-lg sm:text-xl lg:text-2xl font-bold mb-2 {$theme ===
              'dark'
                ? 'text-white'
                : 'text-gray-900'}"
              style="font-family: var(--font-heading);"
            >
              Bienvenido
            </h1>
            <p
              class="text-xs sm:text-sm {$theme === 'dark'
                ? 'text-gray-400'
                : 'text-gray-500'}"
            >
              Ingresa tus credenciales para continuar
            </p>
          </div>

          <form
            on:submit|preventDefault={handleLogin}
            class="space-y-4 sm:space-y-6"
          >
            <div>
              <Label for="identificacion" class="mb-2 text-sm"
                >Identificación</Label
              >
              <Input
                id="identificacion"
                type="text"
                bind:value={identificacion}
                placeholder="Tu número de identificación"
                size="lg"
                required
                class="bg-white/50 dark:bg-gray-800/50 text-base"
              />
            </div>

            <div>
              <Label for="clave" class="mb-2 text-sm">Contraseña</Label>
              <div class="relative">
                <Input
                  id="clave"
                  type={showPassword ? "text" : "password"}
                  bind:value={clave_acceso}
                  placeholder="••••••••"
                  size="lg"
                  required
                  class="pr-10 bg-white/50 dark:bg-gray-800/50 text-base"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none min-h-[44px]"
                  on:click={() => (showPassword = !showPassword)}
                >
                  <span class="material-symbols-rounded text-xl">
                    {showPassword ? "visibility_off" : "visibility"}
                  </span>
                </button>
              </div>
            </div>

            {#if error}
              <Alert color="red" class="text-center text-sm">
                <span class="font-medium">{error}</span>
              </Alert>
            {/if}

            <Button
              type="submit"
              disabled={loading}
              size="lg"
              class="w-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600 text-base min-h-[44px] glass-button"
            >
              {#if loading}
                <Spinner class="mr-3 text-white" size="4" />
                <span>Ingresando...</span>
              {:else}
                <span>Iniciar Sesión</span>
                <span class="material-symbols-rounded text-lg ml-2"
                  >arrow_forward</span
                >
              {/if}
            </Button>
          </form>
        </div>
      </div>
    </Card>
  </div>
</div>
