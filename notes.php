<?php
session_start();
require_once "db.php";

$user_id = $_SESSION['user_id'] ?? 1;
/* --------- CRUD --------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Accept rich text HTML from hidden field (content_html)
    $id       = $_POST['note_id']   ?? null;
    $title    = $_POST['title']     ?? '';
    $tag      = $_POST['tag']       ?? 'General';
    $priority = $_POST['priority']  ?? 'Low';
    $content  = $_POST['content_html'] ?? ''; // rich HTML
    $pinned   = isset($_POST['pinned']) ? 1 : 0;

    // MULTI IMAGE UPLOAD
    $imagesArr = [];
    if (!empty($_FILES['images']['name'][0])) {
        $dir = "uploads/notes/";
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        foreach ($_FILES['images']['name'] as $i => $name) {
            if (!$_FILES['images']['name'][$i]) continue;
            $filename = time() . "_" . preg_replace('/[^a-zA-Z0-9\._-]/','_', $name);
            $path = $dir . $filename;
            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $path)) {
                $imagesArr[] = $path;
            }
        }
    }
    $imagesJson = !empty($imagesArr) ? json_encode($imagesArr) : null;

    if ($id) {
        if ($imagesJson) {
            $stmt = $conn->prepare("UPDATE notes SET title=?, content=?, tag=?, priority=?, pinned=?, images=? WHERE id=? AND user_id=?");
            $stmt->bind_param("ssssissi", $title, $content, $tag, $priority, $pinned, $imagesJson, $id, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE notes SET title=?, content=?, tag=?, priority=?, pinned=? WHERE id=? AND user_id=?");
            $stmt->bind_param("ssssiii", $title, $content, $tag, $priority, $pinned, $id, $user_id);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content, tag, priority, pinned, images) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssiss", $user_id, $title, $content, $tag, $priority, $pinned, $imagesJson);
    }
    $stmt->execute();
    $stmt->close();
    exit(json_encode(["success"=>true]));
}

/* soft-delete (trash) */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("UPDATE notes SET trashed=1, archived=0, pinned=0 WHERE id=$id AND user_id=$user_id");
    header("Location: notes.php");
    exit;
}

/* restore from trash */
if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $conn->query("UPDATE notes SET trashed=0 WHERE id=$id AND user_id=$user_id");
    header("Location: notes.php");
    exit;
}

/* archive toggle */
if (isset($_GET['archive'])) {
    $id = (int)$_GET['archive'];
    $conn->query("UPDATE notes SET archived = 1 - archived WHERE id=$id AND user_id=$user_id");
    header("Location: notes.php");
    exit;
}

/* pin toggle */
if (isset($_GET['pin'])) {
    $id = (int)$_GET['pin'];
    $conn->query("UPDATE notes SET pinned = 1 - pinned WHERE id=$id AND user_id=$user_id");
    header("Location: notes.php");
    exit;
}

/* list (default: not trashed) */
$where = "user_id=$user_id AND trashed=0";
$sort  = $_GET['sort'] ?? 'recent';
$order = "pinned DESC, created_at DESC";
if ($sort === 'oldest') $order = "pinned DESC, created_at ASC";
if ($sort === 'az')     $order = "pinned DESC, title ASC";
if ($sort === 'pinned') $order = "pinned DESC, created_at DESC";

$tagFilter = $_GET['tag'] ?? '';
if ($tagFilter) $where .= " AND tag='" . $conn->real_escape_string($tagFilter) . "'";

$dateFilter = $_GET['date'] ?? '';
if ($dateFilter) {
    // expect YYYY-MM (month filter)
    $where .= " AND DATE_FORMAT(created_at,'%Y-%m')='" . $conn->real_escape_string($dateFilter) . "'";
}

