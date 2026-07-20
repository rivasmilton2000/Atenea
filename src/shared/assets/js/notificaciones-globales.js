(function(window,document){
  'use strict';
  const base=(document.currentScript&&document.currentScript.dataset.ateneaBase)||window.ATENEA_BASE_URL||'/Atenea';
  const nodes=()=>Array.from(document.querySelectorAll('[data-atenea-notification-count]'));
  function render(count){nodes().forEach(node=>{node.textContent=count>99?'99+':String(count);node.hidden=count<1;node.setAttribute('aria-label',count+' notificaciones no leídas');});}
  async function refresh(){if(!nodes().length)return;try{const response=await fetch(base+'/src/notificaciones/api.php',{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin'});if(!response.ok)return;const payload=await response.json();if(payload.ok)render(Number(payload.data.no_leidas||0));}catch(error){/* El contador conserva su valor del servidor cuando no hay conexión. */}}
  document.addEventListener('DOMContentLoaded',()=>{refresh();window.setInterval(refresh,30000);});
})(window,document);
