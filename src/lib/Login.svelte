<script lang="ts">
  import { createEventDispatcher } from "svelte";
  import { theme } from "./themeStore";
  import { LOGIN_ENDPOINT } from "../../constants";
  import { Card, Button, Label, Input, Alert, Spinner } from "flowbite-svelte";

  const dispatch = createEventDispatcher();

  let identificacion = "";
  let clave_acceso = "";
  let loading = false;
  let error = "";

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

  <div class="w-full max-w-md relative z-10 animate-fade-in-up">
    <Card size="xl" class="shadow-premium-xl p-8">
      <div class="text-center mb-8">
        <div
          class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg"
        >
          <span class="material-symbols-rounded text-white text-3xl"
            >school</span
          >
        </div>
        <h1
          class="text-2xl font-bold mb-2 {$theme === 'dark'
            ? 'text-white'
            : 'text-gray-900'}"
          style="font-family: var(--font-heading);"
        >
          Bienvenido
        </h1>
        <p
          class="text-sm {$theme === 'dark'
            ? 'text-gray-400'
            : 'text-gray-500'}"
        >
          Ingresa tus credenciales para continuar
        </p>
      </div>

      <form on:submit|preventDefault={handleLogin} class="space-y-6">
        <div>
          <Label for="identificacion" class="mb-2">Identificación</Label>
          <Input
            id="identificacion"
            type="text"
            bind:value={identificacion}
            placeholder="Tu número de identificación"
            size="lg"
            required
          />
        </div>

        <div>
          <Label for="clave" class="mb-2">Contraseña</Label>
          <Input
            id="clave"
            type="password"
            bind:value={clave_acceso}
            placeholder="••••••••"
            size="lg"
            required
          />
        </div>

        {#if error}
          <Alert color="red" class="text-center">
            <span class="font-medium">{error}</span>
          </Alert>
        {/if}

        <Button
          type="submit"
          disabled={loading}
          size="lg"
          class="w-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-600 hover:via-purple-600 hover:to-pink-600"
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
    </Card>
  </div>
</div>
