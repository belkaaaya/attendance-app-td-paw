/* ========================== ATTENDANCE ROW LOGIC ========================== */
/* Columns (1-based) in the table:
   1:ID  2:Last  3:First  4:Course
   5..10: S1..S6 (sessions)
   11..16: P1..P6 (participation)
   17: Absences  18: Participations  19: Message
*/

function evaluateRow(tr){
  // sessions S1..S6 => 5..10
  const sessionChecks = Array.from(tr.querySelectorAll('td:nth-child(n+5):nth-child(-n+10) input[type="checkbox"]'));
  // participation P1..P6 => 11..16
  const partChecks    = Array.from(tr.querySelectorAll('td:nth-child(n+11):nth-child(-n+16) input[type="checkbox"]'));

  const presents  = sessionChecks.filter(c => c.checked).length;
  const absences  = sessionChecks.length - presents;
  const parts     = partChecks.filter(c => c.checked).length;

  const absCell  = tr.querySelector('.absences');
  const partCell = tr.querySelector('.participations');
  const msgCell  = tr.querySelector('.message');

  if(absCell)  absCell.textContent  = absences;
  if(partCell) partCell.textContent = parts;

  // highlight row by number of absences
  tr.classList.remove('abs-low','abs-mid','abs-high');
  if(absences < 3)       tr.classList.add('abs-low');
  else if(absences <= 4) tr.classList.add('abs-mid');
  else                   tr.classList.add('abs-high');

  // simple messaging (tweak thresholds if needed)
  let msg = "";
  if(absences >= 5){
    msg = "Excluded – too many absences – You need to participate more";
  }else if(absences >= 3){
    msg = (parts >= 3) ? "Warning – attendance low – Good participation"
                       : "Warning – attendance low – You need to participate more";
  }else{
    msg = (parts >= 4) ? "Good attendance – Excellent participation"
                       : "Good attendance – Keep participating";
  }
  if(msgCell) msgCell.textContent = msg;
}

function evaluateAll(){
  document.querySelectorAll('#attendanceTable tbody tr').forEach(evaluateRow);
}

// live recompute when a checkbox in table changes
document.addEventListener('change', e => {
  if(e.target.matches('#attendanceTable input[type="checkbox"]')){
    const tr = e.target.closest('tr');
    if(tr) evaluateRow(tr);
  }
});

// initial compute on load
evaluateAll();


/* ========================== REPORT PER SESSION (S1..S6) ========================== */
/* Definitions for each session k = 1..6:
   - total[k]         = number of students (rows)
   - present[k]       = students with S_k checked
   - participated[k]  = students with P_k checked
*/
function computeSessionReport(){
  const rows = Array.from(document.querySelectorAll('#attendanceTable tbody tr'));
  const n = rows.length;
  const sessions = 6;

  const total        = Array(sessions).fill(n);
  const present      = Array(sessions).fill(0);
  const participated = Array(sessions).fill(0);

  rows.forEach(tr => {
    for(let k=1; k<=sessions; k++){
      const sCell = tr.querySelector(`td:nth-child(${4 + k}) input[type="checkbox"]`);   // S_k => 5..10
      const pCell = tr.querySelector(`td:nth-child(${10 + k}) input[type="checkbox"]`);  // P_k => 11..16
      if(sCell && sCell.checked) present[k-1]++;
      if(pCell && pCell.checked) participated[k-1]++;
    }
  });

  return { total, present, participated };
}

/* Draw grouped bar chart: for each session (S1..S6), draw 3 bars (Total/Present/Participated) */
function drawSessionChart({ total, present, participated }){
  const canvas = document.getElementById('reportChart');
  if(!canvas) return;
  const ctx = canvas.getContext('2d');

  // clear canvas
  ctx.clearRect(0,0,canvas.width,canvas.height);

  const labels = ['S1','S2','S3','S4','S5','S6'];
  const groups = labels.length;

  const datasets = [
    { name:'Total',        values: total,        color:'#3F2F03' },
    { name:'Present',      values: present,      color:'#807043' },
    { name:'Participated', values: participated, color:'#B9B29F' }
  ];

  // layout
  const padding = 44;
  const chartW  = canvas.width  - padding*2;
  const chartH  = canvas.height - padding*2;

  const barGap   = 10;  // space between bars within a group
  const groupGap = 26;  // space between groups

  const barsPerGroup = datasets.length;
  const maxVal = Math.max(...datasets.flatMap(d => d.values), 1);

  const totalGroupGaps = groupGap * (groups - 1);
  const groupWidth = (chartW - totalGroupGaps) / groups;
  const innerWidth = groupWidth - barGap * (barsPerGroup - 1);
  const singleBarW = innerWidth / barsPerGroup;

  // axes
  ctx.strokeStyle = '#b9ac8b';
  ctx.lineWidth = 1.25;
  ctx.beginPath();
  // X axis
  ctx.moveTo(padding, canvas.height - padding);
  ctx.lineTo(canvas.width - padding, canvas.height - padding);
  // Y axis
  ctx.moveTo(padding, padding);
  ctx.lineTo(padding, canvas.height - padding);
  ctx.stroke();

  // horizontal guides
  ctx.strokeStyle = 'rgba(0,0,0,0.06)';
  ctx.lineWidth = 1;
  const guides = 5;
  for(let i=1;i<=guides;i++){
    const y = canvas.height - padding - (chartH/guides)*i;
    ctx.beginPath(); ctx.moveTo(padding, y); ctx.lineTo(canvas.width - padding, y); ctx.stroke();
  }

  // draw groups
  ctx.textAlign = 'center';
  for(let g=0; g<groups; g++){
    const groupX = padding + g*(groupWidth + groupGap);
    // session label
    ctx.fillStyle = '#6f644b';
    ctx.font = '12px Inter, sans-serif';
    ctx.fillText(labels[g], groupX + groupWidth/2, canvas.height - padding + 16);

    // bars in group
    let barX = groupX;
    datasets.forEach(ds => {
      const value = ds.values[g];
      const h = (value / maxVal) * (chartH - 8);
      const y = canvas.height - padding - h;

      ctx.fillStyle = ds.color;
      ctx.fillRect(barX, y, singleBarW, h);

      // value label above bar
      ctx.fillStyle = '#1A1917';
      ctx.font = 'bold 12px Inter, sans-serif';
      ctx.fillText(String(value), barX + singleBarW/2, y - 6);

      barX += singleBarW + barGap;
    });
  }

  // max tick label
  ctx.fillStyle = '#6f644b';
  ctx.font = '12px Inter, sans-serif';
  ctx.textAlign = 'right';
  ctx.fillText(String(maxVal), padding - 6, padding + 6);
}

