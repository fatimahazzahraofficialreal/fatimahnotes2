<?php $jid = intval($_GET['id'] ?? 0); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Journal</title>
<style>
  body{background:#faf7f2;font-family:'Segoe UI',Poppins,system-ui,Arial;margin:0}
  .topbar{
    position:sticky;top:0;z-index:20;
    display:flex;align-items:center;gap:12px;
    padding:12px 16px;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.06);
  }
  .back-btn{
    display:inline-flex;align-items:center;gap:8px;
    border:1px solid #ddd;background:#fff;cursor:pointer;
    border-radius:12px;padding:8px 12px;font-size:14px;
    transition:transform .12s ease,box-shadow .12s ease;
  }
  .back-btn:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(0,0,0,.07);}
  .wrap{display:grid;grid-template-columns:320px 1fr;gap:18px;max-width:1300px;margin:24px auto;padding:0 16px}
  .panel{background:#fff;border-radius:16px;box-shadow:0 8px 20px rgba(0,0,0,.05)}
  .left{padding:14px}
  .toolbar{display:flex;gap:8px;align-items:center;padding:10px 16px;border-bottom:1px solid #eee;flex-wrap:wrap}
  .toolbar select,.toolbar input[type="text"]{padding:8px 10px;border-radius:10px;border:1px solid #ddd}
  .entries{max-height:70vh;overflow:auto;margin-top:10px}
  .entry{padding:10px 12px;border:1px solid #eee;border-radius:12px;margin-bottom:10px;cursor:pointer;background:#fff}
  .entry:hover{background:#f7f4ef}
  .active{outline:2px solid #7bb0ff}

  .ribbon{
    display:flex;gap:8px;align-items:center;
    padding:6px 8px;background:#f5f5f5;border-radius:10px;margin-bottom:10px;flex-wrap:wrap;
  }
  .ribbon button{
    border:none;background:#fff;cursor:pointer;border-radius:6px;
    padding:6px 10px;font-size:15px;box-shadow:0 1px 3px rgba(0,0,0,.1);
  }
  .ribbon button:hover{background:#eee;}

  .editor{
    min-height:420px;width:100%;padding:16px;border:1px solid #ddd;
    border-radius:12px;background:#fff;outline:none;font-size:16px;
    line-height:1.6;overflow:auto;
  }
  .editor:empty:before{content:attr(placeholder);color:#999;}
  .editor img,.editor video,.editor iframe{
    max-width:100%;height:auto;border-radius:8px;display:block;margin:10px 0;
  }
  .saveBadge{font-size:12px;opacity:.7}
  .btn{padding:8px 12px;border-radius:10px;border:1px solid #ddd;background:#fff;cursor:pointer}
  .entry-menu-btn{background:none;border:none;font-size:18px;cursor:pointer;padding:2px 6px;color:#555;transition:.1s;}
  .entry-menu-btn:hover{color:#000;}
  .entry-menu{position:absolute;right:10px;top:28px;display:none;background:#fff;border:1px solid #ccc;border-radius:8px;box-shadow:0 4px 10px rgba(0,0,0,.1);z-index:10;}
  .entry-menu button{display:block;width:100%;padding:8px 12px;border:none;background:none;cursor:pointer;text-align:left;}
  .entry-menu button:hover{background:#f5f5f5;}
</style>
</head>
<body>

<!-- NAV BAR -->
<div class="topbar">
  <button class="back-btn" onclick="window.location.href='index.php'">← Back to Home</button>
  <h2 style="margin:0;font-weight:500;">My Journal</h2>
  <span class="saveBadge" id="saveBadge">saved</span>
</div>

<div class="wrap">
  <!-- LEFT SIDEBAR -->
  <aside class="panel left">
    <div class="toolbar">
      <button class="btn" id="newEntry">+ Entry</button>
      <input id="search" type="text" placeholder="Search title/tags..." style="flex:1">
    </div>
    <div class="entries" id="entries"></div>
  </aside>

  <!-- RIGHT CONTENT -->
  <section class="panel" style="padding:14px 20px">
    <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
      <input id="title" type="text" placeholder="Note title..." style="flex:1;padding:8px 10px;border-radius:8px;border:1px solid #ddd">
      <select id="mood" title="Mood">
        <option>🙂</option><option>😐</option><option>😔</option>
        <option>🔥</option><option>💡</option><option>🙏</option>
      </select>
      <input id="tags" type="text" placeholder="tags: study, iot, reflection" style="min-width:220px;padding:8px 10px;border-radius:8px;border:1px solid #ddd">
    </div>

    <!-- Word-like toolbar -->
    <div class="ribbon" id="ribbon">
      <button onclick="format('bold')"><b>B</b></button>
      <button onclick="format('italic')"><i>I</i></button>
      <button onclick="format('underline')"><u>U</u></button>
      <button onclick="format('insertUnorderedList')">• List</button>
      <button onclick="format('insertOrderedList')">1. List</button>
      <button onclick="format('formatBlock','H2')">H2</button>
      <button onclick="format('formatBlock','BLOCKQUOTE')">❝ Quote</button>
      <button onclick="insertLink()">🔗 Link</button>
      <button id="addImg">📷 Image</button>
      <button id="addVid">🎬 Video</button>
    </div>

    <!-- Editor area -->
    <div id="content" class="editor" contenteditable="true" placeholder="Write your thoughts, ideas, or reflections..."></div>

    <input type="file" id="imgInput" accept="image/*" hidden>
    <input type="file" id="vidInput" accept="video/*" hidden>

    <div style="margin-top:14px;">
      <video id="player" width="560" controls></video>
    </div>
  </section>
</div>

<script>
const journalId = <?php echo $jid; ?>;
let currentId=null, all=[], t;
const editor = document.getElementById('content');
const saveBadge = document.getElementById('saveBadge');

/* === format tools === */
function format(cmd,val=null){ document.execCommand(cmd,false,val); scheduleSave(); }
function insertLink(){
  const url = prompt('Enter URL:');
  if (url) document.execCommand('createLink', false, url);
  scheduleSave();
}

/* === Entry handling === */
async function loadEntries(){
  const r = await fetch(`backend/list_entries.php?journal_id=${journalId}`);
  all = await r.json();
  renderList(all);
  if (all.length && !currentId) openEntry(all[0].id);
}

/* === Render entries with ⋮ menu === */
function renderList(items){
  const q = document.getElementById('search').value?.toLowerCase() || '';
  const box = document.getElementById('entries');
  box.innerHTML = '';

  items
    .filter(e => (e.title||'').toLowerCase().includes(q) || (e.tags||'').toLowerCase().includes(q))
    .forEach(e=>{
      const div = document.createElement('div');
      div.className = 'entry' + (e.id==currentId?' active':'');
      div.style.position = 'relative';
      div.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div style="flex:1;min-width:0;">
            <strong>${escapeHTML(e.title||'Untitled')}</strong>
            <small style="display:block;opacity:.7">${escapeHTML(e.tags||'')}</small>
          </div>
          <button class="entry-menu-btn" onclick="toggleEntryMenu(event,${e.id})">⋮</button>
          <div class="entry-menu" id="entry-menu-${e.id}">
            <button onclick="deleteEntry(${e.id})">🗑 Delete</button>
          </div>
        </div>`;
      div.onclick = ev=>{
        if (!ev.target.classList.contains('entry-menu-btn') && !ev.target.closest('.entry-menu'))
          openEntry(e.id);
      };
      box.appendChild(div);
    });
}

/* === Open entry === */
async function openEntry(id){
  const r = await fetch(`backend/get_entry.php?id=${id}`);
  const e = await r.json();
  currentId = e.id;
  document.getElementById('title').value = e.title || '';
  editor.innerHTML = e.content || '';
  document.getElementById('mood').value = e.mood || '🙂';
  document.getElementById('tags').value = e.tags || '';
  document.getElementById('player').src = e.video_path || '';
  renderList(all);
}

/* === Add entry === */
document.getElementById('newEntry').onclick = async ()=>{
  const f = new FormData();
  f.append('journal_id', journalId);
  f.append('title', 'Untitled');
  const r = await fetch('backend/create_entry.php', { method:'POST', body:f });
  const j = await r.json();
  await loadEntries();
  openEntry(j.id);
};

/* === ⋮ menu toggle === */
function toggleEntryMenu(ev,id){
  ev.stopPropagation();
  document.querySelectorAll('.entry-menu').forEach(m=>m.style.display='none');
  const menu = document.getElementById('entry-menu-'+id);
  if (menu) menu.style.display = (menu.style.display==='block' ? 'none' : 'block');
}
document.addEventListener('click', ()=>document.querySelectorAll('.entry-menu').forEach(m=>m.style.display='none'));

/* === Delete entry === */
async function deleteEntry(id){
  if (!confirm('Delete this entry?')) return;
  const r = await fetch('backend/delete_entry.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id})
  });
  const j = await r.json();
  if (j.success){
    all = all.filter(e=>e.id!=id);
    renderList(all);
    if (currentId==id){
      editor.innerHTML=''; document.getElementById('title').value=''; currentId=null;
    }
  } else alert('Failed to delete: ' + (j.error || 'unknown error'));
}

/* === Autosave === */
['title','mood','tags'].forEach(id=>document.getElementById(id).addEventListener('input',scheduleSave));
editor.addEventListener('input',scheduleSave);

function scheduleSave(){ clearTimeout(t); saveBadge.textContent='saving...'; t=setTimeout(save,800); }

async function save(){
  if(!currentId) return;
  const payload = {
    id: currentId,
    title: document.getElementById('title').value,
    content: editor.innerHTML,
    mood: document.getElementById('mood').value,
    tags: document.getElementById('tags').value
  };
  await fetch('backend/update_entry.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  });
  saveBadge.textContent = 'saved';
  const idx = all.findIndex(e=>e.id==currentId);
  if (idx>-1){
    all[idx].title = payload.title;
    all[idx].tags  = payload.tags;
    all[idx].mood  = payload.mood;
    renderList(all);
  }
}

/* === Image upload === */
document.getElementById('addImg').onclick = ()=>document.getElementById('imgInput').click();
document.getElementById('imgInput').onchange = async e=>{
  if(!currentId || !e.target.files[0]) return;
  const fd = new FormData();
  fd.append('id', currentId);
  fd.append('image', e.target.files[0]);
  const r = await fetch('backend/upload_entry_image.php', { method:'POST', body:fd });
  const j = await r.json();
  if(j.success){ insertHTML(`<img src="${j.path}" alt="">`); scheduleSave(); }
};

/* === Video upload / embed === */
document.getElementById('addVid').onclick = ()=>{
  const opt = confirm('OK = Upload a video file\nCancel = Embed YouTube/mp4 link');
  if (opt) document.getElementById('vidInput').click();
  else{
    const url = prompt('Enter video URL (YouTube/mp4):');
    if (!url) return;
    const yt = parseYouTube(url);
    if (yt) insertHTML(`<iframe src="https://www.youtube.com/embed/${yt}" allowfullscreen></iframe>`);
    else if (/\.(mp4|webm|ogg)$/i.test(url)) insertHTML(`<video controls src="${url}"></video>`);
    scheduleSave();
  }
};
document.getElementById('vidInput').onchange = async e=>{
  if(!currentId || !e.target.files[0]) return;
  const fd = new FormData();
  fd.append('id', currentId);
  fd.append('video', e.target.files[0]);
  const r = await fetch('backend/upload_entry_video.php', { method:'POST', body:fd });
  const j = await r.json();
  if(j.success){ insertHTML(`<video controls src="${j.path}"></video>`); scheduleSave(); }
};

/* === Insert HTML helper === */
function insertHTML(html){
  const sel = window.getSelection();
  if(!sel.rangeCount) return;
  const range = sel.getRangeAt(0);
  range.deleteContents();
  const frag = document.createRange().createContextualFragment(html);
  range.insertNode(frag);
}

/* === Utilities === */
function parseYouTube(url){
  try{
    const u=new URL(url);
    if(/youtu\.be$/.test(u.hostname))return u.pathname.slice(1);
    if(/youtube\.com$/.test(u.hostname)){
      if(u.searchParams.get('v'))return u.searchParams.get('v');
      if(u.pathname.startsWith('/shorts/'))return u.pathname.split('/')[2];
      if(u.pathname.startsWith('/embed/')) return u.pathname.split('/')[2];
    }
  }catch{return null;}
  return null;
}
function escapeHTML(s){
  return String(s||'')
    .replaceAll('&','&amp;')
    .replaceAll('<','&lt;')
    .replaceAll('>','&gt;');
}

/* === search/filter === */
document.getElementById('search').addEventListener('input',()=>renderList(all));

loadEntries();
</script>
</body>
</html>
