<?php
/**
 * Mapa decorativo detras del auto: curvas de nivel, manchas suaves y una ruta con dos pines.
 * Es solo adorno visual (no representa datos reales).
 *
 * @var \App\Views\HtmlView $this
 */
?>
<svg class="panel__mapa" viewBox="0 0 640 440" fill="none" aria-hidden="true" focusable="false">
    <defs>
        <filter id="mapa-difuminar" x="-30%" y="-30%" width="160%" height="160%">
            <feGaussianBlur stdDeviation="10" />
        </filter>
    </defs>

    <path d="M70 250C40 170 110 90 200 110C290 130 330 200 290 280C250 360 120 350 70 250Z" fill="rgb(21 24 31 / 7%)" filter="url(#mapa-difuminar)" />
    <path d="M210 300C200 240 280 210 350 240C420 270 430 350 360 380C290 410 220 370 210 300Z" fill="rgb(21 24 31 / 5%)" filter="url(#mapa-difuminar)" />

    <g stroke="rgb(21 24 31 / 9%)" stroke-width="1">
        <path d="M230 420L470 150L640 120" />
        <path d="M60 120L250 190L330 440" />
    </g>

    <g stroke="rgb(110 100 80 / 28%)" stroke-width="1">
        <path d="M-20 380C60 350 120 395 210 370S360 320 450 350S590 380 660 340" />
        <path d="M-20 410C70 385 140 425 230 400S380 352 470 380S600 410 660 372" />
        <path d="M-20 340C50 312 130 350 200 322S330 280 420 304S580 332 660 296" />
        <path d="M300 -10C340 40 400 20 440 70S520 110 560 60S620 20 660 40" />
        <path d="M340 -10C380 50 440 36 480 90S560 130 610 80S650 50 670 70" />
        <path d="M420 -10C450 30 500 10 530 50S590 80 640 40" />
        <path d="M120 -10C150 30 210 20 240 60S300 90 340 50" />
        <path d="M470 250C500 200 560 215 590 170S640 130 670 150" />
        <path d="M500 300C530 252 590 262 620 215S660 190 680 200" />
        <path d="M520 130C540 100 590 105 600 135C610 165 570 185 540 175C510 165 505 150 520 130Z" />
    </g>

    <path d="M92 340C100 190 220 135 330 125S475 118 520 112" stroke="#15181f" stroke-width="2.4" stroke-linecap="round" stroke-dasharray="0.1 7.5" />

    <path d="M-14 -11L18 0L-14 11L-7 0Z" transform="translate(173 180) rotate(-37)" fill="#15181f" stroke="#ffffff" stroke-width="2" stroke-linejoin="round" paint-order="stroke" />

    <g fill="#15181f">
        <path d="M92 340C88 331 77 322 77 310a15 15 0 0 1 30 0C107 322 96 331 92 340Z" />
        <path d="M520 112C516 103 505 94 505 82a15 15 0 0 1 30 0C535 94 524 103 520 112Z" />
    </g>
    <g fill="#ffffff">
        <circle cx="92" cy="310" r="6" />
        <circle cx="520" cy="82" r="6" />
    </g>
</svg>