// Show Report button: compute and draw per-session chart, then reveal section
const showReportBtn = document.getElementById('showReportBtn');
if(showReportBtn){
  showReportBtn.addEventListener('click', () => {
    const data = computeSessionReport();
    drawSessionChart(data);
    const section = document.getElementById('report');
    if(section) section.style.display = 'block';
  });
}


/* ========================== FORM VALIDATION + ADD ROW ========================== */
const form = document.getElementById('studentForm');
if(form){
  const fields = {
    studentId: { el: document.getElementById('studentId'), wrap: document.getElementById('f-studentId'),
                 test: v => /^\d+$/.test(v.trim()) },
    lastName:  { el: document.getElementById('lastName'),  wrap: document.getElementById('f-lastName'),
                 test: v => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/.test(v.trim()) },
    firstName: { el: document.getElementById('firstName'), wrap: document.getElementById('f-firstName'),
                 test: v => /^[A-Za-zÀ-ÖØ-öø-ÿ' -]+$/.test(v.trim()) },
    email:     { el: document.getElementById('email'),     wrap: document.getElementById('f-email'),
                 test: v => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()) }
  };

  function showError(wrap, ok){
    wrap.classList.toggle('invalid', !ok);
    const msg = wrap.querySelector('.error-msg');
    if(msg) msg.style.display = ok ? 'none' : 'block';
  }
  function validateField(f){
    const ok = f.test(f.el.value);
    showError(f.wrap, ok);
    return ok;
  }

  // live validation
  Object.values(fields).forEach(f => {
    if(!f.el) return;
    f.el.addEventListener('input', () => validateField(f));
    f.el.addEventListener('blur',  () => validateField(f));
  });

  form.addEventListener('submit', e => {
    const results = Object.values(fields).map(validateField);
    if(results.some(ok => !ok)){ e.preventDefault(); return; }

    // demo: add the student as a new row instead of real submit
    e.preventDefault();
    const tbody = document.querySelector('#attendanceTable tbody');
    if(!tbody) return;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${fields.studentId.el.value.trim()}</td>
      <td>${fields.lastName.el.value.trim()}</td>
      <td>${fields.firstName.el.value.trim()}</td>
      <td>AWP</td>
      ${Array.from({length:6}).map(()=>'<td><input type="checkbox"></td>').join('')}
      ${Array.from({length:6}).map(()=>'<td><input type="checkbox"></td>').join('')}
      <td class="absences">0</td>
      <td class="participations">0</td>
      <td class="message"></td>
    `;
    tbody.appendChild(tr);

    // evaluate this new row
    evaluateRow(tr);

    // reset form & hide errors
    form.reset();
    Object.values(fields).forEach(f => showError(f.wrap, true));

    // if the report is visible, refresh it
    const reportVisible = document.getElementById('report')?.style.display !== 'none';
    if(reportVisible){
      const data = computeSessionReport();
      drawSessionChart(data);
    }

    alert('Student added locally (demo). Replace with real submit for production.');
  });
  /* ========================== jQuery interactions ========================== */
$(document).ready(function(){

  // 1. survol -> surligner la ligne
  $('#attendanceTable tbody tr').hover(
    function(){
      $(this).css('background-color', '#fff1c6'); // couleur beige clair
    },
    function(){
      // retire la couleur au départ de la souris
      $(this).css('background-color', '');
    }
  );

  // 2. clic -> affiche une boîte avec nom complet et absences
  $('#attendanceTable tbody').on('click', 'tr', function(){
    const lastName  = $(this).find('td:nth-child(2)').text().trim();
    const firstName = $(this).find('td:nth-child(3)').text().trim();
    const abs       = $(this).find('.absences').text().trim();

    const fullName = `${firstName} ${lastName}`;
    alert(`Student: ${fullName}\nAbsences: ${abs}`);
  });

});
/* ========================== jQuery interactions ========================== */
$(document).ready(function(){

  // quand la souris passe sur une ligne
  $('#attendanceTable tbody').on('mouseenter', 'tr', function(){
    $(this).css({
      'background-color': '#1577a8ff',   // jaune clair visible
      'transition': 'background-color 0.2s'
    });
  });

  // quand la souris quitte la ligne
  $('#attendanceTable tbody').on('mouseleave', 'tr', function(){
    $(this).css('background-color', ''); // revient à la couleur d’origine
  });



    // si aucune valeur encore calculée
    const absDisplay = abs || 'not evaluated';
    const fullName = `${firstName} ${lastName}`;

    alert(`Student: ${fullName}\nAbsences: ${absDisplay}`);
  });




}