$notes = $conn->query("SELECT * FROM notes WHERE $where ORDER BY $order");
$allTagsRes = $conn->query("SELECT DISTINCT tag FROM notes WHERE user_id=$user_id");
$allTags = [];
while($t = $allTagsRes->fetch_assoc()) $allTags[] = $t['tag'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Smart Notes — Pro Final</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- html2pdf for export -->
  <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.1/dist/html2pdf.bundle.min.js"></script>
  <style>
    /* Keep your background exactly as-is */
    body {
      margin: 0; color: #fff; font-family: "Poppins", Arial, sans-serif;
      background: url('bg.jpg') no-repeat center center fixed; background-size: cover;
      height: 100vh; overflow: hidden;
    }
    /* Glass overlay */
    .wrap {
      display: grid; grid-template-columns: 2fr 1fr; gap: 24px;
      height: 100vh; padding: 24px;
      background: linear-gradient(to bottom, rgba(0,0,0,0.25), rgba(0,0,0,0.55));
      backdrop-filter: blur(2px);
    }
    .glass {
      background: rgba(10, 9, 9, 0.47);
      border: 1px solid rgba(28, 26, 26, 0.16);
      border-radius: 18px; padding: 18px; overflow: hidden;
      backdrop-filter: blur(14px);
    }
    .panel-header {
      display: flex; align-items: center; justify-content: space-between; gap: 12px;
      margin-bottom: 10px;
    }
    .panel-header h2 { margin: 0; font-weight: 600; }
    .controls, .filters { display:flex; gap:10px; flex-wrap: wrap; align-items:center; }
    .btn, select, input[type="text"], input[type="month"] {
      background: rgba(6, 4, 4, 0.64); border: 1px solid rgba(255,255,255,0.25);
      color: #fff; padding: 8px 12px; border-radius: 10px; outline: none;
    }
    .btn.primary{ background:#1d8fff; border-color:#1d8fff; }
    .btn.warn{ background:#ff5c5c; border-color:#ff5c5c; }
    .btn.ghost{ background: rgba(21, 19, 19, 0.06); }
    .btn:disabled{ opacity:.5; cursor:not-allowed; }

    /* Editor / Toolbar */
    .editor { display:flex; gap:14px; height: calc(100vh - 120px); }
    .editor-left { flex:1.2; display:flex; flex-direction:column; gap:10px; }
    .toolbar { display:flex; flex-wrap:wrap; gap:8px; }
    .toolbar .btn { padding:6px 10px; }
    .title-row { display:flex; gap:10px; }
    .title-row input[type="text"]{ flex:1; }
    .title-row select { width: 160px; }
    .meta-row{ display:flex; gap:10px; align-items:center; }
    .meta-row label{ display:flex; gap:6px; align-items:center; }
    .content {
      flex:1; background: rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.15);
      border-radius:12px; padding:14px; overflow:auto;
    }
    .content[contenteditable="true"] { outline: none; }
    .stats { font-size:12px; opacity:.8; display:flex; gap:12px; }

    /* Gallery */
    .editor-right { width: 320px; display:flex; flex-direction:column; gap:10px; }
    .gallery { display:grid; grid-template-columns:1fr 1fr; gap:8px; max-height:280px; overflow:auto; }
    .gallery img{ width:100%; height:120px; object-fit:cover; border-radius:10px; }
    .pill { padding:3px 8px; border-radius:999px; font-size:12px; display:inline-block; }
    .tag-General{ background:#3b82f6; } .tag-Career{ background:#10b981; }
    .tag-Goals{ background:#f59e0b; } .tag-Personal{ background:#ec4899; }
    .tag-Ideas{ background:#8b5cf6; }

    /* List */
    .list { height: calc(100vh - 120px); overflow:auto; display:flex; flex-direction:column; gap:10px; }
    .note-card {
      background: rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.14);
      border-radius:14px; padding:12px; cursor:pointer; transition: .15s;
    }
    .note-card:hover{ transform: translateY(-1px); background: rgba(255,255,255,0.12); }
    .note-title{ font-weight:600; }
    .note-sub{ font-size:12px; opacity:.8; display:flex; gap:8px; flex-wrap:wrap; }
    .note-ops{ display:flex; gap:6px; margin-top:6px; flex-wrap:wrap; }
    .note-ops a{ color:#fff; text-decoration:none; font-size:12px; opacity:.9; }
    .muted{ opacity:.6; }

    /* top bar */
    .topbar { position:fixed; top:12px; right:18px; z-index:10; }
    .topbar a { text-decoration:none; color:#fff; background:rgba(0,0,0,0.5); padding:8px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.2); }

    @media (max-width: 1024px){
      .wrap{ grid-template-columns: 1fr; }
      .editor{ flex-direction:column; }
      .editor-right{ width:100%; }
    }
  </style>
</head>
<body>

<div class="topbar"><a href="dashboard_main.php">← Dashboard</a></div>

<div class="wrap">

  <!-- LEFT: Editor -->
  <section class="glass">
    <div class="panel-header">
      <h2>📝 Smart Note Editor</h2>
      <div class="controls">
        <button class="btn primary" id="saveBtn">💾 Save</button>
        <button class="btn" id="exportBtn">📄 Export PDF</button>
        <button class="btn" id="micBtn">🎙️ Voice</button>
      </div>
    </div>

    <div class="editor">
      <div class="editor-left">
        <div class="toolbar">
          <button class="btn ghost" data-cmd="bold"><b>B</b></button>
          <button class="btn ghost" data-cmd="italic"><i>I</i></button>
          <button class="btn ghost" data-cmd="underline"><u>U</u></button>
          <button class="btn ghost" data-cmd="formatBlock" data-val="H2">H2</button>
          <button class="btn ghost" data-cmd="insertUnorderedList">• List</button>
          <button class="btn ghost" id="hlBtn">🔆 Highlight</button>
          <button class="btn ghost" id="linkBtn">🔗 Link</button>
        </div>

        <div class="title-row">
          <input type="text" id="title" placeholder="Title…">
          <select id="tag">
            <?php
            $defs = ['General','Career','Goals','Personal','Ideas'];
            foreach($defs as $d){
              echo "<option>$d</option>";
            }
            ?>
          </select>
          <select id="priority">
            <option>Low</option><option>Medium</option><option>High</option>
          </select>
        </div>

        <div class="meta-row">
          <label><input type="checkbox" id="pinned"> Pinned</label>
          <span class="stats"><span id="wc">0 words</span> • <span id="cc">0 chars</span></span>
        </div>

        <!-- Contenteditable: rich text -->
        <div id="content" class="content" contenteditable="true" spellcheck="true"></div>

        <!-- hidden fields for POST -->
        <form id="noteForm" enctype="multipart/form-data" style="display:none">
          <input type="hidden" name="note_id" id="note_id">
          <input type="hidden" name="title" id="title_post">
          <input type="hidden" name="tag" id="tag_post">
          <input type="hidden" name="priority" id="priority_post">
          <input type="hidden" name="pinned" id="pinned_post" value="">
          <input type="hidden" name="content_html" id="content_html">
        </form>
      </div>

      <!-- Gallery / Uploads -->
      <div class="editor-right">
        <div class="panel-header" style="margin:0">
          <h3 style="margin:0;">🖼️ Images</h3>
          <div class="controls">
            <label class="btn">➕ Add Images
              <input type="file" id="images" multiple accept="image/*" style="display:none">
            </label>
          </div>
        </div>
        <div id="gallery" class="gallery"></div>
        <div id="imgMsg" class="muted" style="font-size:12px;">(Images will be saved with the note)</div>
      </div>
    </div>
  </section>

  <!-- RIGHT: List / Filters -->
  <section class="glass">
    <div class="panel-header">
      <h2>📚 Your Notes</h2>
      <div class="filters">
        <input type="text" id="search" placeholder="Search…">
        <select id="filterTag">
          <option value="">All Tags</option>
          <?php foreach($allTags as $tg) echo "<option>".htmlspecialchars($tg)."</option>"; ?>
        </select>
        <input type="month" id="filterMonth">
        <select id="sort">
          <option value="recent">Recent</option>
          <option value="oldest">Oldest</option>
          <option value="az">A–Z</option>
          <option value="pinned">Pinned</option>
        </select>
      </div>
    </div>

    <div id="list" class="list">
      <?php while($n = $notes->fetch_assoc()): ?>
        <div class="note-card" data-id="<?= $n['id'] ?>" data-title="<?= htmlspecialchars($n['title']) ?>" data-content="<?= htmlspecialchars(strip_tags($n['content'])) ?>" data-tag="<?= htmlspecialchars($n['tag']) ?>" data-date="<?= date('Y-m', strtotime($n['created_at'])) ?>">
          <div class="note-title"><?= htmlspecialchars($n['title']) ?></div>
          <div class="note-sub">
            <span class="pill tag-<?= htmlspecialchars($n['tag']) ?>"><?= htmlspecialchars($n['tag']) ?></span>
            <span class="pill" style="background:<?= $n['priority']=='High'?'#ef4444':($n['priority']=='Medium'?'#f59e0b':'#10b981') ?>"><?= $n['priority'] ?></span>
            <?php if($n['pinned']): ?><span class="pill" style="background:#1d8fff">📌 Pinned</span><?php endif; ?>
            <?php if($n['archived']): ?><span class="pill" style="background:#6b7280">🗄️ Archived</span><?php endif; ?>
            <span class="muted"><?= date("M d, Y", strtotime($n['created_at'])) ?></span>
          </div>
          <div class="note-ops">
            <a href="?pin=<?= $n['id'] ?>">📌 Pin</a>
            <a href="?archive=<?= $n['id'] ?>">🗄️ Archive</a>
            <a href="?delete=<?= $n['id'] ?>" onclick="return confirm('Move to trash?')">🗑️ Trash</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <div class="muted" style="padding:8px 2px; font-size:12px;">
      Tip: Press <b>Ctrl+S</b> to save quickly. Use the mic to dictate your notes.
    </div>
  </section>

</div>

<script>
/* ============ Toolbar ============ */
document.querySelectorAll('.toolbar .btn[data-cmd]').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const cmd = btn.getAttribute('data-cmd');
    const val = btn.getAttribute('data-val') || null;
    if (cmd === 'formatBlock' && val) document.execCommand(cmd, false, val);
    else document.execCommand(cmd, false, null);
    content.focus();
  });
});
document.getElementById('hlBtn').onclick = ()=>{
  document.execCommand('backColor', false, '#fff19a');
  content.focus();
};
document.getElementById('linkBtn').onclick = ()=>{
  const url = prompt('Enter URL (https://...)');
  if (url) document.execCommand('createLink', false, url);
  content.focus();
};

/* ============ Counters + Autosave ============ */
const titleEl   = document.getElementById('title');
const tagEl     = document.getElementById('tag');
const priorEl   = document.getElementById('priority');
const pinEl     = document.getElementById('pinned');
const content   = document.getElementById('content');
const wcEl      = document.getElementById('wc');
const ccEl      = document.getElementById('cc');
const noteForm  = document.getElementById('noteForm');

function updateStats(){
  const text = content.innerText.trim();
  wcEl.textContent = (text ? text.split(/\s+/).length : 0) + ' words';
  ccEl.textContent = text.length + ' chars';
}
['input','keyup','paste'].forEach(ev=>{
  content.addEventListener(ev, updateStats);
  titleEl.addEventListener(ev, updateStats);
});
updateStats();

/* Auto-save (debounce) */
let saveTimer=null;
function queueSave(){
  clearTimeout(saveTimer);
  saveTimer = setTimeout(saveNote, 700);
}
['input','keyup','paste'].forEach(ev=>{
  content.addEventListener(ev, queueSave);
  titleEl.addEventListener(ev, queueSave);
  tagEl.addEventListener(ev, queueSave);
  priorEl.addEventListener(ev, queueSave);
  pinEl.addEventListener('change', queueSave);
});

/* Ctrl+S */
document.addEventListener('keydown', (e)=>{
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's'){
    e.preventDefault(); saveNote();
  }
});

/* ============ Images ============ */
const imagesInput = document.getElementById('images');
const gallery     = document.getElementById('gallery');
let selectedFiles = [];
imagesInput.addEventListener('change', ()=>{
  for (const file of imagesInput.files) selectedFiles.push(file);
  renderGallery();
});
function renderGallery(){
  gallery.innerHTML='';
  selectedFiles.forEach((file, idx)=>{
    const url = URL.createObjectURL(file);
    const img = document.createElement('img');
    img.src = url;
    img.title = file.name;
    img.onclick = ()=>{ if(confirm('Remove this image?')){ selectedFiles.splice(idx,1); renderGallery(); } };
    gallery.appendChild(img);
  });
}

/* ============ Save Note ============ */
function fillHiddenForm(){
  document.getElementById('title_post').value    = titleEl.value.trim();
  document.getElementById('tag_post').value      = tagEl.value;
  document.getElementById('priority_post').value = priorEl.value;
  document.getElementById('pinned_post').value   = pinEl.checked ? '1' : '';
  document.getElementById('content_html').value  = content.innerHTML;
}
async function saveNote() {
  fillHiddenForm();
  const fd = new FormData(noteForm);

  selectedFiles.forEach((file) => fd.append('images[]', file));

  const res = await fetch('notes.php', { method: 'POST', body: fd });
  let data = null;
  try { data = await res.json(); } catch(e) {}

  console.log("✅ Note Saved");

  // ✅ Add to right side UI immediately
  addNoteToRightUI(
    document.getElementById('title').value,
    document.getElementById('tag').value,
    document.getElementById('priority').value
  );

  // ✅ Clear newly selected images
  selectedFiles = [];
}
function addNoteToRightUI(title, tag, priority) {
  const list = document.getElementById("list");

  const card = document.createElement("div");
  card.className = "note-card";
  card.style.padding = "12px";
  card.style.marginBottom = "10px";
  card.style.background = "rgba(255,255,255,0.08)";
  card.style.borderRadius = "12px";

  card.innerHTML = `
    <div class="note-title">${title || "Untitled"}</div>
    <div class="note-sub" style="font-size:12px; opacity:.8;">
      <span class="pill">${tag}</span>
      <span class="pill">${priority}</span>
      <span>${new Date().toDateString()}</span>
    </div>
  `;

  // ✅ Add to top of the list
  list.prepend(card);
}


/* Manual save button */
document.getElementById('saveBtn').onclick = saveNote;

/* ============ Export PDF ============ */
document.getElementById('exportBtn').onclick = ()=>{
  const node = document.createElement('div');
  node.style.padding='16px';
  node.innerHTML = `<h2>${(titleEl.value||'Untitled')}</h2>` + content.innerHTML;
  html2pdf().set({ margin:10, filename:(titleEl.value||'note')+'.pdf', html2canvas:{scale:2}, jsPDF:{unit:'mm', format:'a4', orientation:'portrait'}}).from(node).save();
};

/* ============ Voice-to-Note ============ */
let recognition, listening=false;
if ('webkitSpeechRecognition' in window) {
  recognition = new webkitSpeechRecognition();
  recognition.lang = "en-IN";
  recognition.continuous = false;
  recognition.interimResults = true;

  let buffer = '';
  recognition.onresult = (e)=>{
    let finalText = '';
    for (let i=0;i<e.results.length;i++){
      const tr = e.results[i][0].transcript;
      if (e.results[i].isFinal) finalText += tr + ' ';
      else buffer = tr;
    }
    if (finalText) {
      insertAtCursor(finalText);
      updateStats(); queueSave();
    }
  };
  recognition.onend = ()=>{ listening=false; micBtn.textContent='🎙️ Voice'; };
}
const micBtn = document.getElementById('micBtn');
micBtn.onclick = ()=>{
  if (!recognition) return alert('Speech recognition not supported in this browser');
  if (!listening){ recognition.start(); listening=true; micBtn.textContent='🟢 Listening…'; }
  else { recognition.stop(); }
};
function insertAtCursor(text){
  content.focus();
  document.execCommand('insertText', false, text);
}

/* ============ Load Note from List ============ */
document.querySelectorAll('.note-card').forEach(card=>{
  card.addEventListener('click', async ()=>{
    const id = card.getAttribute('data-id');
    const res = await fetch('get_note.php?id='+id);
    const data = await res.json();

    // Fill editor
    document.getElementById('note_id').value = data.id;
    titleEl.value = data.title || '';
    tagEl.value = data.tag || 'General';
    priorEl.value = data.priority || 'Low';
    pinEl.checked = (data.pinned == '1');
    content.innerHTML = data.content || '';
    updateStats();

    // Load images gallery if present
    selectedFiles = []; // reset added files
    gallery.innerHTML='';
    if (data.images) {
      try {
        const arr = JSON.parse(data.images);
        arr.forEach(src=>{
          const img = document.createElement('img');
          img.src = src;
          img.onclick = ()=> window.open(src, '_blank');
          gallery.appendChild(img);
        });
      } catch(e){}
    }
    // Show quick ops
    // add restore if trashed? We show ops in list already.
  });
});

/* ============ Filters / Search (client-side quick) ============ */
const searchEl = document.getElementById('search');
const filterTagEl = document.getElementById('filterTag');
const filterMonthEl = document.getElementById('filterMonth');
const sortEl = document.getElementById('sort');

function applyFilters(){
  const q   = (searchEl.value||'').toLowerCase();
  const tg  = filterTagEl.value || '';
  const mon = filterMonthEl.value || '';
  const sort = sortEl.value;

  const cards = [...document.querySelectorAll('.note-card')];

  cards.forEach(c=>{
    const t = (c.dataset.title||'').toLowerCase();
    const ct= (c.dataset.content||'').toLowerCase();
    const tag= c.dataset.tag || '';
    const dm = c.dataset.date || '';
    const match = (t.includes(q) || ct.includes(q)) && (!tg || tag===tg) && (!mon || dm===mon);
    c.style.display = match ? '' : 'none';
  });

  // Sort (client quick – pinned already in markup order)
  const list = document.getElementById('list');
  const visible = cards.filter(c=>c.style.display!=='none');
  visible.sort((a,b)=>{
    if (sort==='az') return (a.dataset.title||'').localeCompare(b.dataset.title||'');
    // default recent: keep DOM order; oldest: reverse
    if (sort==='oldest') return 1; // will reverse by appending
    return 0;
  });
  if (sort==='oldest'){
    visible.reverse();
  }
  visible.forEach(c=> list.appendChild(c));
}
[searchEl, filterTagEl, filterMonthEl, sortEl].forEach(el=> el.addEventListener('input', applyFilters));
applyFilters();
</script>
</body>
</html>
