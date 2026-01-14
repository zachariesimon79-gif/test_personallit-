(() => {
  // THEME: auto / light / dark
  const html = document.documentElement;
  const btn = document.getElementById("themeToggle");

  const getSystem = () =>
    window.matchMedia && window.matchMedia("(prefers-color-scheme: light)").matches ? "light" : "dark";

  const apply = (mode) => {
    if (mode === "auto") {
      html.dataset.theme = getSystem() === "light" ? "light" : "dark";
    } else {
      html.dataset.theme = mode;
    }
    if (btn) btn.textContent = "Thème : " + mode[0].toUpperCase() + mode.slice(1);
  };

  const saved = localStorage.getItem("themeMode") || "auto";
  apply(saved);

  if (btn) {
    btn.addEventListener("click", () => {
      const cur = localStorage.getItem("themeMode") || "auto";
      const next = cur === "auto" ? "dark" : cur === "dark" ? "light" : "auto";
      localStorage.setItem("themeMode", next);
      apply(next);
    });
  }

  if (window.matchMedia) {
    window.matchMedia("(prefers-color-scheme: light)").addEventListener("change", () => {
      const cur = localStorage.getItem("themeMode") || "auto";
      if (cur === "auto") apply("auto");
    });
  }

  // AUDIO: autoplay “safe”
  const audio = document.getElementById("bgMusic");
  if (audio) {
    audio.loop = true;
    const start = () => {
      audio.play().catch(() => {});
      window.removeEventListener("click", start);
      window.removeEventListener("keydown", start);
    };
    window.addEventListener("click", start);
    window.addEventListener("keydown", start);
  }

  // petit parallax souris
  document.addEventListener("mousemove", (e) => {
    const x = (e.clientX / window.innerWidth) * 100;
    const y = (e.clientY / window.innerHeight) * 100;
    document.body.style.backgroundPosition = `${x}% ${y}%`;
  });
})();
