{{-- Legacy compat styles — included by pages not yet migrated to Tailwind
     (Incoming Parcels, Sort Parcels, Delivery Monitoring, Rider Management).
     Delete this include from a page once that page is rewritten with
     Tailwind utility classes, same as dashboard.blade.php / the layout. --}}
<style>
  .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;}
  .stat-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:18px 20px;position:relative;}
  .stat-card.primary{background:#4B2E7E;border-color:#4B2E7E;}
  .stat-card .card-top{display:flex;align-items:center;gap:6px;margin-bottom:10px;}
  .stat-card .card-top svg{flex-shrink:0;opacity:.65;}
  .stat-card.primary .card-top svg{opacity:.8;}
  .stat-card .k{font-size:11.5px;font-weight:600;color:var(--text-600);line-height:1;}
  .stat-card.primary .k{color:rgba(255,255,255,.75);}
  .stat-card .val-row{display:flex;align-items:flex-end;gap:8px;}
  .stat-card .v{font-size:32px;font-weight:800;letter-spacing:-.5px;color:var(--text-900);line-height:1;font-family:'Poppins',sans-serif;}
  .stat-card.primary .v{color:#fff;}
  .stat-card .delta-pill{display:inline-flex;align-items:center;gap:3px;border-radius:6px;padding:2px 7px;font-size:10.5px;font-weight:700;margin-bottom:2px;}
  .stat-card .delta-pill.up{background:#e8f5ee;color:#16a34a;}
  .stat-card .delta-pill.down{background:#fef2f2;color:#dc2626;}
  .stat-card .delta-pill.neutral{background:#f1eeff;color:#7c4fe0;}
  .stat-card.primary .delta-pill.up{background:rgba(255,255,255,.2);color:#fff;}
  .stat-card.primary .delta-pill.down{background:rgba(220,38,38,.3);color:#fca5a5;}
  .stat-card .sub{font-size:11px;color:var(--text-400);margin-top:8px;}
  .stat-card.primary .sub{color:rgba(255,255,255,.5);}
  .stat-card.accent{border-color:rgba(124,79,224,.25);}
  .panel{background:#fff;border:1px solid var(--line);border-radius:10px;overflow:hidden;margin-bottom:16px;}
  .panel-head{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid var(--line);flex-wrap:wrap;gap:10px;}
  .panel-head h2{margin:0;font-size:14.5px;font-weight:700;}
  .panel-head .sub{font-size:12px;color:var(--text-600);margin-top:2px;}
  .panel-body{padding:16px 18px;}
  .chart-wrap{height:220px;position:relative;}
  .chart-wrap.small{height:190px;}
  .rider-list{display:flex;flex-direction:column;}
  .rider-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--line);}
  .rider-row:last-child{border-bottom:none;}
  .status-dot{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:600;padding:3px 8px;border-radius:20px;}
  .status-dot.active{background:var(--ok-100);color:var(--ok-600);}
  .status-dot.busy{background:var(--pend-100);color:var(--pend-600);}
  .status-dot.offline{background:#F1EFF6;color:var(--text-400);}
  .status-dot .d{width:6px;height:6px;border-radius:50%;background:currentColor;}
  .feed{display:flex;flex-direction:column;}
  .feed-row{display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--line);}
  .feed-row:last-child{border-bottom:none;}
  .feed-ic{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex:0 0 auto;}
  .feed-ic.green{background:var(--ok-100);color:var(--ok-600);}
  .feed-ic.accent{background:#EFEAFB;color:var(--accent-600);}
  .feed-ic.violet{background:#EFEAFB;color:var(--ink-700);}
  .feed-ic.red{background:var(--bad-100);color:var(--bad-600);}
  .feed-text{font-size:12.5px;line-height:1.4;}
  .feed-text b{font-weight:600;}
  .feed-time{font-size:11px;color:var(--text-400);margin-top:2px;}
  table{width:100%;border-collapse:collapse;}
  thead th{text-align:left;font-size:10.5px;color:var(--text-600);font-weight:600;letter-spacing:.2px;padding:9px 18px;border-bottom:1px solid var(--line);background:#FBFAFD;}
  tbody td{padding:11px 18px;border-bottom:1px solid var(--line);font-size:12.5px;vertical-align:middle;}
  tbody tr:last-child td{border-bottom:none;}
  .pid{font-family:'IBM Plex Mono',monospace;font-weight:600;font-size:12px;}
  .badge{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:600;padding:4px 9px;border-radius:20px;}
  .badge.pending{background:var(--pend-100);color:var(--pend-600);}
  .badge.ok{background:var(--ok-100);color:var(--ok-600);}
  .badge .dotb{width:6px;height:6px;border-radius:50%;background:currentColor;}
  .link-btn{font-size:12px;font-weight:600;color:var(--ink-700);text-decoration:none;cursor:pointer;}
  .tabs{display:flex;gap:6px;background:var(--paper);border:1px solid var(--line);border-radius:8px;padding:3px;}
  .tab{font-size:11.5px;font-weight:600;color:var(--text-600);padding:5px 10px;border-radius:6px;cursor:pointer;}
  .tab.active{background:#fff;color:var(--text-900);box-shadow:0 1px 2px rgba(0,0,0,.06);}
  @media (max-width: 1100px){.grid,.grid-3{grid-template-columns:1fr;}}
  @media (max-width: 900px){.stats{grid-template-columns:repeat(2,1fr);}}
</style>
