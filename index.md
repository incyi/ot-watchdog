---
layout: default
---

<script>
  fetch('https://raw.githubusercontent.com/incyi/ot-watchdog/refs/heads/main/README.md')
    .then(r => r.text())
    .then(md => {
      // GitHub Pages heeft geen markdown renderer via JS
      // Gebruik marked.js
      document.getElementById('content').innerHTML = marked.parse(md);
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.6/marked.min.js"></script>
<div id="content">Laden...</div>