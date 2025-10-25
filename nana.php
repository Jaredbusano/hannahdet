<?php
// ...existing code...
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Will you be my Date?</title>
	<style>
		:root{
			--pink1:#ffd5e6;
			--pink2:#ffb6d5;
			--accent:#ff6fa3;
			--card:#fff6fb;
		}
		html,body{height:100%;margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,"Helvetica Neue",Arial;}
		body{
			background: linear-gradient(180deg,var(--pink1),var(--pink2));
			overflow:hidden;
			display:flex;
			align-items:center;
			justify-content:center;
			color:#333;
		}
		canvas{position:fixed;inset:0;z-index:0;pointer-events:none;}
		.center{
			position:relative;
			z-index:2;
			max-width:720px;
			width:92%;
			text-align:center;
		}
		.modal{
			background:rgba(255,255,255,0.95);
			border-radius:16px;
			padding:28px;
			box-shadow:0 8px 30px rgba(0,0,0,0.12);
			backdrop-filter: blur(6px);
		}
		h1{margin:0 0 10px;font-size:28px;color:var(--accent)}
		.question{font-size:20px;margin-bottom:18px}
		.btns{display:flex;gap:12px;justify-content:center}
		button{
			padding:10px 18px;border-radius:12px;border:0;cursor:pointer;font-weight:600;
			box-shadow:0 6px 12px rgba(0,0,0,0.09);
		}
		.yes{background:var(--accent);color:white}
		.no{background:#ffd6e8;color:#a80043}
		.letter{
			margin-top:18px;
			background:linear-gradient(180deg,rgba(255,255,255,0.9),var(--card));
			padding:18px;border-radius:12px;
			box-shadow:inset 0 1px 0 rgba(255,255,255,0.6);
			display:none;
			text-align:left;
			line-height:1.5;
			font-size:16px;
		}
		.letter.show{display:block;animation:fadeIn 700ms ease-out;}
		@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
		.hearts{display:flex;gap:8px;justify-content:center;margin-top:12px}
		.heart{width:18px;height:18px;background:var(--accent);transform:rotate(-45deg);border-radius:4px;position:relative;animation:pop 1s infinite;}
		.heart:before,.heart:after{content:"";position:absolute;width:18px;height:18px;background:var(--accent);border-radius:50%}
		.heart:before{top:-9px;left:0}
		.heart:after{left:9px;top:0}
		.heart:nth-child(2){animation-delay:0.2s}
		.heart:nth-child(3){animation-delay:0.4s}
		@keyframes pop{0%{transform:scale(.9) rotate(-45deg)}50%{transform:scale(1.08) rotate(-45deg)}100%{transform:scale(.95) rotate(-45deg)}}

		/* No button playful shake */
		.shake{animation:shake 700ms ease-in-out}
		@keyframes shake{
			0%{transform:translateX(0)}
			15%{transform:translateX(-8px)}
			30%{transform:translateX(8px)}
			45%{transform:translateX(-6px)}
			60%{transform:translateX(6px)}
			75%{transform:translateX(-3px)}
			100%{transform:translateX(0)}
		}

		.footerNote{font-size:13px;color:#8a3353;margin-top:10px}
		.small{font-size:13px;color:#6b2a46;margin-top:12px}
		/* responsive */
		@media (max-width:420px){
			h1{font-size:22px}
			.question{font-size:18px}
		}
	</style>
</head>
<body>
	<canvas id="petalCanvas"></canvas>

	<div class="center">
		<div id="prompt" class="modal" role="dialog" aria-modal="true">
			<h1>HI HANNAH!</h1>
			<div class="question">Do you wanna be my date?</div>
			<div class="btns">
				<button id="yesBtn" class="yes">Yes 💕</button>
				<button id="noBtn" class="no">No 😢</button>
			</div>

			<div id="letter" class="letter" aria-hidden="true">
				<p>Dear Hannah,</p>
				<p>I don't know what kind of spell you cast on me, but ever since you came along, everything's been so colorfull!, </p>
				<p>I would love to take you pretty person on a date this Sunday, (Nov. 2, 2025) if that's okayyy we'll go to church and eat some good food! and I quoted this from your song, "let's laugh and cry until we die" han - dye tonight.</p>
				<p style="margin-top:12px">With a hopeful heart,<br><strong>Jared b pogi ;p</strong></p>
				<div class="hearts" aria-hidden="true">
					<div class="heart"></div><div class="heart"></div><div class="heart"></div>
				</div>

			<div id="noMessage" class="small" style="display:none;margin-top:12px;color:#7a2a4b"></div>
	</div>

	<script>
		// Canvas petal system (replaces previous heart system)
		(() => {
			const canvas = document.getElementById('petalCanvas');
			const ctx = canvas.getContext('2d');
			let W = canvas.width = innerWidth;
			let H = canvas.height = innerHeight;
			const PETAL_COUNT = 350; // reduced for visibility and performance
			let petals = [];

			function rand(min,max){return Math.random()*(max-min)+min}
			function resize(){W = canvas.width = innerWidth; H = canvas.height = innerHeight}
			addEventListener('resize', resize);

			function createPetals(){
				petals = new Array(PETAL_COUNT);
				for(let i=0;i<PETAL_COUNT;i++){
					petals[i] = {
						x: rand(0,W),
						y: rand(-H, H),
						r: rand(6,14), // base size for petal
						speed: rand(0.4,1.6),
						drift: rand(-0.4,0.8),
						rotate: rand(-0.6,0.6),
						rotSpeed: rand(-0.01,0.02),
						// soft pink-y petals
						colorA: `hsla(${rand(330,345)},85%,${rand(60,78)}%,${rand(0.9,1)})`,
						colorB: `hsla(${rand(320,335)},80%,${rand(55,72)}%,${rand(0.9,1)})`
					};
				}
			}

			// draw a petal centered at 0,0 using Bezier curves
			function drawPetal(p){
				ctx.save();
				ctx.translate(p.x, p.y);
				ctx.rotate(p.rotate);
				// subtle scale variance
				const sx = 1 + Math.sin(p.rotate)*0.08;
				const sy = 0.9 + Math.cos(p.rotate)*0.08;
				ctx.scale(sx, sy);

				// gradient from tip to base for a nicer look
				const grad = ctx.createLinearGradient(0, -p.r, 0, p.r*1.3);
				grad.addColorStop(0, p.colorA);
				grad.addColorStop(1, p.colorB);
				ctx.fillStyle = grad;

				ctx.beginPath();
				// petal-like teardrop via two bezier curves
				ctx.moveTo(0, -p.r);
				ctx.bezierCurveTo(p.r * 0.9, -p.r * 0.9, p.r * 1.1, p.r * 0.4, 0, p.r * 1.3);
				ctx.bezierCurveTo(-p.r * 1.1, p.r * 0.4, -p.r * 0.9, -p.r * 0.9, 0, -p.r);
				ctx.closePath();
				ctx.fill();

				// slight highlight
				ctx.beginPath();
				ctx.globalAlpha = 0.12;
				ctx.fillStyle = '#ffffff';
				ctx.ellipse(-p.r*0.18, -p.r*0.2, p.r*0.35, p.r*0.6, -0.4, 0, Math.PI*2);
				ctx.fill();
				ctx.globalAlpha = 1;

				ctx.restore();
			}

			function update(){
				ctx.clearRect(0,0,W,H);
				for(let i=0;i<PETAL_COUNT;i++){
					const p = petals[i];
					p.y += p.speed;
					// gentle horizontal sway that depends on vertical position for variety
					p.x += p.drift + Math.sin((p.y * 0.02) + i * 0.07) * (0.6 + p.r * 0.02);
					p.rotate += p.rotSpeed;
					// wrap
					if(p.y > H + 50){ p.y = -50; p.x = rand(0,W); }
					if(p.x > W + 60) p.x = -60;
					if(p.x < -60) p.x = W + 60;
					// draw petal
					drawPetal(p);
				}
				requestAnimationFrame(update);
			}
			createPetals();
			update();

			// Expose a small method to trigger petal burst on "no"
			window.petalBurst = function(){
				for(let i=0;i<80;i++){
					const idx = Math.floor(rand(0,PETAL_COUNT));
					const p = petals[idx];
					p.speed = Math.max(0.6, p.speed + rand(-0.2,2.4));
					p.drift += rand(-2,2);
					p.rotate += rand(-0.8,0.8);
				}
			};
		})();

		// UI handling (updated: evasive "No" button)
		const yesBtn = document.getElementById('yesBtn');
		const noBtn = document.getElementById('noBtn');
		const letter = document.getElementById('letter');
		const prompt = document.getElementById('prompt');
		const noMessage = document.getElementById('noMessage');
		const btnsContainer = document.querySelector('.btns');

		// Make the No button evade the pointer when it gets close
		let noOffset = { x: 0, y: 0 };
		const AVOID_THRESHOLD = 140; // px
		noBtn.style.transition = 'transform 180ms ease';
		noBtn.style.willChange = 'transform';

		function clamp(v, a, b){ return Math.max(a, Math.min(b, v)); }

		function onPointerMove(e){
			const rect = noBtn.getBoundingClientRect();
			const cx = rect.left + rect.width / 2;
			const cy = rect.top + rect.height / 2;
			const dx = e.clientX - cx;
			const dy = e.clientY - cy;
			const dist = Math.hypot(dx, dy);

			if(dist < AVOID_THRESHOLD){
				// direction away from pointer
				const force = (AVOID_THRESHOLD - dist) * 0.9;
				const nx = (dx === 0 && dy === 0) ? (Math.random() - 0.5) : -dx / (dist || 1);
				const ny = (dx === 0 && dy === 0) ? (Math.random() - 0.5) : -dy / (dist || 1);

				noOffset.x += nx * force + (Math.random() - 0.5) * 20;
				noOffset.y += ny * (force * 0.35) + (Math.random() - 0.5) * 8;

				// bound movement so button stays visible inside the container
				const parentRect = btnsContainer.getBoundingClientRect();
				const maxX = Math.max(80, parentRect.width / 2 - rect.width / 2 - 8);
				const maxY = 120;

				noOffset.x = clamp(noOffset.x, -maxX, maxX);
				noOffset.y = clamp(noOffset.y, -maxY, maxY);

				noBtn.style.transform = `translate(${noOffset.x}px, ${noOffset.y}px)`;
			}
		}

		btnsContainer.addEventListener('pointermove', onPointerMove);
		btnsContainer.addEventListener('pointerleave', () => {
			noOffset = { x: 0, y: 0 };
			noBtn.style.transform = 'translate(0, 0)';
		});

		yesBtn.addEventListener('click', () => {
			// show letter
			letter.classList.add('show');
			letter.setAttribute('aria-hidden','false');
			document.body.animate([{filter:'brightness(1)'},{filter:'brightness(1.06)'}],{duration:800,fill:'forwards'});

			// disable buttons and stop evasive behavior
			yesBtn.disabled = true;
			noBtn.disabled = true;
			btnsContainer.removeEventListener('pointermove', onPointerMove);
			noBtn.style.transform = 'translate(0, 0)';
		});

		// Prevent normal clicking on No; keep playful fallback behavior if it somehow triggers.
		noBtn.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			// Playful fallback (same as before)
			prompt.classList.add('shake');
			window.petalBurst && window.petalBurst();
			noMessage.style.display = 'block';
			noMessage.textContent = "Oh... that's okay. Thank you for being honest — I hope your day is as lovely as you are.";
			noBtn.disabled = true;
			yesBtn.disabled = true;
			setTimeout(()=> prompt.classList.remove('shake'),700);
		});
	</script>
</body>
</html>