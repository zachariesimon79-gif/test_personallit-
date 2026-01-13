<?php
// music_player.php — widget musique global
// Pré-requis: $db + current_user() dispo si connecté
// Peut fonctionner aussi non connecté: on ne joue rien si pas de musique choisie

$src = null;

// Si user connecté -> on prend sa musique choisie
if (isset($db) && function_exists('current_user')) {
  $user = current_user();
  if ($user) {
    $stmt = $db->prepare("
      SELECT t.stored_name
      FROM user_music_choice c
      JOIN tracks t ON t.id = c.track_id
      WHERE c.user_id = :u
      ORDER BY c.id DESC
      LIMIT 1
    ");
    $stmt->execute([':u' => $user['id']]);
    $stored = $stmt->fetchColumn();

    if ($stored) {
      $src = "music/uploads/" . rawurlencode($stored);
    }
  }
}

// Si pas de musique choisie -> on peut juste ne rien afficher
if (!$src) return;
?>

<div id="musicWidget" style="position:fixed; right:14px; bottom:14px; z-index:9999;">
  <div class="card" style="margin:0; padding:10px; display:flex; gap:8px; align-items:center;">
    <button class="btn" type="button" id="musicPlay">▶</button>
    <button class="btn" type="button" id="musicMute">🔇</button>

    <button class="btn" type="button" id="volDown">-</button>
    <div class="small" style="min-width:54px; text-align:center;" id="volText">100%</div>
    <button class="btn" type="button" id="volUp">+</button>

    <label class="small" style="display:flex; gap:6px; align-items:center; margin-left:6px;">
      <input type="checkbox" id="musicOff">
      Sans musique
    </label>
  </div>
</div>

<audio id="bgMusic" loop preload="auto">
  <source src="<?php echo htmlspecialchars($src); ?>">
</audio>

<script>
(function(){
  const audio   = document.getElementById('bgMusic');
  const playBtn = document.getElementById('musicPlay');
  const muteBtn = document.getElementById('musicMute');
  const offChk  = document.getElementById('musicOff');
  const downBtn = document.getElementById('volDown');
  const upBtn   = document.getElementById('volUp');
  const volText = document.getElementById('volText');

  if (!audio || !playBtn || !muteBtn || !offChk || !downBtn || !upBtn || !volText) return;

  const trackKey = 'music_track';
  const timeKey  = 'music_time';
  const volKey   = 'music_volume';
  const muteKey  = 'music_muted';
  const offKey   = 'music_off';

  const src = audio.querySelector('source')?.getAttribute('src') || '';

  // --- init préférences ---
  const savedVol  = parseFloat(localStorage.getItem(volKey) || '0.6');
  const savedMute = localStorage.getItem(muteKey) === '1';
  const savedOff  = localStorage.getItem(offKey) === '1';

  audio.volume = isNaN(savedVol) ? 0.6 : Math.min(1, Math.max(0, savedVol));
  audio.muted  = savedMute;
  offChk.checked = savedOff;

  function updateVolUI(){
    volText.textContent = Math.round(audio.volume * 100) + "%";
    muteBtn.textContent = audio.muted ? "🔈" : "🔇";
  }
  updateVolUI();

  // --- si nouvelle musique : reset time ---
  const prevTrack = localStorage.getItem(trackKey);
  if (prevTrack !== src) {
    localStorage.setItem(trackKey, src);
    localStorage.setItem(timeKey, '0');
  }

  // --- reprendre au bon timing ---
  audio.addEventListener('loadedmetadata', () => {
    const savedTime = parseFloat(localStorage.getItem(timeKey) || '0');
    if (!isNaN(savedTime) && savedTime > 0 && savedTime < audio.duration) {
      audio.currentTime = savedTime;
    }
  });

  // sauvegarde temps
  audio.addEventListener('timeupdate', () => {
    try { localStorage.setItem(timeKey, String(audio.currentTime || 0)); } catch(e){}
  });

  function setOff(on){
    localStorage.setItem(offKey, on ? '1' : '0');
    if (on) {
      audio.pause();
      playBtn.textContent = "▶";
    } else {
      tryPlay();
    }
  }

  offChk.addEventListener('change', () => setOff(offChk.checked));

  function tryPlay(){
    if (offChk.checked) return;
    audio.play().then(() => {
      playBtn.textContent = "⏸";
    }).catch(() => {
      // autoplay bloqué -> on attend un clic
      playBtn.textContent = "▶";
    });
  }

  // Play/Pause
  playBtn.addEventListener('click', () => {
    if (offChk.checked) { setOff(false); offChk.checked = false; return; }
    if (audio.paused) tryPlay();
    else { audio.pause(); playBtn.textContent = "▶"; }
  });

  // Mute toggle
  muteBtn.addEventListener('click', () => {
    audio.muted = !audio.muted;
    localStorage.setItem(muteKey, audio.muted ? '1' : '0');
    updateVolUI();
  });

  // Volume
  function setVol(v){
    audio.volume = Math.min(1, Math.max(0, v));
    localStorage.setItem(volKey, String(audio.volume));
    updateVolUI();
  }

  downBtn.addEventListener('click', () => setVol(audio.volume - 0.1));
  upBtn.addEventListener('click', () => setVol(audio.volume + 0.1));

  // Autoplay : tente direct
  if (!offChk.checked) tryPlay();

  // Débloque autoplay au premier clic n’importe où
  window.addEventListener('pointerdown', () => {
    if (!offChk.checked && audio.paused) tryPlay();
  }, { once:false });

})();
</script>
