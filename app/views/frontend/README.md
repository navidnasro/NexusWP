# Front end

Vue 3 single-page app served by the plugin at `/app`.

```bash
npm install
npm run dev     # Vite dev server with HMR
npm run build   # emit to plugin resources/
```

The route is declared in `routes/web.php` and rendered by
`app/controllers/AppController.php`. `AppController` rewrites the `<base>` tag at
request time, so the built bundle works from any WordPress install path.

Keep framework calls out of the front end: it talks to the plugin through the
REST routes in `routes/api.php`.
