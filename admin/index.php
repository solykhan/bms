<?php
/**
 * admin/index.php — Back-office dashboard for Linda Mani Wedding Celebrant.
 * Login-protected. Talks to api.php. Runs live on Hostinger with your API key.
 */
session_start();
$loggedIn = !empty($_SESSION['ab_admin']);
?><!DOCTYPE html>
<html lang="en-AU">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Back Office · Linda Mani Wedding Celebrant</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Mulish:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box;}
  body{margin:0;background:#EFE9DE;color:#2B2B27;font-family:'Mulish',sans-serif;}
  a{color:#8a6d3f;}
  input,textarea,select{font-family:'Mulish',sans-serif;color:#2B2B27;background:#FBF9F4;border:1px solid rgba(51,70,59,0.22);border-radius:6px;outline:none;padding:12px 14px;font-size:14px;width:100%;}
  input:focus,textarea:focus{border-color:#B08D57;}
  textarea{resize:vertical;line-height:1.6;}
  button{font-family:'Mulish',sans-serif;cursor:pointer;border:none;border-radius:6px;font-weight:700;}
  .mono{font-family:ui-monospace,Menlo,Consolas,monospace;}
  /* login */
  .login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
  .login-card{background:#FBF9F4;border:1px solid rgba(51,70,59,0.14);border-radius:12px;padding:40px 36px;max-width:400px;width:100%;box-shadow:0 30px 60px -34px rgba(38,53,44,0.4);}
  .brandmark{font-family:'Cormorant Garamond',serif;font-size:30px;font-weight:600;color:#33463B;text-align:center;}
  .brandsub{font-size:10.5px;font-weight:700;letter-spacing:2.4px;text-transform:uppercase;color:#B08D57;text-align:center;margin-top:4px;}
  /* app */
  header.top{background:#FBF9F4;border-bottom:1px solid rgba(51,70,59,0.12);}
  .bar{max-width:1080px;margin:0 auto;padding:20px 28px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;}
  .tabs{max-width:1080px;margin:0 auto;padding:0 28px;display:flex;gap:8px;flex-wrap:wrap;}
  .tab{padding:11px 18px;font-size:13.5px;font-weight:600;border:1px solid rgba(51,70,59,0.14);border-bottom:none;border-radius:8px 8px 0 0;background:#FBF9F4;color:#8C948A;}
  .tab.active{background:#EFE9DE;color:#26352C;}
  main{max-width:1080px;margin:0 auto;padding:28px;}
  .card{background:#FBF9F4;border:1px solid rgba(51,70,59,0.12);border-radius:10px;padding:26px 28px;margin-bottom:20px;}
  h2.sec{font-size:17px;font-weight:800;margin:0 0 16px;color:#26352C;}
  .btn-primary{background:#33463B;color:#F6F2EA;padding:13px 26px;font-size:14.5px;}
  .btn-gold{background:#B08D57;color:#2A362E;padding:11px 20px;font-size:13.5px;}
  .btn-ghost{background:transparent;border:1px solid rgba(51,70,59,0.24);color:#33463B;padding:9px 15px;font-size:13px;font-weight:600;}
  .btn-x{background:none;color:#c07a6a;font-size:15px;padding:4px 8px;font-weight:700;}
  .row{display:flex;gap:10px;align-items:center;}
  .stat{background:#FBF9F4;border:1px solid rgba(51,70,59,0.12);border-radius:10px;padding:22px;text-align:center;}
  .stat .n{font-family:'Cormorant Garamond',serif;font-size:38px;font-weight:600;line-height:1;}
  .stat .l{font-size:12.5px;color:#6b7066;margin-top:7px;}
  .item{background:#FBF9F4;border:1px solid rgba(51,70,59,0.12);border-radius:8px;padding:15px 18px;display:flex;align-items:center;gap:14px;margin-bottom:9px;}
  .badge{font-size:11px;font-weight:700;border-radius:5px;padding:4px 10px;}
  .b-pending{background:rgba(176,141,87,0.15);color:#8a6d3f;border:1px solid rgba(176,141,87,0.4);}
  .b-published{background:rgba(61,122,82,0.14);color:#3d7a52;border:1px solid rgba(61,122,82,0.3);}
  .b-failed{background:rgba(163,67,47,0.12);color:#a3432f;border:1px solid rgba(163,67,47,0.35);}
  .hide{display:none;}
  .toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#26352C;color:#F6F2EA;padding:13px 22px;border-radius:8px;font-size:14px;box-shadow:0 12px 30px -12px rgba(0,0,0,0.5);z-index:100;opacity:0;transition:opacity .2s;}
  .toast.show{opacity:1;}
  .warn{background:rgba(163,67,47,0.1);border:1px solid rgba(163,67,47,0.3);color:#a3432f;border-radius:8px;padding:13px 16px;font-size:13.5px;line-height:1.55;margin-bottom:18px;}
  .spinner{display:inline-block;width:15px;height:15px;border:2px solid rgba(246,242,234,0.4);border-top-color:#F6F2EA;border-radius:50%;animation:spin .7s linear infinite;vertical-align:-2px;margin-right:7px;}
  @keyframes spin{to{transform:rotate(360deg);}}
  .muted{color:#9aa094;font-size:13px;}
</style>
</head>
<body>

<!-- ===== LOGIN ===== -->
<div id="login" class="login-wrap <?= $loggedIn ? 'hide' : '' ?>">
  <div class="login-card">
    <div class="brandmark">Linda Mani</div>
    <div class="brandsub">Back Office</div>
    <p style="text-align:center;color:#6b7066;font-size:14px;margin:18px 0 22px;">Sign in to manage your blog.</p>
    <form id="loginForm">
      <input type="password" id="pw" placeholder="Password" autofocus>
      <div id="loginErr" style="color:#a3432f;font-size:13px;margin-top:10px;"></div>
      <button class="btn-primary" style="width:100%;margin-top:16px;" type="submit">Sign In</button>
    </form>
  </div>
</div>

<!-- ===== APP ===== -->
<div id="app" class="<?= $loggedIn ? '' : 'hide' ?>">
  <header class="top">
    <div class="bar">
      <div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:600;color:#26352C;">Back Office</div>
        <div class="muted">AI blog generation &amp; publishing</div>
      </div>
      <div class="row">
        <a class="btn-ghost" href="../Blog.dc.html" target="_blank" style="text-decoration:none;">↗ View Blog</a>
        <button class="btn-ghost" id="logoutBtn">Sign Out</button>
      </div>
    </div>
    <div class="tabs">
      <button class="tab active" data-tab="generate">✦ Generate</button>
      <button class="tab" data-tab="queue">☰ Queue</button>
      <button class="tab" data-tab="posts">❐ Posts</button>
      <button class="tab" data-tab="prompt">⚙ Prompt</button>
    </div>
  </header>

  <main>
    <div id="keyWarn" class="warn hide">⚠ No Anthropic API key detected in <span class="mono">cron/config.php</span>. Generation will fail until you add your <span class="mono">sk-ant-…</span> key.</div>

    <!-- GENERATE -->
    <section id="tab-generate">
      <div class="card">
        <h2 class="sec">Generate a post now</h2>
        <p class="muted" style="margin:0 0 14px;">Enter a keyword or title. The article is written, saved as a live page, added to your sitemap, and listed on your Blog — instantly.</p>
        <div class="row">
          <input type="text" id="genKw" placeholder="e.g. wedding celebrant Narellan">
          <button class="btn-primary" id="genBtn" style="white-space:nowrap;">Generate &amp; Publish</button>
        </div>
        <div id="genResult" style="margin-top:18px;"></div>
      </div>
    </section>

    <!-- QUEUE -->
    <section id="tab-queue" class="hide">
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;">
        <div class="stat"><div class="n" id="stPending" style="color:#6b7066;">–</div><div class="l">Pending</div></div>
        <div class="stat"><div class="n" id="stPublished" style="color:#3d7a52;">–</div><div class="l">Published</div></div>
        <div class="stat"><div class="n" id="stFailed" style="color:#a3432f;">–</div><div class="l">Failed</div></div>
      </div>
      <div class="card">
        <h2 class="sec">Add keywords to the queue</h2>
        <textarea id="qInput" rows="4" placeholder="One keyword or title per line…"></textarea>
        <div class="row" style="justify-content:flex-end;margin-top:12px;">
          <button class="btn-primary" id="qAddBtn">+ Add to Queue</button>
        </div>
      </div>
      <div class="card">
        <div class="row" style="justify-content:space-between;margin-bottom:14px;">
          <h2 class="sec" style="margin:0;">Queue</h2>
          <button class="btn-gold" id="publishNextBtn">▶ Publish next now</button>
        </div>
        <div id="queueList"></div>
      </div>
    </section>

    <!-- POSTS -->
    <section id="tab-posts" class="hide">
      <div class="card">
        <h2 class="sec">Published posts</h2>
        <div id="postsList"></div>
      </div>
    </section>

    <!-- PROMPT -->
    <section id="tab-prompt" class="hide">
      <div class="card">
        <h2 class="sec">Article prompt template</h2>
        <p class="muted" style="margin:0 0 14px;">Variables: <span class="mono">{{keyword}} {{brand_name}} {{domain_name}} {{sitemap_url}}</span>. This is the same file the daily cron uses.</p>
        <textarea id="promptBox" rows="18" class="mono" style="font-size:12.5px;"></textarea>
        <div class="row" style="justify-content:flex-end;margin-top:12px;">
          <button class="btn-primary" id="savePromptBtn">Save Prompt</button>
        </div>
      </div>
    </section>
  </main>
</div>

<div id="toast" class="toast"></div>

<script>
const API = 'api.php';
let state = { queue:[], posts:[], prompt:'' };

function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2600); }
async function api(action, data={}){
  const body = new URLSearchParams({action, ...data});
  const r = await fetch(API, {method:'POST', body});
  return r.json();
}
function esc(s){ return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// ---- login ----
document.getElementById('loginForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const res = await api('login', {password: document.getElementById('pw').value});
  if(res.ok){ document.getElementById('login').classList.add('hide'); document.getElementById('app').classList.remove('hide'); loadState(); }
  else document.getElementById('loginErr').textContent = res.error || 'Login failed';
});
document.getElementById('logoutBtn').addEventListener('click', async ()=>{ await api('logout'); location.reload(); });

// ---- tabs ----
document.querySelectorAll('.tab').forEach(t=>t.addEventListener('click', ()=>{
  document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));
  t.classList.add('active');
  ['generate','queue','posts','prompt'].forEach(n=>document.getElementById('tab-'+n).classList.add('hide'));
  document.getElementById('tab-'+t.dataset.tab).classList.remove('hide');
}));

// ---- state ----
async function loadState(){
  const res = await api('state');
  if(!res.ok){ if(res.auth===false) location.reload(); return; }
  state = res;
  document.getElementById('keyWarn').classList.toggle('hide', res.keyConfigured);
  document.getElementById('promptBox').value = res.prompt || '';
  renderQueue(); renderPosts();
}

function renderQueue(){
  const q = state.queue||[];
  document.getElementById('stPending').textContent   = q.filter(x=>x.status==='pending').length;
  document.getElementById('stPublished').textContent = (state.posts||[]).length;
  document.getElementById('stFailed').textContent    = q.filter(x=>x.status==='failed').length;
  const el = document.getElementById('queueList');
  if(!q.length){ el.innerHTML = '<div class="muted">Queue is empty. Add keywords above.</div>'; return; }
  el.innerHTML = q.map((item,i)=>{
    const cls = item.status==='published'?'b-published':item.status==='failed'?'b-failed':'b-pending';
    const retry = item.status==='failed' ? `<button class="btn-ghost" data-retry="${esc(item.keyword)}">Retry</button>` : '';
    return `<div class="item">
      <span class="muted" style="width:22px;">${i+1}</span>
      <div style="flex:1;"><div style="font-weight:600;">${esc(item.keyword)}</div>${item.date?`<div class="muted">${esc(item.date)}</div>`:''}</div>
      <span class="badge ${cls}">${esc(item.status)}</span>
      ${retry}
      <button class="btn-x" data-del="${esc(item.keyword)}">✕</button>
    </div>`;
  }).join('');
  el.querySelectorAll('[data-del]').forEach(b=>b.onclick=async()=>{ const r=await api('queue_remove',{keyword:b.dataset.del}); if(r.ok){state.queue=r.queue;renderQueue();} });
  el.querySelectorAll('[data-retry]').forEach(b=>b.onclick=async()=>{ const r=await api('queue_retry',{keyword:b.dataset.retry}); if(r.ok){state.queue=r.queue;renderQueue();} });
}

function renderPosts(){
  const p = state.posts||[];
  const el = document.getElementById('postsList');
  if(!p.length){ el.innerHTML = '<div class="muted">No posts yet. Generate one from the Generate tab.</div>'; return; }
  el.innerHTML = p.map(post=>`<div class="item">
      <div style="flex:1;">
        <div style="font-family:'Cormorant Garamond',serif;font-size:19px;font-weight:600;color:#26352C;">${esc(post.title)}</div>
        <div class="muted">/${esc(post.slug)}.html · ${esc(post.category||'')} · ${esc(post.date||'')} · ${esc(post.words||0)} words</div>
      </div>
      <a class="btn-ghost" href="../${esc(post.slug)}.html" target="_blank" style="text-decoration:none;">View</a>
      <button class="btn-x" data-del="${esc(post.slug)}">✕</button>
    </div>`).join('');
  el.querySelectorAll('[data-del]').forEach(b=>b.onclick=async()=>{ if(!confirm('Delete this post and its page?'))return; const r=await api('post_delete',{slug:b.dataset.del}); if(r.ok){state.posts=r.posts;renderPosts();renderQueue();toast('Post deleted');} });
}

// ---- generate ----
document.getElementById('genBtn').addEventListener('click', async ()=>{
  const kw = document.getElementById('genKw').value.trim();
  if(!kw){ toast('Enter a keyword first'); return; }
  const btn = document.getElementById('genBtn'); const box = document.getElementById('genResult');
  btn.disabled=true; btn.innerHTML='<span class="spinner"></span>Writing…';
  box.innerHTML='<div class="muted">Generating a 1500+ word article — this usually takes 30–90 seconds. Please wait…</div>';
  const res = await api('generate',{keyword:kw});
  btn.disabled=false; btn.textContent='Generate & Publish';
  if(res.ok){
    const p=res.post;
    box.innerHTML=`<div style="background:#fff;border:1px solid rgba(61,122,82,0.35);border-radius:8px;padding:20px;">
      <div style="color:#3d7a52;font-weight:700;font-size:13px;">✓ Published (${esc(p.words)} words)</div>
      <div style="font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:600;margin:6px 0;">${esc(p.title)}</div>
      <div class="muted">/${esc(p.slug)}.html</div>
      <a class="btn-gold" href="../${esc(p.slug)}.html" target="_blank" style="display:inline-block;margin-top:12px;text-decoration:none;">View live page ↗</a>
    </div>`;
    document.getElementById('genKw').value=''; loadState(); toast('Article published');
  } else {
    box.innerHTML=`<div class="warn" style="margin:0;">✕ ${esc(res.error||'Generation failed')}</div>`;
  }
});

// ---- queue actions ----
document.getElementById('qAddBtn').addEventListener('click', async ()=>{
  const v=document.getElementById('qInput').value.trim();
  if(!v){toast('Nothing to add');return;}
  const r=await api('queue_add',{keywords:v});
  if(r.ok){ state.queue=r.queue; document.getElementById('qInput').value=''; renderQueue(); toast('Added to queue'); }
});
document.getElementById('publishNextBtn').addEventListener('click', async ()=>{
  const btn=document.getElementById('publishNextBtn');
  btn.disabled=true; btn.innerHTML='<span class="spinner"></span>Publishing…';
  const res=await api('publish_next');
  btn.disabled=false; btn.textContent='▶ Publish next now';
  if(res.ok){ toast('Published: '+(res.post?res.post.title:'')); loadState(); }
  else toast(res.error||'Failed');
});

// ---- prompt ----
document.getElementById('savePromptBtn').addEventListener('click', async ()=>{
  const r=await api('save_prompt',{prompt:document.getElementById('promptBox').value});
  toast(r.ok?'Prompt saved':(r.error||'Save failed'));
});

<?php if ($loggedIn): ?>loadState();<?php endif; ?>
</script>
</body>
</html>
