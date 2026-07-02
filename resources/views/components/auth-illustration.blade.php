<svg viewBox="0 0 400 350" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="clipboard-grad" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#1ac0ff" stop-opacity="0.3"/>
      <stop offset="100%" stop-color="#009ed6" stop-opacity="0.1"/>
    </linearGradient>
    <linearGradient id="line-grad" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%" stop-color="#1ac0ff"/>
      <stop offset="50%" stop-color="#009ed6"/>
      <stop offset="100%" stop-color="#006493"/>
    </linearGradient>
  </defs>

  <!-- Floating circles decoration -->
  <circle cx="60" cy="60" r="40" stroke="#6ad6ff" stroke-width="1" stroke-dasharray="4 4" fill="none" opacity="0.3"/>
  <circle cx="340" cy="40" r="25" fill="#1ac0ff" opacity="0.12"/>
  <circle cx="350" cy="280" r="35" stroke="#009ed6" stroke-width="1.5" fill="none" opacity="0.2"/>
  <circle cx="50" cy="290" r="15" fill="#6ad6ff" opacity="0.15"/>

  <!-- Clipboard body -->
  <rect x="100" y="45" width="200" height="260" rx="16" fill="url(#clipboard-grad)" stroke="white" stroke-width="2" stroke-opacity="0.3"/>
  <rect x="108" y="53" width="184" height="244" rx="12" fill="white" fill-opacity="0.92"/>

  <!-- Clip -->
  <rect x="170" y="35" width="60" height="22" rx="6" fill="white" fill-opacity="0.35" stroke="white" stroke-width="1.5" stroke-opacity="0.5"/>
  <circle cx="200" cy="46" r="4" fill="white" fill-opacity="0.5"/>

  <!-- Checklist header -->
  <rect x="125" y="65" width="150" height="6" rx="3" fill="#d0d5dd" opacity="0.6"/>
  <rect x="125" y="78" width="100" height="4" rx="2" fill="#d0d5dd" opacity="0.4"/>

  <!-- Checklist items -->
  <circle cx="135" cy="120" r="7" fill="#eef9ff" stroke="#1ac0ff" stroke-width="2"/>
  <rect x="150" y="117" width="110" height="6" rx="3" fill="#d0d5dd" opacity="0.5"/>

  <circle cx="135" cy="160" r="7" fill="#dcf3ff" stroke="#009ed6" stroke-width="2"/>
  <rect x="150" y="157" width="95" height="6" rx="3" fill="#d0d5dd" opacity="0.5"/>

  <circle cx="135" cy="200" r="8" fill="#009ed6"/>
  <path d="M131 200 L134 203 L141 196" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  <rect x="150" y="197" width="100" height="6" rx="3" fill="#009ed6" opacity="0.4"/>

  <circle cx="135" cy="240" r="7" fill="transparent" stroke="#d0d5dd" stroke-width="2"/>
  <rect x="150" y="237" width="80" height="6" rx="3" fill="#d0d5dd" opacity="0.4"/>

  <!-- Medical cross -->
  <rect x="260" y="170" width="8" height="28" rx="3" fill="#009ed6" opacity="0.7"/>
  <rect x="252" y="180" width="24" height="8" rx="3" fill="#009ed6" opacity="0.7"/>

  <!-- Heartbeat line -->
  <polyline points="108,275 145,275 158,265 170,285 182,258 195,278 210,275 240,275 260,275"
    stroke="url(#line-grad)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
  <circle cx="182" cy="258" r="4" fill="#1ac0ff"/>

  <!-- Small pulse dots -->
  <circle cx="145" cy="275" r="3" fill="#6ad6ff" opacity="0.6"/>
  <circle cx="210" cy="275" r="2" fill="#009ed6" opacity="0.4"/>
</svg>
