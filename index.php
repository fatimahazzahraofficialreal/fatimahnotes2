<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Journals</title>
  <style>
    body{background:#faf7f2;font-family:system-ui,Segoe UI,Arial}
    header{text-align:center;margin:36px 0 18px}
    .journal-collection{
      display:flex;gap:16px;flex-wrap:wrap;
      justify-content:center;padding:8px 20px;
    }
    .journal-card{
      position:relative;
      width:180px;min-height:120px;border-radius:14px;
      padding:16px;box-shadow:0 6px 18px rgba(0,0,0,.08);
      cursor:pointer;transition:transform .15s ease;
    }
    .journal-card:hover{transform:translateY(-3px);}
    .journal-card h3{margin:0 0 8px;font-size:18px}
    .btn{padding:8px 12px;border:1px solid #ddd;border-radius:10px;background:#fff;cursor:pointer}
    #error{color:#b00020;text-align:center;margin-top:8px}
    .menu-btn{
      position:absolute;top:6px;right:8px;
      background:none;border:none;font-size:20px;
      cursor:pointer;color:#444;
    }
    .menu-btn:hover{color:#000;}
    .menu{
      position:absolute;top:28px;right:6px;
      background:#fff;border:1px solid #ccc;
      border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,.1);
      display:none;z-index:5;
    }
    .menu button{
      display:block;width:100%;padding:8px 12px;
      background:none;border:none;text-align:left;cursor:pointer;
    }
    .menu button:hover{background:#f5f5f5;}
  </style>
</head>
<body>
  <header>
    <h1>Fatimah Notes & Journals</h1>
    <button id="newJournalBtn" class="btn">+ New Journal</button>
    <div id="error"></div>
  </header>

  <section id="journalContainer" class="journal-collection"></section>

  <script>
const elList=document.getElementById('journalContainer');
const elErr=document.getElementById('error');

async function loadJournals(){
  elErr.textContent='';
  elList.innerHTML='<p style="opacity:.7">Loading...</p>';
  try{
    const r=await fetch('backend/get_journals.php',{cache:'no-store'});
    const text=await r.text();
    let data;
    try{data=JSON.parse(text);}catch{elErr.textContent='Response bukan JSON: '+text.slice(0,120);return;}
    if(!Array.isArray(data)){elErr.textContent='Format data tidak sesuai';return;}
    elList.innerHTML='';
    if(data.length===0){elList.innerHTML='<p style="opacity:.6">Belum ada jurnal. Klik + New Journal.</p>';return;}
    data.forEach(j=>{
      const d=document.createElement('div');
      d.className='journal-card';
      d.style.background=j.color||'#E6D0B8';
      <!-- di index.php, dalam loop data.forEach(j => { ... }) -->
      d.innerHTML = `
      <button class="menu-btn" onclick="toggleMenu(event,${j.id})">⋮</button>
      <div class="menu" id="menu-${j.id}">
      <button onclick="renameJournal(${j.id}, '${(j.title||'Untitled').replace(/'/g,"\\'")}')">✏️ Ganti Nama</button>
      <button onclick="deleteJournal(${j.id})">🗑 Hapus Jurnal</button>
      </div>
      <h3 id="title-${j.id}">${escapeHTML(j.title || 'Untitled')}</h3>
      <small>${new Date(j.created_at).toLocaleString()}</small>
`;

      d.addEventListener('click',e=>{
        if(!e.target.classList.contains('menu-btn') && !e.target.closest('.menu'))
          location.href=`journal.php?id=${j.id}`;
      });
      elList.appendChild(d);
    });
  }catch(e){
    elErr.textContent='Gagal memuat data: '+e.message;
  }
}

function escapeHTML(s){return String(s||'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');}
loadJournals();

// === Toggle menu ===
function toggleMenu(ev,id){
  ev.stopPropagation();
  document.querySelectorAll('.menu').forEach(m=>m.style.display='none');
  const m=document.getElementById('menu-'+id);
  if(m)m.style.display=(m.style.display==='block'?'none':'block');
}
document.addEventListener('click',()=>document.querySelectorAll('.menu').forEach(m=>m.style.display='none'));

// === Hapus jurnal ===
async function deleteJournal(id){
  if(!confirm('Yakin ingin menghapus jurnal ini? Semua catatan di dalamnya juga akan terhapus.'))return;
  const r=await fetch('backend/delete_journal.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id})
  });
  const j=await r.json();
  if(j.success){
    document.getElementById('menu-'+id).closest('.journal-card').remove();
  }else alert('Gagal menghapus: '+(j.error||'unknown'));
}

async function renameJournal(id, currentTitle) {
  const title = prompt('Nama jurnal baru:', currentTitle || '');
  if (!title || title.trim() === '') return;

  try {
    const res = await fetch('backend/save_journal.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id,                   // ⬅️ update mode
        title: title.trim(),
        content: '',          // tetap aman, tidak mengubah konten
        color: '#E6D0B8'      // biarkan sama (server tidak wajib memakainya)
      })
    });
    const json = await res.json();
    if (json.success) {
      // update judul pada kartu tanpa reload
      const h3 = document.getElementById(`title-${id}`);
      if (h3) h3.textContent = title.trim();
      // tutup menu
      const menu = document.getElementById(`menu-${id}`);
      if (menu) menu.style.display = 'none';
    } else {
      alert('Gagal mengganti nama: ' + (json.error || 'unknown'));
    }
  } catch (e) {
    alert('Request error: ' + e.message);
  }
}

// === Tambah jurnal baru ===
document.getElementById('newJournalBtn').addEventListener('click',async()=>{
  const title=prompt('Judul jurnal baru?'); if(!title)return;
  const res=await fetch('backend/save_journal.php',{
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({title,content:'',color:'#E6D0B8'})
  });
  const json=await res.json();
  if(json.success&&json.id)location.href=`journal.php?id=${json.id}`;
  else alert('Gagal menyimpan: '+(json.error||'Unknown error'));
});
  </script>
</body>
</html>
