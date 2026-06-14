document.addEventListener('DOMContentLoaded',function(){
  const hamburger=document.getElementById('hamburger');
  const nav=document.getElementById('navMenu');
  if(hamburger&&nav){hamburger.addEventListener('click',()=>nav.classList.toggle('open'));}
  const current=(location.pathname.split('/').pop()||'index.html').toLowerCase();
  document.querySelectorAll('.nav-menu a').forEach(a=>{if((a.getAttribute('href')||'').toLowerCase()===current){a.classList.add('active')}});
  const search=document.getElementById('integrationSearch');
  if(search){search.addEventListener('input',function(){const val=this.value.toLowerCase();document.querySelectorAll('.integration-card').forEach(card=>{card.style.display=card.textContent.toLowerCase().includes(val)?'flex':'none';});});}
  const reveal=document.querySelectorAll('.fade-up,.feature-card,.plain-card,.price-card,.integration-card,.team-card,.widget,.task-column');
  const io=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('show');io.unobserve(e.target);}})},{threshold:.12});
  reveal.forEach(el=>io.observe(el));
});


// Integration page search and section tabs
(function(){
  const search = document.getElementById('integrationSearch');
  const cards = Array.from(document.querySelectorAll('.app-item, .integration-card'));
  if(search && cards.length){
    search.addEventListener('input', function(){
      const q = this.value.toLowerCase().trim();
      cards.forEach(card => {
        const text = card.innerText.toLowerCase();
        card.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }
  const tabButtons = document.querySelectorAll('.integration-tabs button');
  const sections = document.querySelectorAll('.integration-section');
  if(tabButtons.length && sections.length){
    tabButtons.forEach(btn => btn.addEventListener('click', () => {
      tabButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const f = btn.dataset.filter;
      sections.forEach(sec => {
        sec.style.display = (f === 'all' && sec.dataset.section === 'all') || sec.dataset.section === f ? '' : 'none';
      });
      if(f === 'all') sections.forEach(sec => sec.style.display = '');
    }));
  }
})();
