import { mount } from 'svelte'
import './app.css'
import App from './App.svelte'
import './lib/themeStore' // Initialize theme store to apply dark class to HTML

const app = mount(App, {
  target: document.getElementById('app')!,
})

export default app
