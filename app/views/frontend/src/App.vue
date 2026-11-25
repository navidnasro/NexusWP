<script setup>
const links = [
  { label: 'Documentation', href: 'https://github.com/navidnasro/NexusWP#readme' },
  { label: 'Core package', href: 'https://github.com/navidnasro/nexuswp-core' },
]
</script>

<template>
  <main class="shell">
    <section class="card">
      <p class="eyebrow">WordPress plugin framework</p>
      <h1>NexusWP</h1>
      <p class="lede">
        Routing, models, migrations, hooks, events and jobs, with a Vue front end
        already wired to the plugin.
      </p>
      <nav class="links">
        <a v-for="link in links" :key="link.href" :href="link.href" target="_blank" rel="noreferrer">
          {{ link.label }}
        </a>
      </nav>
      <p class="hint">Start with <code>src/App.vue</code> and <code>routes/api.php</code>.</p>
    </section>
  </main>
</template>
