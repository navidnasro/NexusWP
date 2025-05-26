import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig(
  {
    plugins: [vue()],
    base: './',
    root: 'app/views/frontend',
    build: {
      outDir: path.resolve(__dirname, 'resources'), // outputs to plugin_root/resources/
      emptyOutDir: true,
      rollupOptions: {
        input: path.resolve(__dirname, 'app/views/frontend/index.html') // entry point
      }
    },
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'app/views/frontend/src'), // src folder alias
      },
    },
  }
)
