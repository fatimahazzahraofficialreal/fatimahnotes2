/* assets/js/script.js
   Dashboard: list & create journals (localStorage)
*/

const STORAGE_KEY = "journals.v1";

const palette = [
  "#E6D0B8", // kertas cokelat muda
  "#F7E1A0", // krem/kuning pastel
  "#C9E6DC", // hijau mint
  "#F4B8B8", // merah pastel
  "#D6C7F7", // ungu lembut
  "#FFD7AE", // peach
  "#B8E1FF"  // biru muda
];

// --- Helpers LocalStorage ---
function loadJournals() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

function saveJournals(list) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
}

// --- Seed contoh pertama kali ---
function seedIfEmpty() {
  const list = loadJournals();
  if (list.length) return;

  const now = Date.now();
  const demo = [
    { id: `${now}-study`, title: "Study Journal", color: palette[0], createdAt: now },
    { id: `${now}-jul`,   title: "jul",            color: palette[1], createdAt: now + 1 },
    { id: `${now}-recipe`,title: "Recipe book",    color: palette[2], createdAt: now + 2 },
    { id: `${now}-jun`,   title: "jun",            color: palette[3], createdAt: now + 3 },
  ];
  saveJournals(demo);
}

// --- Render kartu jurnal di dashboard ---
function renderJournals() {
  const container = document.querySelector(".journal-collection");
  if (!container) return;

  container.innerHTML = "";
  const list = loadJournals().sort((a, b) => b.createdAt - a.createdAt);

  list.forEach(j => {
    const card = document.createElement("div");
    card.className = "journal-card";
    card.style.background = j.color || palette[0];
    card.style.backgroundImage = "linear-gradient(transparent 0, rgba(0,0,0,0.03) 100%)"; // kesan tekstur
    card.innerHTML = `
      <div class="card-inner">
        <h3>${escapeHTML(j.title || "Untitled")}</h3>
        <p>${new Date(j.createdAt).toLocaleDateString()}</p>
      </div>
    `;
    card.onclick = () => {
      window.location.href = `journal.html?id=${encodeURIComponent(j.id)}`;
    };
    container.appendChild(card);
  });

  // Jika kosong
  if (list.length === 0) {
    const empty = document.createElement("p");
    empty.style.opacity = "0.7";
    empty.textContent = "Belum ada jurnal. Klik + New Journal untuk memulai.";
    container.appendChild(empty);
  }
}

// --- Buat jurnal baru ---
function setupCreateButton() {
  const btn = document.getElementById("newJournalBtn");
  if (!btn) return;

  btn.addEventListener("click", () => {
    const title = prompt("Judul jurnal baru?");
    if (!title) return;

    const list = loadJournals();
    const id = `j-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
    const color = palette[Math.floor(Math.random() * palette.length)];

    list.push({
      id,
      title: title.trim(),
      color,
      createdAt: Date.now(),
    });
    saveJournals(list);
    renderJournals();

    // langsung masuk ke halaman jurnalnya
    window.location.href = `journal.html?id=${encodeURIComponent(id)}`;
  });
}

// --- Util kecil ---
function escapeHTML(str) {
  return String(str)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

// --- Init ---
document.addEventListener("DOMContentLoaded", () => {
  seedIfEmpty();
  setupCreateButton();
  renderJournals();
});
